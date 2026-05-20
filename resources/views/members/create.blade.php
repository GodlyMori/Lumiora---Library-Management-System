@extends('layouts.app')
@section('title', 'Add Member')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-person-plus"></i> Register New Member
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('members.store') }}">
                    @csrf
                    @include('members._form')
                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Register Member
                        </button>
                        <a href="{{ route('members.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
