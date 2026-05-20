@extends('layouts.app')
@section('title', 'Edit Member')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-pencil"></i> Edit Member — {{ $member->name }}
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('members.update', $member) }}">
                    @csrf @method('PUT')
                    @include('members._form')
                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-save"></i> Update Member
                        </button>
                        <a href="{{ route('members.show', $member) }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
