@extends('layouts.app')
@section('title','Reports')
@section('subtitle','Export & analyze library data')

@section('content')
<div class="row g-3">
    <div class="col-md-4">
        <div class="card t-card h-100">
            <div class="card-body text-center py-4">
                <div class="mb-3" style="font-size:2.5rem">⚠️</div>
                <h6 class="fw-700">Overdue Books</h6>
                <p class="text-muted small">All books past their due date with outstanding fines.</p>
                <div class="d-grid gap-2 mt-3">
                    <a href="{{ route('reports.overdue') }}" class="btn btn-danger btn-sm">
                        <i class="bi bi-eye me-1"></i>View Report
                    </a>
                    <a href="{{ route('reports.export.overdue') }}" class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-download me-1"></i>Export CSV
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card t-card h-100">
            <div class="card-body text-center py-4">
                <div class="mb-3" style="font-size:2.5rem">📅</div>
                <h6 class="fw-700">Daily Report</h6>
                <p class="text-muted small">Books issued and returned today with fine summary.</p>
                <div class="d-grid gap-2 mt-3">
                    <a href="{{ route('reports.daily') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-eye me-1"></i>View Today
                    </a>
                    <a href="{{ route('reports.export.borrowings') }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-download me-1"></i>Export All CSV
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card t-card h-100">
            <div class="card-body text-center py-4">
                <div class="mb-3" style="font-size:2.5rem">📊</div>
                <h6 class="fw-700">Monthly Summary</h6>
                <p class="text-muted small">Monthly breakdown of borrowings, returns, and fines.</p>
                <div class="d-grid gap-2 mt-3">
                    <a href="{{ route('reports.monthly') }}" class="btn btn-success btn-sm">
                        <i class="bi bi-eye me-1"></i>View Report
                    </a>
                    <a href="{{ route('reports.export.members') }}" class="btn btn-outline-success btn-sm">
                        <i class="bi bi-download me-1"></i>Export Members CSV
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
