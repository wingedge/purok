<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        'birthday',
        'indigent'
    ];

    protected $casts = [
        'birthday' => 'date',
        'indigent' => 'boolean',
    ];


    public function dependents()
    {
        return $this->hasMany(Dependent::class);
    }

    public function isIndigent(): bool
    {
        return $this->indigent;
    }
}
