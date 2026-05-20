@extends('layouts.app')
@section('title', 'Categories')

@section('content')
<div class="row g-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                {{ isset($category) ? 'Edit Category' : 'Add Category' }}
            </div>
            <div class="card-body">
                @if(isset($category))
                <form method="POST" action="{{ route('categories.update', $category) }}">
                    @csrf @method('PUT')
                @else
                <form method="POST" action="{{ route('categories.store') }}">
                    @csrf
                @endif
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $category->name ?? '') }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2">{{ old('description', $category->description ?? '') }}</textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                            {{ isset($category) ? 'Update' : 'Add Category' }}
                        </button>
                        @if(isset($category))
                        <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary btn-sm">Cancel</a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">All Categories</div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Name</th><th>Description</th><th>Books</th><th>Actions</th></tr></thead>
                    <tbody>
                        @forelse($categories as $cat)
                        <tr>
                            <td class="fw-semibold">{{ $cat->name }}</td>
                            <td class="text-muted small">{{ $cat->description ?? '—' }}</td>
                            <td><span class="badge bg-secondary">{{ $cat->books_count }}</span></td>
                            <td>
                                <a href="{{ route('categories.edit', $cat) }}" class="btn btn-sm btn-outline-warning py-0">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('categories.destroy', $cat) }}" class="d-inline"
                                      onsubmit="return confirm('Delete this category?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger py-0"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">No categories yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
