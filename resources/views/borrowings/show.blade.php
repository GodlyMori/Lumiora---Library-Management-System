@extends('layouts.app')
@section('title', 'Borrowing Details')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><i class="bi bi-arrow-left-right"></i> {{ $borrowing->borrowing_number }}</span>
                @php
                    $badge = match($borrowing->status) { 'borrowed' => 'primary', 'returned' => 'success', 'overdue' => 'danger', default => 'secondary' };
                @endphp
                <span class="badge bg-{{ $badge }} fs-6">{{ ucfirst($borrowing->status) }}</span>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">Member</dt>
                    <dd class="col-sm-8">
                        <a href="{{ route('members.show', $borrowing->member) }}">{{ $borrowing->member->name }}</a>
                        <small class="text-muted">({{ $borrowing->member->membership_id }})</small>
                    </dd>
                    <dt class="col-sm-4">Book</dt>
                    <dd class="col-sm-8">
                        <a href="{{ route('books.show', $borrowing->book) }}">{{ $borrowing->book->title }}</a>
                    </dd>
                    <dt class="col-sm-4">Issue Date</dt>     <dd class="col-sm-8">{{ $borrowing->issue_date->format('M d, Y') }}</dd>
                    <dt class="col-sm-4">Due Date</dt>       <dd class="col-sm-8">{{ $borrowing->due_date->format('M d, Y') }}</dd>
                    <dt class="col-sm-4">Returned On</dt>
                    <dd class="col-sm-8">{{ $borrowing->actual_return_date ? $borrowing->actual_return_date->format('M d, Y') : '—' }}</dd>
                    <dt class="col-sm-4">Overdue Days</dt>   <dd class="col-sm-8">{{ $borrowing->overdue_days }} days</dd>
                    <dt class="col-sm-4">Fine</dt>
                    <dd class="col-sm-8">
                        @if($borrowing->fine_amount > 0)
                            ₱{{ number_format($borrowing->fine_amount, 2) }}
                            @if($borrowing->fine_paid)
                                <span class="badge bg-success ms-1">Paid</span>
                            @else
                                <span class="badge bg-danger ms-1">Unpaid</span>
                            @endif
                        @else
                            No fine
                        @endif
                    </dd>
                    @if($borrowing->notes)
                    <dt class="col-sm-4">Notes</dt> <dd class="col-sm-8">{{ $borrowing->notes }}</dd>
                    @endif
                </dl>

                <div class="d-flex gap-2 mt-2">
                    @if($borrowing->status !== 'returned')
                    <form method="POST" action="{{ route('borrowings.return', $borrowing) }}"
                          onsubmit="return confirm('Mark as returned?')">
                        @csrf
                        <button class="btn btn-success">
                            <i class="bi bi-arrow-return-left"></i> Process Return
                        </button>
                    </form>
                    @endif

                    @if($borrowing->fine_amount > 0 && !$borrowing->fine_paid)
                    <form method="POST" action="{{ route('borrowings.payFine', $borrowing) }}">
                        @csrf
                        <button class="btn btn-outline-warning">
                            <i class="bi bi-cash"></i> Mark Fine as Paid
                        </button>
                    </form>
                    @endif

                    <a href="{{ route('borrowings.index') }}" class="btn btn-outline-secondary">Back</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
