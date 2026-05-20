@extends('layouts.app')
@section('title','Daily Report')
@section('subtitle', now()->format('F d, Y — l'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <div></div>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-printer me-1"></i>Print
        </button>
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-sm">← Back</a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card stat-card g-blue shadow-sm">
            <div class="stat-icon mb-2"><i class="bi bi-arrow-up-circle"></i></div>
            <div class="stat-val">{{ $issuedToday }}</div>
            <div class="stat-lbl">Issued Today</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card g-green shadow-sm">
            <div class="stat-icon mb-2"><i class="bi bi-arrow-return-left"></i></div>
            <div class="stat-val">{{ $returnedToday }}</div>
            <div class="stat-lbl">Returned Today</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card g-orange shadow-sm">
            <div class="stat-icon mb-2"><i class="bi bi-cash-coin"></i></div>
            <div class="stat-val" style="font-size:1.4rem">₱{{ number_format($fineCollectedToday, 2) }}</div>
            <div class="stat-lbl">Fines Collected</div>
        </div>
    </div>
</div>

<div class="card t-card">
    <div class="card-header">
        <span><i class="bi bi-list-ul me-2"></i>Today's Transactions</span>
        <span class="badge bs-borrowed">{{ $transactions->count() }} records</span>
    </div>
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead><tr><th>Time</th><th>Member</th><th>Book</th><th>Type</th><th>Fine</th></tr></thead>
            <tbody>
                @forelse($transactions as $t)
                <tr>
                    <td class="small text-muted">{{ \Carbon\Carbon::parse($t->updated_at)->format('h:i A') }}</td>
                    <td>
                        <a href="{{ route('members.show', $t->member_id ?? '#') }}" class="text-decoration-none fw-500 small">
                            {{ $t->member_name }}
                        </a>
                        <div class="small text-muted">{{ $t->membership_id }}</div>
                    </td>
                    <td class="small">{{ \Illuminate\Support\Str::limit($t->book_title, 42) }}</td>
                    <td>
                        @if($t->transaction_type === 'returned')
                            <span class="badge bs-returned">Returned</span>
                        @else
                            <span class="badge bs-borrowed">Issued</span>
                        @endif
                    </td>
                    <td class="small">
                        @if($t->fine_amount > 0)
                            <span class="{{ $t->fine_paid ? 'text-success' : 'text-danger' }} fw-500">
                                ₱{{ number_format($t->fine_amount, 2) }}
                            </span>
                        @else — @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="5">
                    <div class="empty-state"><i class="bi bi-calendar-x"></i><p>No transactions today.</p></div>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
