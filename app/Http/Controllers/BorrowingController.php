<?php

namespace App\Http\Controllers;

use App\Models\{Borrowing, Book, Member};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BorrowingController extends Controller
{
    public function index(Request $request)
    {
        $query = Borrowing::with(['member', 'book']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from_date')) {
            $query->where('issue_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->where('issue_date', '<=', $request->to_date);
        }

        if ($request->filled('search')) {
            $term = $request->search;

            $query->where(function ($q) use ($term) {
                $q->whereHas('member', function ($m) use ($term) {
                    $m->where('name', 'like', "%$term%")
                      ->orWhere('membership_id', 'like', "%$term%");
                })
                ->orWhereHas('book', function ($b) use ($term) {
                    $b->where('title', 'like', "%$term%");
                })
                ->orWhere('borrowing_number', 'like', "%$term%");
            });
        }

        $borrowings = $query->latest()->paginate(15);

        return view('borrowings.index', compact('borrowings'));
    }

    public function create()
    {
        $books = DB::table('books')
            ->leftJoin('book_categories', 'book_categories.id', '=', 'books.category_id')
            ->select(
                'books.id',
                'books.title',
                'books.author',
                'books.available_copies',
                'book_categories.name as category_name'
            )
            ->where('books.is_active', 1)
            ->whereNull('books.deleted_at')
            ->orderBy('books.title')
            ->get();

        $members = DB::table('v_member_summary')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('borrowings.create', [
            'books' => $books,
            'members' => $members,
            'defaultDueDate' => now()->addDays(14)->format('Y-m-d')
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'member_id' => 'required|exists:members,id',
            'books' => 'required|array|min:1',
            'books.*.book_id' => 'required|exists:books,id|distinct',
            'due_date' => 'required|date|after:today',
            'notes' => 'nullable|string',
        ]);

        $member = Member::findOrFail($data['member_id']);

        if (!$member->canBorrow()) {
            return back()->with('error', 'Member cannot borrow (inactive, expired, or limit reached).');
        }

        $successCount = 0;
        $errors = [];
        $issued = [];

        DB::beginTransaction();

        try {

            foreach ($data['books'] as $bookData) {

                $book = Book::findOrFail($bookData['book_id']);
                $copy = $book->getAvailableCopy();

                if (!$copy) {
                    $errors[] = "No available copies of {$book->title}";
                    continue;
                }

                // ✅ FIX: generate borrowing number safely
                $borrowingNumber = 'BRW-' . now()->format('YmdHis') . '-' . rand(100, 999);

                $borrowing = Borrowing::create([
                    'borrowing_number' => $borrowingNumber,
                    'member_id' => $member->id,
                    'book_id' => $book->id,
                    'book_copy_id' => $copy->id,
                    'issued_by' => auth()->id(),
                    'issue_date' => today(),
                    'due_date' => $data['due_date'],
                    'status' => 'borrowed',
                    'notes' => $data['notes'],
                ]);

                $issued[] = $borrowing;
                $successCount++;
            }

            DB::commit();

            if ($successCount === 0) {
                return back()->with('error', 'No books were issued. ' . implode(', ', $errors));
            }

            $message = $successCount . ' book(s) issued successfully.';

            if (!empty($errors)) {
                $message .= ' Skipped: ' . implode(', ', $errors);
            }

            return redirect()->route('borrowings.show', $issued[0])
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error issuing books: ' . $e->getMessage());
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
            return back()->with('error', 'Already returned.');
        }

        $today = today();
        $overdueDays = max(0, $today->diffInDays($borrowing->due_date, false) * -1);
        $fine = $overdueDays * 5;

        $borrowing->update([
            'status' => 'returned',
            'actual_return_date' => $today,
            'overdue_days' => $overdueDays,
            'fine_amount' => $fine,
        ]);

        return redirect()->route('borrowings.show', $borrowing)
            ->with('success', $overdueDays > 0
                ? "Returned with {$overdueDays} day(s) overdue. Fine: ₱{$fine}"
                : "Book returned successfully."
            );
    }

    public function payFine(Borrowing $borrowing)
    {
        $borrowing->update(['fine_paid' => true]);

        return back()->with('success', 'Fine marked as paid.');
    }
}