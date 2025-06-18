<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $fillable = [
        'id',
        'area_id',
        'name',
        'plant',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
