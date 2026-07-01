<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommunityFundingEvent extends Model
{
    protected $fillable = [
        'name',
        'description',
        'deadline',
        'goal_amount',
    ];

    protected $casts = [
        'deadline' => 'date:Y-m-d',
        'goal_amount' => 'decimal:2',
    ];

    public function donations(): HasMany
    {
        return $this->hasMany(CommunityFundingDonation::class);
    }

    public function getActualAmountAttribute(): float
    {
        if (array_key_exists('donations_sum_amount', $this->attributes)) {
            return (float) $this->attributes['donations_sum_amount'];
        }

        return (float) $this->donations()->sum('amount');
    }

    public function getProgressPercentageAttribute(): float
    {
        $goalAmount = (float) $this->goal_amount;

        if ($goalAmount <= 0) {
            return 0.0;
        }

        return round(min(100, ($this->actual_amount / $goalAmount) * 100), 2);
    }

    public function getStatusAttribute(): string
    {
        if ($this->actual_amount >= (float) $this->goal_amount && (float) $this->goal_amount > 0) {
            return 'Completed';
        }

        if ($this->deadline !== null && $this->deadline->isPast()) {
            return 'Overdue';
        }

        return 'Active';
    }
}
