<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $fillable = [
        'reservation_number', 'member_id', 'book_id', 'queue_position',
        'status', 'reserved_date', 'expiry_date', 'notification_date', 'notes',
    ];

    protected $casts = [
        'reserved_date'     => 'date',
        'expiry_date'       => 'date',
        'notification_date' => 'date',
    ];

    public function member() { return $this->belongsTo(Member::class); }
    public function book()   { return $this->belongsTo(Book::class); }

    public function isExpired(): bool
    {
        return $this->status === 'pending' && $this->expiry_date->isPast();
    }
}
