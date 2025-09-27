<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'booking_trx_id',
        'user_id',
        'course_id',
        'pricing_id',
        'course_batch_id',
        'sub_total_amount',
        'grand_total_amount',
        'total_tax_amount',
        'is_paid',
        'payment_type',
        'proof',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function pricing(): BelongsTo
    {
        return $this->belongsTo(Pricing::class);
    }

    public function courseBatch(): BelongsTo
    {
        return $this->belongsTo(CourseBatch::class);
    }
}
