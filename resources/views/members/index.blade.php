@extends('layouts.app')
@section('title','Members')
@section('subtitle','Registered library members')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div></div>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.export.members') }}" class="btn btn-outline-success btn-sm no-print">
            <i class="bi bi-download me-1"></i>Export CSV
        </a>
        <a href="{{ route('members.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-person-plus me-1"></i>Add Member
        </a>
    </div>
</div>

<div class="card t-card mb-3 no-print">
    <div class="card-body py-2">
        <form method="GET" class="row g-2">
            <div class="col-md-4 srch-wrap">
                <i class="bi bi-search" style="font-size:.85rem"></i>
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Name, email, membership ID..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="active"    {{ request('status') === 'active'    ? 'selected' : '' }}>Active</option>
                    <option value="inactive"  {{ request('status') === 'inactive'  ? 'selected' : '' }}>Inactive</option>
                    <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="membership_status" class="form-select form-select-sm">
                    <option value="">All Memberships</option>
                    <option value="valid"          {{ request('membership_status') === 'valid'          ? 'selected' : '' }}>Valid</option>
                    <option value="expiring_soon"  {{ request('membership_status') === 'expiring_soon'  ? 'selected' : '' }}>Expiring Soon</option>
                    <option value="expired"        {{ request('membership_status') === 'expired'        ? 'selected' : '' }}>Expired</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button class="btn btn-primary btn-sm flex-fill">Search</button>
                <a href="{{ route('members.index') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
            </div>
        </form>
    </div>
</div>

<div class="card t-card">
    <div class="card-header">
        <span><i class="bi bi-people me-2"></i>Members</span>
        <span class="badge bs-borrowed">{{ $members->total() }} records</span>
    </div>
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Name</th><th>Type</th><th>Expiry</th>
                    <th>Borrowed</th><th>Overdue</th><th>Unpaid Fine</th>
                    <th>Status</th><th class="no-print">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($members as $m)
                <tr>
                    <td>
                        <a href="{{ route('members.show', $m->id) }}" class="text-decoration-none fw-500">
                            {{ $m->name }}
                        </a>
                        <div class="small text-muted">{{ $m->membership_id }}</div>
                    </td>
                    <td><span class="badge bs-pending">{{ ucfirst($m->member_type) }}</span></td>
                    <td>
                        @php $exp = \Carbon\Carbon::parse($m->membership_expiry_date); @endphp
                        @if($m->membership_status === 'expired')
                            <span class="text-danger fw-500 small">Expired {{ $exp->format('M d, Y') }}</span>
                        @elseif($m->membership_status === 'expiring_soon')
                            <span class="text-warning fw-500 small">{{ $exp->format('M d, Y') }} ⚠️</span>
                        @else
                            <span class="small">{{ $exp->format('M d, Y') }}</span>
                        @endif
                    </td>
                    <td><span class="badge bs-borrowed">{{ $m->currently_borrowed }}</span></td>
                    <td>
                        @if($m->total_overdue > 0)
                            <span class="badge bs-overdue">{{ $m->total_overdue }}</span>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td>
                        @if($m->unpaid_fines > 0)
                            <span class="text-danger fw-500 small">₱{{ number_format($m->unpaid_fines,2) }}</span>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td>
                        @php $scls = ['active'=>'bs-available','suspended'=>'bs-overdue','inactive'=>'bs-cancelled'][$m->status] ?? 'bs-cancelled' @endphp
                        <span class="badge {{ $scls }}">{{ ucfirst($m->status) }}</span>
                    </td>
                    <td class="no-print">
                        <a href="{{ route('members.show', $m->id) }}" class="btn btn-sm btn-outline-primary py-0">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="{{ route('members.edit', $m->id) }}" class="btn btn-sm btn-outline-warning py-0">
                            <i class="bi bi-pencil"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8">
                    <div class="empty-state"><i class="bi bi-people"></i><p>No members found.</p></div>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($members->hasPages())
    <div class="card-footer bg-white">{{ $members->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
