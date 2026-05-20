@extends('layouts.app')
@section('title', $book->title)

@section('content')
<div class="row g-3">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><i class="bi bi-book"></i> {{ $book->title }}</span>
                <div class="d-flex gap-1">
                    <a href="{{ route('books.edit', $book) }}" class="btn btn-sm btn-warning">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <a href="{{ route('books.index') }}" class="btn btn-sm btn-outline-secondary">Back</a>
                </div>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3">Author</dt>       <dd class="col-sm-9">{{ $book->author }}</dd>
                    <dt class="col-sm-3">Category</dt>     <dd class="col-sm-9">{{ $book->category->name ?? '—' }}</dd>
                    <dt class="col-sm-3">ISBN</dt>         <dd class="col-sm-9">{{ $book->isbn ?? '—' }}</dd>
                    <dt class="col-sm-3">Publisher</dt>    <dd class="col-sm-9">{{ $book->publisher ?? '—' }}</dd>
                    <dt class="col-sm-3">Year</dt>         <dd class="col-sm-9">{{ $book->published_year ?? '—' }}</dd>
                    <dt class="col-sm-3">Language</dt>     <dd class="col-sm-9">{{ $book->language ?? '—' }}</dd>
                    <dt class="col-sm-3">Pages</dt>        <dd class="col-sm-9">{{ $book->pages ?? '—' }}</dd>
                    <dt class="col-sm-3">Location</dt>     <dd class="col-sm-9">{{ $book->location ?? '—' }}</dd>
                    <dt class="col-sm-3">Total Copies</dt> <dd class="col-sm-9">{{ $book->total_copies }}</dd>
                    <dt class="col-sm-3">Available</dt>
                    <dd class="col-sm-9">
                        <span class="badge {{ $book->available_copies > 0 ? 'bg-success' : 'bg-danger' }}">
                            {{ $book->available_copies }}
                        </span>
                    </dd>
                    @if($book->description)
                    <dt class="col-sm-3">Description</dt>  <dd class="col-sm-9">{{ $book->description }}</dd>
                    @endif
                </dl>
            </div>
        </div>

        {{-- Recent borrowings of this book --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Borrowing History</div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Member</th><th>Issued</th><th>Due</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($book->borrowings()->with('member')->latest()->take(10)->get() as $b)
                        <tr>
                            <td>{{ $b->member->name }}</td>
                            <td>{{ $b->issue_date->format('M d, Y') }}</td>
                            <td>{{ $b->due_date->format('M d, Y') }}</td>
                            <td><span class="badge bg-{{ $b->status === 'returned' ? 'success' : ($b->status === 'overdue' ? 'danger' : 'primary') }}">{{ ucfirst($b->status) }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-2">No borrowings yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        {{-- Quick borrow --}}
        @if($book->available_copies > 0)
        <div class="card border-0 shadow-sm mb-3 border-start border-success border-3">
            <div class="card-body text-center">
                <i class="bi bi-check-circle-fill text-success fs-2"></i>
                <div class="fw-semibold mt-1">Available to Borrow</div>
                <a href="{{ route('borrowings.create', ['book_id' => $book->id]) }}" class="btn btn-success btn-sm mt-2">
                    <i class="bi bi-arrow-left-right"></i> Issue This Book
                </a>
            </div>
        </div>
        @else
        <div class="card border-0 shadow-sm mb-3 border-start border-danger border-3">
            <div class="card-body text-center">
                <i class="bi bi-x-circle-fill text-danger fs-2"></i>
                <div class="fw-semibold mt-1">All Copies Borrowed</div>
                <a href="{{ route('reservations.create', ['book_id' => $book->id]) }}" class="btn btn-outline-warning btn-sm mt-2">
                    <i class="bi bi-bookmark"></i> Make Reservation
                </a>
            </div>
        </div>
        @endif

        {{-- Copies --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Physical Copies</div>
            <ul class="list-group list-group-flush">
                @foreach($book->copies as $copy)
                <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                    <span class="small">{{ $copy->copy_number }}</span>
                    <span class="badge {{ $copy->status === 'available' ? 'bg-success' : 'bg-warning text-dark' }}">
                        {{ ucfirst($copy->status) }}
                    </span>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endsection
