<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    use HasFactory;

    public $timestamps = true;
    protected $table = 'result';
    protected $fillable = [
        'log_id',
        'order',
        'result',
        'attempt'
    ];

    public function log(): BelongsTo
    {
        return $this->belongsTo(Log::class, 'id', 'log_id');
    }
}
