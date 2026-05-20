<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function overdue()
    {
        // v_overdue_borrowings calculates days_overdue and fine live
        $overdue = DB::table('v_overdue_borrowings')->get();
        $totalFine = $overdue->where('fine_paid', 0)->sum('calculated_fine');

        return view('reports.overdue', compact('overdue', 'totalFine'));
    }

    public function daily()
    {
        // v_daily_transactions has everything for today already
        $transactions = DB::table('v_daily_transactions')->get();

        $issuedToday        = $transactions->where('transaction_type', 'issued')->count();
        $returnedToday      = $transactions->where('transaction_type', 'returned')->count();
        $fineCollectedToday = $transactions
            ->where('transaction_type', 'returned')
            ->where('fine_paid', true)
            ->sum('fine_amount');

        return view('reports.daily', compact(
            'transactions', 'issuedToday', 'returnedToday', 'fineCollectedToday'
        ));
    }

    public function monthly(Request $request)
    {
        $month     = $request->get('month', now()->format('Y-m'));
        $date      = Carbon::parse($month . '-01');
        $monthlyLabel = $date->format('F Y');

        // Pull from v_monthly_stats
        $stats = DB::table('v_monthly_stats')
            ->where('yr', $date->year)
            ->where('mo', $date->month)
            ->first();

        $monthlyIssued   = $stats->total_issued   ?? 0;
        $monthlyReturned = $stats->total_returned  ?? 0;
        $monthlyOverdue  = $stats->total_overdue   ?? 0;
        $monthlyFines    = $stats->total_fines     ?? 0;

        // Top borrowed books this month
        $topBooks = DB::table('borrowings')
            ->join('books', 'books.id', '=', 'borrowings.book_id')
            ->selectRaw('books.title, books.author, COUNT(*) as borrow_count')
            ->whereYear('issue_date', $date->year)
            ->whereMonth('issue_date', $date->month)
            ->groupBy('books.id', 'books.title', 'books.author')
            ->orderByDesc('borrow_count')
            ->take(5)
            ->get();

        return view('reports.monthly', compact(
            'monthlyIssued', 'monthlyReturned', 'monthlyOverdue',
            'monthlyFines', 'monthlyLabel', 'month', 'topBooks'
        ));
    }

    // ── CSV Exports using views ──────────────────────────────────────

    public function exportOverdueCsv()
    {
        $overdue = DB::table('v_overdue_borrowings')->get();
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="overdue_' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($overdue) {
            $f = fopen('php://output', 'w');
            fputcsv($f, ['Member', 'Membership ID', 'Phone', 'Book', 'Due Date', 'Days Overdue', 'Fine (₱)', 'Paid']);
            foreach ($overdue as $r) {
                fputcsv($f, [
                    $r->member_name, $r->membership_id, $r->member_phone,
                    $r->book_title,
                    $r->due_date,
                    $r->days_overdue,
                    number_format($r->calculated_fine, 2),
                    $r->fine_paid ? 'Yes' : 'No',
                ]);
            }
            fclose($f);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportBorrowingsCsv(Request $request)
    {
        $query = DB::table('borrowings')
            ->join('members', 'members.id', '=', 'borrowings.member_id')
            ->join('books',   'books.id',   '=', 'borrowings.book_id')
            ->select(
                'borrowings.borrowing_number', 'members.name as member_name',
                'members.membership_id', 'books.title as book_title',
                'borrowings.issue_date', 'borrowings.due_date',
                'borrowings.actual_return_date', 'borrowings.status',
                'borrowings.fine_amount', 'borrowings.fine_paid'
            );

        if ($request->filled('month')) {
            $date = Carbon::parse($request->month . '-01');
            $query->whereYear('borrowings.issue_date', $date->year)
                  ->whereMonth('borrowings.issue_date', $date->month);
        }

        $data    = $query->orderBy('borrowings.issue_date', 'desc')->get();
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="borrowings_' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($data) {
            $f = fopen('php://output', 'w');
            fputcsv($f, ['Borrowing #', 'Member', 'Membership ID', 'Book', 'Issue Date', 'Due Date', 'Return Date', 'Status', 'Fine', 'Paid']);
            foreach ($data as $r) {
                fputcsv($f, [
                    $r->borrowing_number, $r->member_name, $r->membership_id,
                    $r->book_title, $r->issue_date, $r->due_date,
                    $r->actual_return_date ?? '', $r->status,
                    number_format($r->fine_amount, 2), $r->fine_paid ? 'Yes' : 'No',
                ]);
            }
            fclose($f);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportMembersCsv()
    {
        // Use v_member_summary for complete stats
        $members = DB::table('v_member_summary')->orderBy('name')->get();
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="members_' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($members) {
            $f = fopen('php://output', 'w');
            fputcsv($f, ['Membership ID', 'Name', 'Email', 'Phone', 'Type', 'Status', 'Expiry', 'Total Borrowed', 'Currently Borrowed', 'Unpaid Fines (₱)']);
            foreach ($members as $m) {
                fputcsv($f, [
                    $m->membership_id, $m->name, $m->email, $m->phone,
                    $m->member_type, $m->status, $m->membership_expiry_date,
                    $m->total_borrowed, $m->currently_borrowed,
                    number_format($m->unpaid_fines, 2),
                ]);
            }
            fclose($f);
        };

        return response()->stream($callback, 200, $headers);
    }
}
