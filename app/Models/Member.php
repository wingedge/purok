<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $fillable = [
        'full_name',
        'address',
        'phone',
        'email',
    ];

    public function dependents()
    {
        return $this->hasMany(Dependent::class);
    }
}
