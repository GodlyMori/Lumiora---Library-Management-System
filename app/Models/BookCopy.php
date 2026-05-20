<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookCopy extends Model
{
    protected $fillable = ['book_id', 'copy_number', 'condition', 'status', 'notes'];

    public function book()      { return $this->belongsTo(Book::class); }
    public function borrowings(){ return $this->hasMany(Borrowing::class); }
    public function isAvailable(): bool { return $this->status === 'available'; }
}
