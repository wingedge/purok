<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dependent extends Model
{
    protected $fillable = [
        'member_id',
        'name',
        'relationship',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
