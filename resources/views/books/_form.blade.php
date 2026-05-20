{{-- Shared form fields for create & edit --}}
<div class="row g-3">
    <div class="col-md-8">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
               value="{{ old('title', $book->title ?? '') }}" required>
        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">ISBN</label>
        <input type="text" name="isbn" class="form-control @error('isbn') is-invalid @enderror"
               value="{{ old('isbn', $book->isbn ?? '') }}">
        @error('isbn') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Author <span class="text-danger">*</span></label>
        <input type="text" name="author" class="form-control @error('author') is-invalid @enderror"
               value="{{ old('author', $book->author ?? '') }}" required>
        @error('author') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Category <span class="text-danger">*</span></label>
        <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
            <option value="">Select category...</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ old('category_id', $book->category_id ?? '') == $cat->id ? 'selected' : '' }}>
                    {{ $cat->name }}
                </option>
            @endforeach
        </select>
        @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Publisher</label>
        <input type="text" name="publisher" class="form-control"
               value="{{ old('publisher', $book->publisher ?? '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Published Year</label>
        <input type="number" name="published_year" class="form-control"
               value="{{ old('published_year', $book->published_year ?? '') }}"
               min="1000" max="{{ date('Y') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Total Copies <span class="text-danger">*</span></label>
        <input type="number" name="total_copies" class="form-control @error('total_copies') is-invalid @enderror"
               value="{{ old('total_copies', $book->total_copies ?? 1) }}" min="1" required>
        @error('total_copies') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-3">
        <label class="form-label">Language</label>
        <input type="text" name="language" class="form-control"
               value="{{ old('language', $book->language ?? 'English') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Pages</label>
        <input type="number" name="pages" class="form-control"
               value="{{ old('pages', $book->pages ?? '') }}" min="1">
    </div>
    <div class="col-md-6">
        <label class="form-label">Shelf Location</label>
        <input type="text" name="location" class="form-control"
               value="{{ old('location', $book->location ?? '') }}" placeholder="e.g. A1-Shelf3">
    </div>
    <div class="col-md-6">
        <label class="form-label">Cover Image</label>
        <input type="file" name="cover_image" class="form-control" accept="image/*">
        @if(!empty($book->cover_image))
            <small class="text-muted">Current: {{ basename($book->cover_image) }}</small>
        @endif
    </div>
    <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="3">{{ old('description', $book->description ?? '') }}</textarea>
    </div>
</div>
