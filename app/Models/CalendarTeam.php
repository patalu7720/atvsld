<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalendarTeam extends Model
{
    protected $table = 'calendar_team';

    public function calendar() {
        return $this->belongsTo(Calendar::class);
    }

    public function team() {
        return $this->belongsTo(Team::class);
    }

    public function area() {
        return $this->belongsTo(Area::class);
    }
}

