<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunityFundingDonation extends Model
{
    protected $fillable = [
        'community_funding_event_id',
        'member_id',
        'amount',
        'received_at',
        'remarks',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'received_at' => 'date:Y-m-d',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(CommunityFundingEvent::class, 'community_funding_event_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
