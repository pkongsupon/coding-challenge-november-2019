<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Log extends Model
{
    use HasFactory;

    public $timestamps = true;
    protected $table = 'log';
    protected $fillable = [
        'left',
        'right',
        'cnt_result',
        'iteration'
    ];

    public function result(): HasMany
    {
        return $this->hasMany(Result::class, 'log_id', 'id');
    }
}
