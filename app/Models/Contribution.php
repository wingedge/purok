<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'week_start',
        'amount',
        'remarks',
    ];

    protected $casts = [
        'week_start' => 'date:Y-m-d',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
