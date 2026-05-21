<?php
namespace App\Http\Controllers;

use App\Models\{Book, Member, BookCopy, BookCategory};
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // ── Stat cards (from views) ────────────────────────────────
        $totalBooks        = DB::table('v_book_availability')->count();
        $totalBookCopies   = DB::table('v_book_availability')->sum('total_copies');
        $availableBooks    = DB::table('v_book_availability')->sum('available_copies');

        $totalMembers      = DB::table('v_member_summary')->count();
        $activeMembers     = DB::table('v_member_summary')->where('status', 'active')->count();

        $activeBorrowings  = DB::table('v_active_borrowings')->where('status', 'borrowed')->count();
        $overdueBorrowings = DB::table('v_overdue_borrowings')->count();
        $totalUnpaidFines  = DB::table('v_member_summary')->sum('unpaid_fines');

        // ── Recent borrowings from view ────────────────────────────
        $recentBorrowings = \App\Models\Borrowing::with(['member', 'book'])
    ->latest()
    ->take(8)
    ->get();

        // ── Monthly chart from v_monthly_stats ─────────────────────
        $monthlyStats = DB::table('v_monthly_stats')
            ->orderBy('yr')->orderBy('mo')
            ->take(6)->get();

        $monthlyLabels   = $monthlyStats->pluck('month_label');
        $monthlyIssued   = $monthlyStats->pluck('total_issued');
        $monthlyReturned = $monthlyStats->pluck('total_returned');

        // ── Category doughnut ──────────────────────────────────────
        $categories     = BookCategory::withCount('books')->orderByDesc('books_count')->take(8)->get();
        $categoryLabels = $categories->pluck('name');
        $categoryData   = $categories->pluck('books_count');

        return view('dashboard.index', compact(
            'totalBooks', 'totalBookCopies', 'availableBooks',
            'totalMembers', 'activeMembers',
            'activeBorrowings', 'overdueBorrowings', 'totalUnpaidFines',
            'recentBorrowings',
            'monthlyLabels', 'monthlyIssued', 'monthlyReturned',
            'categoryLabels', 'categoryData'
        ));
    }
}
