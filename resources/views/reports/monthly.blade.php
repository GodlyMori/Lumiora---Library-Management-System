@extends('layouts.app')
@section('title','Monthly Report')
@section('subtitle', $monthlyLabel)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <form method="GET" class="d-flex gap-2 align-items-center">
        <label class="form-label mb-0 small fw-500">Month:</label>
        <input type="month" name="month" class="form-control form-control-sm" value="{{ $month }}" style="width:180px">
        <button class="btn btn-primary btn-sm">Go</button>
    </form>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.export.borrowings', ['month' => $month]) }}" class="btn btn-outline-success btn-sm">
            <i class="bi bi-download me-1"></i>Export CSV
        </a>
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-printer me-1"></i>Print
        </button>
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-sm">← Back</a>
    </div>
</div>

<h5 class="fw-700 mb-3">{{ $monthlyLabel }}</h5>

<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="card stat-card g-blue shadow-sm">
            <div class="stat-icon mb-2"><i class="bi bi-arrow-left-right"></i></div>
            <div class="stat-val">{{ $monthlyIssued }}</div>
            <div class="stat-lbl">Issued</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card stat-card g-green shadow-sm">
            <div class="stat-icon mb-2"><i class="bi bi-arrow-return-left"></i></div>
            <div class="stat-val">{{ $monthlyReturned }}</div>
            <div class="stat-lbl">Returned</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card stat-card g-red shadow-sm">
            <div class="stat-icon mb-2"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="stat-val">{{ $monthlyOverdue }}</div>
            <div class="stat-lbl">Overdue</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card stat-card g-orange shadow-sm">
            <div class="stat-icon mb-2"><i class="bi bi-cash-coin"></i></div>
            <div class="stat-val" style="font-size:1.3rem">₱{{ number_format($monthlyFines,2) }}</div>
            <div class="stat-lbl">Fines Collected</div>
        </div>
    </div>
</div>

{{-- Top borrowed books --}}
@if($topBooks->count() > 0)
<div class="card t-card">
    <div class="card-header">
        <span><i class="bi bi-trophy me-2 text-warning"></i>Most Borrowed Books This Month</span>
    </div>
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead><tr><th>#</th><th>Title</th><th>Author</th><th>Times Borrowed</th></tr></thead>
            <tbody>
                @foreach($topBooks as $i => $b)
                <tr>
                    <td>
                        @if($i === 0) 🥇
                        @elseif($i === 1) 🥈
                        @elseif($i === 2) 🥉
                        @else {{ $i + 1 }}
                        @endif
                    </td>
                    <td class="fw-500">{{ $b->title }}</td>
                    <td class="text-muted small">{{ $b->author }}</td>
                    <td><span class="badge bs-borrowed">{{ $b->borrow_count }}x</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection
