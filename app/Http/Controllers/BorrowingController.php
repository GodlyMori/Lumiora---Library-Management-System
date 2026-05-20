<?php
namespace App\Http\Controllers;

use App\Models\{Borrowing, Book, Member};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BorrowingController extends Controller
{
    public function index(Request $request)
    {
        // Use v_active_borrowings for active ones, fall back to full table for filters
        $query = Borrowing::with(['member', 'book']);

        if ($request->filled('status'))    $query->where('status', $request->status);
        if ($request->filled('from_date')) $query->where('issue_date', '>=', $request->from_date);
        if ($request->filled('to_date'))   $query->where('issue_date', '<=', $request->to_date);

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(fn($q) =>
                $q->whereHas('member', fn($m) => $m->where('name', 'like', "%$term%")
                                                   ->orWhere('membership_id', 'like', "%$term%"))
                  ->orWhereHas('book', fn($b) => $b->where('title', 'like', "%$term%"))
                  ->orWhere('borrowing_number', 'like', "%$term%")
            );
        }

        $borrowings = $query->latest()->paginate(15);
        return view('borrowings.index', compact('borrowings'));
    }

    public function create(Request $request)
    {
        // Use v_book_availability to only show books with copies available
        $books = DB::table('books')
        ->leftJoin('book_categories', 'book_categories.id', '=', 'books.category_id')
        ->select('books.id', 'books.title', 'books.author', 'books.available_copies',
             'book_categories.name as category_name')
        ->where('books.is_active', 1)
        ->whereNull('books.deleted_at')
        ->orderBy('books.title')
        ->get();

        $members = DB::table('v_member_summary')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $defaultDueDate = now()->addDays(14)->format('Y-m-d');

        return view('borrowings.create', compact('members', 'books', 'defaultDueDate'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'member_id' => 'required|exists:members,id',
            'books'     => 'required|array|min:1',
            'books.*.book_id' => 'required|exists:books,id|distinct',
            'due_date'  => 'required|date|after:today',
            'notes'     => 'nullable|string',
        ]);

        $member = Member::findOrFail($data['member_id']);

        // Check if member can borrow
        if (!$member->canBorrow()) {
            return back()->with('error', 'Member cannot borrow: check status, expiry date, or borrow limit.');
        }

        $successCount = 0;
        $errors = [];
        $issuedBorrowings = [];

        // Use database transaction to ensure all books are issued together
        DB::beginTransaction();
        try {
            foreach ($data['books'] as $bookData) {
                $book = Book::findOrFail($bookData['book_id']);
                
                // Check if there's an available copy
                $copy = $book->getAvailableCopy();
                if (!$copy) {
                    $errors[] = "No available copies of '{$book->title}'";
                    continue;
                }

                // Create the borrowing record
                $borrowing = Borrowing::create([
                    'member_id'    => $member->id,
                    'book_id'      => $book->id,
                    'book_copy_id' => $copy->id,
                    'issued_by'    => auth()->id(),
                    'issue_date'   => today(),
                    'due_date'     => $data['due_date'],
                    'status'       => 'borrowed',
                    'notes'        => $data['notes'],
                ]);

                $issuedBorrowings[] = $borrowing;
                $successCount++;
            }

            DB::commit();

            // Prepare success message
            $message = $successCount === 1
                ? "1 book issued successfully! Due: {$data['due_date']}"
                : "{$successCount} books issued successfully! Due: {$data['due_date']}";

            // Add error messages if any books failed
            if (!empty($errors)) {
                $message .= ' | Skipped: ' . implode(', ', $errors);
            }

            // Redirect to the first borrowing's show page, or back to index if none issued
            if (!empty($issuedBorrowings)) {
                return redirect()->route('borrowings.show', $issuedBorrowings[0])
                    ->with('success', $message);
            }

            return back()->with('error', 'No books could be issued. ' . implode(', ', $errors));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'An error occurred while issuing books: ' . $e->getMessage());
        }
    }

    public function show(Borrowing $borrowing)
    {
        $borrowing->load(['member', 'book', 'bookCopy', 'issuedBy']);
        return view('borrowings.show', compact('borrowing'));
    }

    public function return(Borrowing $borrowing)
    {
        if ($borrowing->status === 'returned') {
            return back()->with('error', 'This book has already been returned!');
        }

        $today       = today();
        $overdueDays = max(0, $today->diffInDays($borrowing->due_date, false) * -1);
        $fine        = $overdueDays * 5.00; // ₱5 per day

        // Trigger handles: available_copies restore, book_copy status,
        // member borrowed_count decrement, next reservation notification
        $borrowing->update([
            'status'             => 'returned',
            'actual_return_date' => $today,
            'overdue_days'       => $overdueDays,
            'fine_amount'        => $fine,
        ]);

        $msg = $overdueDays > 0
            ? "Book returned. Overdue by {$overdueDays} day(s). Fine: ₱" . number_format($fine, 2)
            : 'Book returned successfully. No fine!';

        return redirect()->route('borrowings.show', $borrowing)->with('success', $msg);
    }

    public function payFine(Borrowing $borrowing)
    {
        $borrowing->update(['fine_paid' => true]);
        return back()->with('success', 'Fine of ₱' . number_format($borrowing->fine_amount, 2) . ' marked as paid!');
    }
}
