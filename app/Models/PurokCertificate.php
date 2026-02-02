<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurokCertificate extends Model
{
    protected $fillable = ['member_id', 'request_date', 'purpose'];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
