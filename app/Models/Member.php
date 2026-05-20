<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'membership_id', 'name', 'email', 'phone', 'address',
        'member_type', 'status', 'membership_start_date',
        'membership_expiry_date', 'max_books',
    ];

    protected $casts = [
        'membership_start_date'  => 'date',
        'membership_expiry_date' => 'date',
    ];

    // ── Relationships ──────────────────────────────────────────────
    public function borrowings()  { return $this->hasMany(Borrowing::class); }
    public function reservations(){ return $this->hasMany(Reservation::class); }

    public function activeBorrowings()
    {
        return $this->hasMany(Borrowing::class)->whereIn('status', ['borrowed', 'overdue']);
    }

    // ── Helpers ────────────────────────────────────────────────────
    /** Check if the member is allowed to borrow more books */
    public function canBorrow(): bool
    {
        return $this->status === 'active'
            && $this->membership_expiry_date->isFuture()
            && $this->activeBorrowings()->count() < $this->max_books;
    }

    /** Total unpaid fines (calculated from borrowings) */
    public function getOutstandingFineAttribute(): float
    {
        return (float) $this->borrowings()
            ->where('fine_paid', false)
            ->where('fine_amount', '>', 0)
            ->sum('fine_amount');
    }
}
