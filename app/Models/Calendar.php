<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Calendar extends Model
{
    protected $fillable = [
        'month',
        'year',
    ];

    public function teams()
    {
        return $this->belongsToMany(Team::class)
                ->withPivot('area_id')
                ->withTimestamps();
    }
}
