<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rental extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_id',
        'renter_name',
        'renter_contact',
        'quantity',
        'rent_date',
        'return_date',
        'status',
    ];

    protected $casts = [
        'rent_date' => 'date',
        'return_date' => 'date',
    ];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }
}
