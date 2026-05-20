@extends('layouts.app')
@section('title','Overdue Books')
@section('subtitle','Books past their due date')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <div></div>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.export.overdue') }}" class="btn btn-outline-success btn-sm">
            <i class="bi bi-download me-1"></i>Export CSV
        </a>
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-printer me-1"></i>Print
        </button>
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-sm">← Back</a>
    </div>
</div>

@if($overdue->count() > 0)
<div class="alert alert-danger d-flex align-items-center gap-2 mb-3 no-print">
    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
    <div>
        <strong>{{ $overdue->count() }} overdue book(s)</strong> —
        Total outstanding fines: <strong>₱{{ number_format($totalFine, 2) }}</strong>
        <div class="small mt-1">Fines are calculated live from the database view at ₱5/day.</div>
    </div>
</div>
@endif

<div class="card t-card">
    <div class="card-header">
        <span><i class="bi bi-exclamation-triangle me-2 text-danger"></i>Overdue Books Report</span>
        <span class="badge bs-overdue">{{ $overdue->count() }} records</span>
    </div>
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead>
                <tr><th>Member</th><th>Contact</th><th>Book</th><th>Due Date</th><th>Days Overdue</th><th>Fine (₱5/day)</th><th class="no-print">Action</th></tr>
            </thead>
            <tbody>
                @forelse($overdue as $r)
                <tr>
                    <td>
                        <a href="{{ route('members.show', $r->member_id) }}" class="text-decoration-none fw-500">
                            {{ $r->member_name }}
                        </a>
                        <div class="small text-muted">{{ $r->membership_id }}</div>
                    </td>
                    <td class="small text-muted">{{ $r->member_phone ?? $r->member_email ?? '—' }}</td>
                    <td class="small">{{ \Illuminate\Support\Str::limit($r->book_title, 40) }}</td>
                    <td class="text-danger fw-500 small">{{ \Carbon\Carbon::parse($r->due_date)->format('M d, Y') }}</td>
                    <td><span class="badge bs-overdue">{{ $r->days_overdue }} days</span></td>
                    <td>
                        <span class="{{ $r->fine_paid ? 'text-success' : 'text-danger' }} fw-600">
                            ₱{{ number_format($r->calculated_fine, 2) }}
                        </span>
                        @if($r->fine_paid)<span class="badge bs-returned ms-1">Paid</span>@endif
                    </td>
                    <td class="no-print">
                        <a href="{{ route('borrowings.show', $r->id) }}" class="btn btn-sm btn-outline-primary py-0">
                            <i class="bi bi-eye"></i> View
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7">
                    <div class="empty-state">
                        <i class="bi bi-check-circle-fill" style="color:#10b981"></i>
                        <p>No overdue books! Everything is returned on time. 🎉</p>
                    </div>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
