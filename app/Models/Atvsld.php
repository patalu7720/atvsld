<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Atvsld extends Model
{
    protected $fillable = [
        'area_id',
        'user_id',
        'problem_detected',
        'risk',
        'solution',
        'start_date',
        'end_date',
        'status',
        'solution_email'
    ];

    public function areas()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function images()
    {
        return $this->hasMany(ImagesAtvsld::class, 'atvsld_id');
    }
    
    public function teams()
    {
        return $this->belongsTo(Team::class);
    }
}
