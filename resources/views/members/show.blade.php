@extends('layouts.app')
@section('title', $member->name)
@section('subtitle','Member Profile')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <div>
        <h4 class="fw-700 mb-0">{{ $member->name }}</h4>
        <small class="text-muted">{{ $member->membership_id }}</small>
    </div>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-printer me-1"></i>Print ID Card
        </button>
        <a href="{{ route('members.edit', $member) }}" class="btn btn-warning btn-sm">
            <i class="bi bi-pencil me-1"></i>Edit
        </a>
        <a href="{{ route('borrowings.create', ['member_id' => $member->id]) }}" class="btn btn-primary btn-sm">
            <i class="bi bi-arrow-left-right me-1"></i>Issue Book
        </a>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-4">

        {{-- Profile Card --}}
        <div class="card t-card mb-3">
            <div class="card-body text-center py-4">
                <div class="mx-auto mb-3 d-flex align-items-center justify-content-center"
                     style="width:72px;height:72px;border-radius:50%;
                            background:linear-gradient(135deg,#6366f1,#8b5cf6)">
                    <span style="color:#fff;font-size:1.8rem;font-weight:700">
                        {{ strtoupper(substr($member->name,0,1)) }}
                    </span>
                </div>
                <h5 class="fw-700 mb-1">{{ $member->name }}</h5>
                <div class="text-muted small mb-2">{{ $member->membership_id }}</div>
                @php $scls = ['active'=>'bs-available','suspended'=>'bs-overdue','inactive'=>'bs-cancelled'][$member->status] ?? 'bs-cancelled' @endphp
                <span class="badge {{ $scls }} px-3 py-1">{{ ucfirst($member->status) }}</span>
                @if($summary->membership_status === 'expiring_soon')
                    <span class="badge bs-pending ms-1">Expiring Soon</span>
                @elseif($summary->membership_status === 'expired')
                    <span class="badge bs-overdue ms-1">Expired</span>
                @endif

                <hr class="my-3">
                <dl class="row text-start small mb-0">
                    <dt class="col-5 text-muted">Type</dt>     <dd class="col-7 fw-500">{{ ucfirst($member->member_type) }}</dd>
                    <dt class="col-5 text-muted">Email</dt>    <dd class="col-7">{{ $member->email ?? '—' }}</dd>
                    <dt class="col-5 text-muted">Phone</dt>    <dd class="col-7">{{ $member->phone ?? '—' }}</dd>
                    <dt class="col-5 text-muted">Expiry</dt>
                    <dd class="col-7">
                        @php $exp = \Carbon\Carbon::parse($member->membership_expiry_date) @endphp
                        <span class="{{ $exp->isPast() ? 'text-danger fw-600' : '' }}">{{ $exp->format('M d, Y') }}</span>
                    </dd>
                    <dt class="col-5 text-muted">Max Books</dt> <dd class="col-7">{{ $member->max_books }}</dd>
                    <dt class="col-5 text-muted">Address</dt>   <dd class="col-7">{{ $member->address ?? '—' }}</dd>
                </dl>
            </div>
        </div>

        {{-- Borrow Summary (from v_member_summary) --}}
        <div class="card t-card mb-3">
            <div class="card-header"><i class="bi bi-bar-chart me-2"></i>Borrow Summary</div>
            <div class="card-body">
                <div class="row g-2 text-center">
                    <div class="col-4">
                        <div style="font-size:1.5rem;font-weight:700;color:#6366f1">{{ $summary->total_borrowed }}</div>
                        <div class="text-muted" style="font-size:.72rem">Total</div>
                    </div>
                    <div class="col-4">
                        <div style="font-size:1.5rem;font-weight:700;color:#10b981">{{ $summary->total_returned }}</div>
                        <div class="text-muted" style="font-size:.72rem">Returned</div>
                    </div>
                    <div class="col-4">
                        <div style="font-size:1.5rem;font-weight:700;color:#ef4444">{{ $summary->total_overdue }}</div>
                        <div class="text-muted" style="font-size:.72rem">Overdue</div>
                    </div>
                </div>
                @if($summary->unpaid_fines > 0)
                <div class="mt-3 p-2 rounded-3 text-center" style="background:#fee2e2">
                    <div style="font-size:.8rem;color:#b91c1c;font-weight:600">
                        ₱{{ number_format($summary->unpaid_fines,2) }} unpaid fine(s)
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Printable ID Card --}}
        <div class="card border-0" style="border-radius:14px;overflow:hidden;box-shadow:0 4px 16px rgba(0,0,0,.12)">
            <div style="background:linear-gradient(135deg,#0f172a,#1e3a5f);padding:14px 16px 10px">
                <div class="d-flex align-items-center gap-2">
                    <div style="width:26px;height:26px;background:linear-gradient(135deg,#6366f1,#8b5cf6);
                                border-radius:7px;display:flex;align-items:center;justify-content:center;
                                color:#fff;font-size:13px"><i class="bi bi-book-half"></i></div>
                    <div style="color:#fff;font-weight:700;font-size:.9rem">Librex</div>
                    <div class="ms-auto" style="color:rgba(255,255,255,.45);font-size:.62rem;text-transform:uppercase;letter-spacing:1px">Member Card</div>
                </div>
            </div>
            <div style="background:#fff;padding:14px 16px">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div style="width:42px;height:42px;border-radius:50%;
                                background:linear-gradient(135deg,#6366f1,#8b5cf6);
                                display:flex;align-items:center;justify-content:center;
                                color:#fff;font-size:1.1rem;font-weight:700;flex-shrink:0">
                        {{ strtoupper(substr($member->name,0,1)) }}
                    </div>
                    <div>
                        <div style="font-weight:700;font-size:.88rem;color:#0f172a">{{ $member->name }}</div>
                        <div style="font-size:.7rem;color:#6366f1;font-weight:600">{{ $member->membership_id }}</div>
                    </div>
                </div>
                <div class="d-flex justify-content-between" style="font-size:.7rem;color:#64748b">
                    <span><strong style="color:#0f172a">Type:</strong> {{ ucfirst($member->member_type) }}</span>
                    <span><strong style="color:#0f172a">Exp:</strong> {{ $exp->format('m/Y') }}</span>
                </div>
            </div>
        </div>

    </div>

    {{-- Borrowing History --}}
    <div class="col-md-8">
        <div class="card t-card">
            <div class="card-header">
                <span><i class="bi bi-clock-history me-2"></i>Borrowing History</span>
                <span class="badge bs-borrowed">{{ $summary->total_borrowed }} total</span>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead>
                        <tr><th>Book</th><th>Issued</th><th>Due</th><th>Returned</th><th>Fine</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        @forelse($member->borrowings()->with('book')->latest()->paginate(10) as $b)
                        <tr>
                            <td>
                                <a href="{{ route('books.show',$b->book) }}" class="text-decoration-none fw-500 small">
                                    {{ \Illuminate\Support\Str::limit($b->book->title, 38) }}
                                </a>
                            </td>
                            <td class="small">{{ $b->issue_date->format('M d, Y') }}</td>
                            <td class="small">{{ $b->due_date->format('M d, Y') }}</td>
                            <td class="small">{{ $b->actual_return_date?->format('M d, Y') ?? '—' }}</td>
                            <td class="small">
                                @if($b->fine_amount > 0)
                                    <span class="{{ $b->fine_paid ? 'text-success' : 'text-danger' }} fw-500">
                                        ₱{{ number_format($b->fine_amount,2) }}
                                    </span>
                                @else — @endif
                            </td>
                            <td>
                                @php $cls = ['borrowed'=>'bs-borrowed','returned'=>'bs-returned','overdue'=>'bs-overdue'][$b->status] ?? 'bs-cancelled' @endphp
                                <span class="badge {{ $cls }}">{{ ucfirst($b->status) }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6">
                            <div class="empty-state"><i class="bi bi-journal-x"></i><p>No borrowing records yet.</p></div>
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
