@extends('layouts.app')
@section('title', 'Reservations')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Reservations</h5>
    <a href="{{ route('reservations.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> New Reservation
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Member</th>
                    <th>Book</th>
                    <th>Queue</th>
                    <th>Reserved</th>
                    <th>Expiry</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reservations as $r)
                <tr>
                    <td><small class="text-muted">{{ $r->reservation_number }}</small></td>
                    <td>{{ $r->member->name }}</td>
                    <td class="text-truncate" style="max-width:160px">{{ $r->book->title }}</td>
                    <td><span class="badge bg-secondary">#{{ $r->queue_position }}</span></td>
                    <td>{{ $r->reserved_date->format('M d, Y') }}</td>
                    <td>{{ $r->expiry_date->format('M d, Y') }}</td>
                    <td>
                        @php
                            $badge = match($r->status) {
                                'pending'   => 'warning text-dark',
                                'available' => 'success',
                                'fulfilled' => 'primary',
                                'cancelled' => 'secondary',
                                'expired'   => 'danger',
                                default     => 'secondary'
                            };
                        @endphp
                        <span class="badge bg-{{ $badge }}">{{ ucfirst($r->status) }}</span>
                    </td>
                    <td>
                        @if(in_array($r->status, ['pending','available']))
                        <form method="POST" action="{{ route('reservations.cancel', $r) }}" class="d-inline"
                              onsubmit="return confirm('Cancel this reservation?')">
                            @csrf
                            <button class="btn btn-sm btn-outline-danger py-0">
                                <i class="bi bi-x"></i> Cancel
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No reservations found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($reservations->hasPages())
    <div class="card-footer bg-white">{{ $reservations->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
