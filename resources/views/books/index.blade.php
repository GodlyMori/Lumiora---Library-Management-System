@extends('layouts.app')
@section('title', 'Books')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Books</h5>
    <a href="{{ route('books.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> Add Book
    </a>
</div>

{{-- Search / Filter --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Search title, author, ISBN..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="category" class="form-select form-select-sm">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="available" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="1" {{ request('available') ? 'selected' : '' }}>Available Only</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button class="btn btn-primary btn-sm flex-fill">Search</button>
                <a href="{{ route('books.index') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
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
                    <th>Title</th>
                    <th>Author</th>
                    <th>Category</th>
                    <th>Copies</th>
                    <th>Available</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($books as $book)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        <a href="{{ route('books.show', $book) }}" class="text-decoration-none fw-semibold">
                            {{ $book->title }}
                        </a>
                        @if($book->isbn)<br><small class="text-muted">ISBN: {{ $book->isbn }}</small>@endif
                    </td>
                    <td>{{ $book->author }}</td>
                    <td><span class="badge bg-secondary">{{ $book->category->name ?? '—' }}</span></td>
                    <td>{{ $book->total_copies }}</td>
                    <td>
                        <span class="badge {{ $book->available_copies > 0 ? 'bg-success' : 'bg-danger' }}">
                            {{ $book->available_copies }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('books.show', $book) }}" class="btn btn-sm btn-outline-info py-0">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="{{ route('books.edit', $book) }}" class="btn btn-sm btn-outline-warning py-0">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form method="POST" action="{{ route('books.destroy', $book) }}" class="d-inline"
                              onsubmit="return confirm('Delete this book?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger py-0"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No books found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($books->hasPages())
    <div class="card-footer bg-white">{{ $books->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
