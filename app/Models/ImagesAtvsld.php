<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImagesAtvsld extends Model
{
    protected $fillable = [
        'atvsld_id',
        'image_path',
        'original_name',
        'mime_type',
    ];
    
    public function form(): BelongsTo
    {
        return $this->belongsTo(Atvsld::class);
    }
}
