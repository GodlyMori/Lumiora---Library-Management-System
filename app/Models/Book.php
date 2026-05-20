<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id', 'title', 'author', 'isbn', 'publisher',
        'published_year', 'description', 'language', 'pages',
        'cover_image', 'location', 'total_copies', 'available_copies', 'is_active',
    ];

    protected $casts = [
        'is_active'        => 'boolean',
        'pages'            => 'integer',
        'total_copies'     => 'integer',
        'available_copies' => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────────────
    public function category()    { return $this->belongsTo(BookCategory::class, 'category_id'); }
    public function copies()      { return $this->hasMany(BookCopy::class); }
    public function borrowings()  { return $this->hasMany(Borrowing::class); }
    public function reservations(){ return $this->hasMany(Reservation::class); }

    // ── Helpers ────────────────────────────────────────────────────
    public function isAvailable(): bool { return $this->available_copies > 0; }

    public function getAvailableCopy(): ?BookCopy
    {
        return $this->copies()->where('status', 'available')->first();
    }

    // ── Scopes ─────────────────────────────────────────────────────
    public function scopeActive($q)                   { return $q->where('is_active', true); }
    public function scopeAvailable($q)                { return $q->where('available_copies', '>', 0); }
    public function scopeSearch($q, string $term)
    {
        return $q->where(fn($s) =>
            $s->where('title',  'like', "%{$term}%")
              ->orWhere('author','like', "%{$term}%")
              ->orWhere('isbn',  'like', "%{$term}%")
        );
    }
}
