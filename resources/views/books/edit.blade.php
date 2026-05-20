@extends('layouts.app')
@section('title', 'Edit Book')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-pencil"></i> Edit Book — {{ $book->title }}
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('books.update', $book) }}" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    @include('books._form')
                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-save"></i> Update Book
                        </button>
                        <a href="{{ route('books.show', $book) }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
