{{-- Shared form fields for create & edit --}}
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Full Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $member->name ?? '') }}" required>
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
               value="{{ old('email', $member->email ?? '') }}">
        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Phone</label>
        <input type="text" name="phone" class="form-control"
               value="{{ old('phone', $member->phone ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Member Type <span class="text-danger">*</span></label>
        <select name="member_type" class="form-select @error('member_type') is-invalid @enderror" required>
            @foreach(['student','faculty','staff','public'] as $type)
                <option value="{{ $type }}" {{ old('member_type', $member->member_type ?? '') === $type ? 'selected' : '' }}>
                    {{ ucfirst($type) }}
                </option>
            @endforeach
        </select>
        @error('member_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
            @foreach(['active','inactive','suspended'] as $s)
                <option value="{{ $s }}" {{ old('status', $member->status ?? 'active') === $s ? 'selected' : '' }}>
                    {{ ucfirst($s) }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Membership Start <span class="text-danger">*</span></label>
        <input type="date" name="membership_start_date" class="form-control @error('membership_start_date') is-invalid @enderror"
               value="{{ old('membership_start_date', isset($member) ? $member->membership_start_date->format('Y-m-d') : date('Y-m-d')) }}" required>
        @error('membership_start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Membership Expiry <span class="text-danger">*</span></label>
        <input type="date" name="membership_expiry_date" class="form-control @error('membership_expiry_date') is-invalid @enderror"
               value="{{ old('membership_expiry_date', isset($member) ? $member->membership_expiry_date->format('Y-m-d') : date('Y-m-d', strtotime('+1 year'))) }}" required>
        @error('membership_expiry_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Max Books Allowed</label>
        <input type="number" name="max_books" class="form-control"
               value="{{ old('max_books', $member->max_books ?? 5) }}" min="1" max="20">
    </div>
    <div class="col-12">
        <label class="form-label">Address</label>
        <textarea name="address" class="form-control" rows="2">{{ old('address', $member->address ?? '') }}</textarea>
    </div>
</div>
