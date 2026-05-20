@extends('layouts.app')
@section('title', 'New Reservation')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-bookmark"></i> Create Reservation
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('reservations.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Member <span class="text-danger">*</span></label>
                        <select name="member_id" class="form-select @error('member_id') is-invalid @enderror" required>
                            <option value="">Select member...</option>
                            @foreach($members as $m)
                                <option value="{{ $m->id }}" {{ old('member_id', request('member_id')) == $m->id ? 'selected' : '' }}>
                                    {{ $m->name }} ({{ $m->membership_id }})
                                </option>
                            @endforeach
                        </select>
                        @error('member_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Book <span class="text-danger">*</span></label>
                        <select name="book_id" class="form-select @error('book_id') is-invalid @enderror" required>
                            <option value="">Select book...</option>
                            @foreach($books as $book)
                                <option value="{{ $book->id }}" {{ old('book_id', request('book_id')) == $book->id ? 'selected' : '' }}>
                                    {{ $book->title }} ({{ $book->available_copies }} available)
                                </option>
                            @endforeach
                        </select>
                        @error('book_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Expiry Date <span class="text-danger">*</span></label>
                        <input type="date" name="expiry_date" class="form-control @error('expiry_date') is-invalid @enderror"
                               value="{{ old('expiry_date', $defaultExpiry) }}" required>
                        @error('expiry_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-bookmark-check"></i> Reserve Book
                        </button>
                        <a href="{{ route('reservations.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
