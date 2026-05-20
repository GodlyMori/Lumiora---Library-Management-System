@extends('layouts.app')
@section('title','Dashboard')
@section('subtitle','Welcome back, ' . auth()->user()->name)

@section('content')
{{-- Stat Cards --}}
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="card stat-card g-blue shadow-sm">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-lbl">Total Books</div>
                    <div class="stat-val mt-1">{{ $totalBooks }}</div>
                    <div class="stat-sub">{{ $totalBookCopies }} total copies</div>
                </div>
                <div class="stat-icon"><i class="bi bi-book"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card stat-card g-violet shadow-sm">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-lbl">Members</div>
                    <div class="stat-val mt-1">{{ $totalMembers }}</div>
                    <div class="stat-sub">{{ $activeMembers }} active</div>
                </div>
                <div class="stat-icon"><i class="bi bi-people"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card stat-card g-green shadow-sm">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-lbl">Active Borrowings</div>
                    <div class="stat-val mt-1">{{ $activeBorrowings }}</div>
                    <div class="stat-sub">{{ $availableBooks }} copies available</div>
                </div>
                <div class="stat-icon"><i class="bi bi-arrow-left-right"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card stat-card g-red shadow-sm">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-lbl">Overdue</div>
                    <div class="stat-val mt-1">{{ $overdueBorrowings }}</div>
                    <div class="stat-sub">₱{{ number_format($totalUnpaidFines,2) }} unpaid fines</div>
                </div>
                <div class="stat-icon"><i class="bi bi-exclamation-triangle"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    {{-- Monthly chart --}}
    <div class="col-md-8">
        <div class="card t-card">
            <div class="card-header">
                <span><i class="bi bi-graph-up me-2 text-primary"></i>Borrowings — Last 6 Months</span>
            </div>
            <div class="card-body p-3">
                <canvas id="monthlyChart" height="100"></canvas>
            </div>
        </div>
    </div>

    {{-- Category doughnut --}}
    <div class="col-md-4">
        <div class="card t-card">
            <div class="card-header">
                <span><i class="bi bi-pie-chart me-2 text-violet"></i>Books by Category</span>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center p-3">
                <canvas id="categoryChart" height="180"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- Recent borrowings --}}
    <div class="col-md-7">
        <div class="card t-card">
            <div class="card-header">
                <span><i class="bi bi-clock-history me-2"></i>Recent Borrowings</span>
                <a href="{{ route('borrowings.index') }}" class="btn btn-sm btn-outline-primary no-print">View All</a>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead><tr><th>Member</th><th>Book</th><th>Due Date</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($recentBorrowings as $b)
                        <tr>
                            <td>
                                <a href="{{ route('members.show',$b->member) }}" class="text-decoration-none fw-500">
                                    {{ $b->member->name }}
                                </a>
                            </td>
                            <td class="text-truncate" style="max-width:140px" title="{{ $b->book->title }}">{{ $b->book->title }}</td>
                            <td style="white-space:nowrap">{{ $b->due_date->format('M d, Y') }}</td>
                            <td>
                                @php $cls = ['borrowed'=>'bs-borrowed','returned'=>'bs-returned','overdue'=>'bs-overdue'][$b->status] ?? 'bs-cancelled' @endphp
                                <span class="badge {{ $cls }}">{{ ucfirst($b->status) }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4"><div class="empty-state"><i class="bi bi-inbox"></i><p>No borrowings yet</p></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Quick actions + overdue alert --}}
    <div class="col-md-5 d-flex flex-column gap-3">
        {{-- Overdue alert --}}
        @if($overdueBorrowings > 0)
        <div class="card border-0 shadow-sm" style="border-radius:14px;background:linear-gradient(135deg,#fef2f2,#fee2e2)">
            <div class="card-body d-flex align-items-center gap-3">
                <div style="font-size:2rem">⚠️</div>
                <div class="flex-fill">
                    <div class="fw-600" style="color:#b91c1c;font-size:.9rem">{{ $overdueBorrowings }} overdue book(s)!</div>
                    <div style="font-size:.8rem;color:#dc2626">Action required — notify members</div>
                </div>
                <a href="{{ route('reports.overdue') }}" class="btn btn-sm" style="background:#b91c1c;color:#fff;white-space:nowrap">
                    View All
                </a>
            </div>
        </div>
        @endif

        {{-- Quick actions --}}
        <div class="card t-card">
            <div class="card-header"><span><i class="bi bi-lightning me-2 text-warning"></i>Quick Actions</span></div>
            <div class="card-body d-grid gap-2">
                <a href="{{ route('borrowings.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Issue a Book
                </a>
                <a href="{{ route('books.create') }}" class="btn btn-outline-primary">
                    <i class="bi bi-book me-2"></i>Add New Book
                </a>
                <a href="{{ route('members.create') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-person-plus me-2"></i>Register Member
                </a>
                <a href="{{ route('reports.overdue') }}" class="btn btn-outline-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>Overdue Report
                </a>
            </div>
        </div>

        {{-- Availability bar --}}
        <div class="card t-card">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2" style="font-size:.85rem;font-weight:600">
                    <span>Book Availability</span>
                    <span class="text-muted">{{ $availableBooks }}/{{ $totalBookCopies }}</span>
                </div>
                <div class="progress" style="height:8px;border-radius:99px">
                    @php $pct = $totalBookCopies > 0 ? ($availableBooks/$totalBookCopies)*100 : 0 @endphp
                    <div class="progress-bar bg-success" style="width:{{ $pct }}%;border-radius:99px"></div>
                </div>
                <div class="mt-1" style="font-size:.75rem;color:#94a3b8">{{ number_format($pct,1) }}% available</div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Monthly borrowings chart
new Chart(document.getElementById('monthlyChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($monthlyLabels) !!},
        datasets: [{
            label: 'Issued',
            data: {!! json_encode($monthlyIssued) !!},
            backgroundColor: 'rgba(99,102,241,.8)',
            borderRadius: 6,
        },{
            label: 'Returned',
            data: {!! json_encode($monthlyReturned) !!},
            backgroundColor: 'rgba(16,185,129,.7)',
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: {
            y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
            x: { grid: { display: false } }
        }
    }
});

// Category doughnut chart
new Chart(document.getElementById('categoryChart'), {
    type: 'doughnut',
    data: {
        labels: {!! json_encode($categoryLabels) !!},
        datasets: [{
            data: {!! json_encode($categoryData) !!},
            backgroundColor: ['#6366f1','#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#14b8a6','#f97316','#06b6d4','#84cc16'],
            borderWidth: 2, borderColor: '#fff',
        }]
    },
    options: {
        responsive: true, cutout: '65%',
        plugins: { legend: { position: 'bottom', labels: { font: { size: 11 }, boxWidth: 12 } } }
    }
});
</script>
@endsection
