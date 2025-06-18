<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $fillable = [
        'name',
        'plant',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function atvslds()
    {
        return $this->hasMany(Atvsld::class);
    }


    public function calendars()
    {
        return $this->belongsToMany(Calendar::class)
            ->withPivot('area_id')
            ->withTimestamps();
    }
}
