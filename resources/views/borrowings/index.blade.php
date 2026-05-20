@extends('layouts.app')
@section('title', 'Borrowings')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Borrowings</h5>
    <a href="{{ route('borrowings.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> Issue Book
    </a>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Search member or book..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="borrowed" {{ request('status') === 'borrowed' ? 'selected' : '' }}>Borrowed</option>
                    <option value="returned" {{ request('status') === 'returned' ? 'selected' : '' }}>Returned</option>
                    <option value="overdue"  {{ request('status') === 'overdue'  ? 'selected' : '' }}>Overdue</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="from_date" class="form-control form-control-sm"
                       value="{{ request('from_date') }}" placeholder="From">
            </div>
            <div class="col-md-2">
                <input type="date" name="to_date" class="form-control form-control-sm"
                       value="{{ request('to_date') }}" placeholder="To">
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button class="btn btn-primary btn-sm flex-fill">Search</button>
                <a href="{{ route('borrowings.index') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Member</th>
                    <th>Book</th>
                    <th>Issue Date</th>
                    <th>Due Date</th>
                    <th>Fine</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($borrowings as $b)
                <tr class="{{ $b->status === 'overdue' ? 'table-danger' : '' }}">
                    <td><small class="text-muted">{{ $b->borrowing_number }}</small></td>
                    <td>{{ $b->member->name }}</td>
                    <td class="text-truncate" style="max-width:150px">{{ $b->book->title }}</td>
                    <td>{{ $b->issue_date->format('M d, Y') }}</td>
                    <td>{{ $b->due_date->format('M d, Y') }}</td>
                    <td>{{ $b->fine_amount > 0 ? '₱'.number_format($b->fine_amount,2) : '—' }}</td>
                    <td>
                        @php
                            $badge = match($b->status) { 'borrowed' => 'primary', 'returned' => 'success', 'overdue' => 'danger', default => 'secondary' };
                        @endphp
                        <span class="badge bg-{{ $badge }}">{{ ucfirst($b->status) }}</span>
                    </td>
                    <td>
                        <a href="{{ route('borrowings.show', $b) }}" class="btn btn-sm btn-outline-info py-0">
                            <i class="bi bi-eye"></i>
                        </a>
                        @if($b->status !== 'returned')
                        <form method="POST" action="{{ route('borrowings.return', $b) }}" class="d-inline"
                              onsubmit="return confirm('Mark this book as returned?')">
                            @csrf
                            <button class="btn btn-sm btn-outline-success py-0">
                                <i class="bi bi-arrow-return-left"></i> Return
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No borrowings found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($borrowings->hasPages())
    <div class="card-footer bg-white">{{ $borrowings->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
