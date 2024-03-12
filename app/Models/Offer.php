<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Offer extends Model
{
    protected $guarded = [];
    use HasFactory;
    use SoftDeletes;
    public $timestamps = false;
    protected $primaryKey = 'KODE';
    protected $keyType = 'string';
}
