<?php
namespace App\Http\Controllers;

use App\Models\{Reservation, Book, Member};
use Illuminate\Http\Request;

class ReservationController extends Controller
{

    public function index(Request $request)
    {
        $reservations = Reservation::with(['member', 'book'])
            ->latest()->paginate(15);

        return view('reservations.index', compact('reservations'));
    }

    public function create(Request $request)
    {
        $members     = Member::where('status', 'active')->orderBy('name')->get();
        $books       = Book::where('is_active', true)->orderBy('title')->get();
        $defaultExpiry = now()->addDays(7)->format('Y-m-d');

        return view('reservations.create', compact('members', 'books', 'defaultExpiry'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'member_id'   => 'required|exists:members,id',
            'book_id'     => 'required|exists:books,id',
            'expiry_date' => 'required|date|after:today',
            'notes'       => 'nullable|string',
        ]);

        // Check for duplicate reservation
        $exists = Reservation::where('member_id', $data['member_id'])
            ->where('book_id', $data['book_id'])
            ->whereIn('status', ['pending', 'available'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'Member already has an active reservation for this book!');
        }

        // Queue position
        $queuePos = Reservation::where('book_id', $data['book_id'])
            ->whereIn('status', ['pending', 'available'])
            ->count() + 1;

        // Generate reservation number
        $count = Reservation::count() + 1;
        $resNumber = 'RES-' . now()->year . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);

        Reservation::create([
            'reservation_number' => $resNumber,
            'member_id'          => $data['member_id'],
            'book_id'            => $data['book_id'],
            'queue_position'     => $queuePos,
            'status'             => 'pending',
            'reserved_date'      => today(),
            'expiry_date'        => $data['expiry_date'],
            'notes'              => $data['notes'],
        ]);

        return redirect()->route('reservations.index')
            ->with('success', 'Reservation created! Queue position: #' . $queuePos);
    }

    public function cancel(Reservation $reservation)
    {
        if (!in_array($reservation->status, ['pending', 'available'])) {
            return back()->with('error', 'This reservation cannot be cancelled.');
        }

        $reservation->update(['status' => 'cancelled']);

        // Reorder queue positions for remaining pending reservations
        Reservation::where('book_id', $reservation->book_id)
            ->where('status', 'pending')
            ->orderBy('queue_position')
            ->get()
            ->each(function ($r, $index) {
                $r->update(['queue_position' => $index + 1]);
            });

        return back()->with('success', 'Reservation cancelled successfully.');
    }
}
