<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Joa extends Model
{
    protected $guarded = [];
    use HasFactory;
    use SoftDeletes;
    public $timestamps = false;
    protected $primaryKey = 'NOMOR';
    protected $keyType = 'string';

    protected $casts = [
        'PERSEN_ASURANSI' => 'float',
        // Other attribute castings
    ];
}
