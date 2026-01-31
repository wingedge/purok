<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $fillable = [
        'name',        
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

    public function getIsIndigentAttribute(): string
    {
        return $this->indigent ? 'Yes' : 'No';
    }

    public function contributions()
    {
        return $this->hasMany(Contribution::class);
    }

}
