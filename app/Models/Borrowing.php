<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Borrowing extends Model
{
    protected $fillable = [
        'borrowing_number', 'member_id', 'book_id', 'book_copy_id',
        'issued_by', 'issue_date', 'due_date', 'actual_return_date',
        'status', 'overdue_days', 'fine_amount', 'fine_paid', 'notes',
    ];

    protected $casts = [
        'issue_date'         => 'date',
        'due_date'           => 'date',
        'actual_return_date' => 'date',
        'fine_paid'          => 'boolean',
    ];

    // ── Relationships ──────────────────────────────────────────────
    public function member()   { return $this->belongsTo(Member::class); }
    public function book()     { return $this->belongsTo(Book::class); }
    public function bookCopy() { return $this->belongsTo(BookCopy::class); }
    public function issuedBy() { return $this->belongsTo(User::class, 'issued_by'); }

    // ── Helpers ────────────────────────────────────────────────────
    /** How many days overdue (0 if not overdue) */
    public function getOverdueDaysAttribute(): int
    {
        $returnDate = $this->actual_return_date ?? now()->startOfDay();
        return max(0, $this->due_date->diffInDays($returnDate, false) * -1);
    }

    public function isOverdue(): bool
    {
        return $this->status !== 'returned' && now()->isAfter($this->due_date);
    }
}
