@extends('layouts.app')
@section('title', 'Issue Books')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-9">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-arrow-left-right"></i> Issue Books to Member
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('borrowings.store') }}" id="issueBookForm">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label">Member <span class="text-danger">*</span></label>
                        <select name="member_id" class="form-select @error('member_id') is-invalid @enderror" required>
                            <option value="">Select member...</option>
                            @foreach($members as $m)
                                <option value="{{ $m->id }}"
                                    {{ old('member_id', request('member_id')) == $m->id ? 'selected' : '' }}
                                    {{ $m->status !== 'active' ? 'disabled' : '' }}>
                                    {{ $m->name }} ({{ $m->membership_id }})
                                    {{ $m->status !== 'active' ? '[' . $m->status . ']' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('member_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- Books Section -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0">Books <span class="text-danger">*</span></label>
                            <button type="button" class="btn btn-sm btn-success" id="addBookBtn">
                                <i class="bi bi-plus-circle"></i> Add Book
                            </button>
                        </div>
                        
                        <div id="booksContainer">
                            <!-- First book row (always present) -->
                            <div class="book-row mb-2 p-3 border rounded bg-light">
                                <div class="row g-2 align-items-start">
                                    <div class="col-md-10">
                                        <select name="books[0][book_id]" class="form-select book-select @error('books.0.book_id') is-invalid @enderror" required>
                                            <option value="">Select book...</option>
                                            @foreach($books as $book)
                                                <option value="{{ $book->id }}"
                                                    data-available="{{ $book->available_copies }}"
                                                    {{ old('books.0.book_id', request('book_id')) == $book->id ? 'selected' : '' }}
                                                    {{ $book->available_copies <= 0 ? 'disabled' : '' }}>
                                                    {{ $book->title }} — {{ $book->author }}
                                                    ({{ $book->available_copies }} available)
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('books.0.book_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-2 text-end">
                                        <span class="text-muted small">Book 1</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Due Date <span class="text-danger">*</span></label>
                        <input type="date" name="due_date" class="form-control @error('due_date') is-invalid @enderror"
                               value="{{ old('due_date', $defaultDueDate) }}" required>
                        @error('due_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <small class="text-muted">All books will have the same due date</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Issue Books
                        </button>
                        <a href="{{ route('borrowings.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// This script handles adding and removing book rows dynamically
let bookCounter = 1; // Start at 1 since we already have book 0
const booksData = @json($books); // Get all books data from PHP

document.getElementById('addBookBtn').addEventListener('click', function() {
    const container = document.getElementById('booksContainer');
    
    // Create a new book row
    const newRow = document.createElement('div');
    newRow.className = 'book-row mb-2 p-3 border rounded bg-light';
    newRow.innerHTML = `
        <div class="row g-2 align-items-start">
            <div class="col-md-10">
                <select name="books[${bookCounter}][book_id]" class="form-select book-select" required>
                    <option value="">Select book...</option>
                    ${booksData.map(book => `
                        <option value="${book.id}" 
                            data-available="${book.available_copies}"
                            ${book.available_copies <= 0 ? 'disabled' : ''}>
                            ${book.title} — ${book.author} (${book.available_copies} available)
                        </option>
                    `).join('')}
                </select>
            </div>
            <div class="col-md-2 text-end">
                <button type="button" class="btn btn-sm btn-danger remove-book-btn">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
    `;
    
    container.appendChild(newRow);
    bookCounter++;
    
    // Add event listener for the remove button
    newRow.querySelector('.remove-book-btn').addEventListener('click', function() {
        newRow.remove();
    });
});

// Prevent duplicate book selection
document.getElementById('issueBookForm').addEventListener('submit', function(e) {
    const selectedBooks = [];
    const bookSelects = document.querySelectorAll('.book-select');
    let hasDuplicate = false;
    
    bookSelects.forEach(select => {
        const value = select.value;
        if (value && selectedBooks.includes(value)) {
            hasDuplicate = true;
            alert('You cannot issue the same book multiple times!');
            e.preventDefault();
            return;
        }
        if (value) {
            selectedBooks.push(value);
        }
    });
});
</script>
@endpush
@endsection
