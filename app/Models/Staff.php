<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Staff extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $guarded = [];
    public $timestamps = false;
    protected $primaryKey = 'KODE';
    //table name
    protected $table = 'staffs';


    public function positions()
    {
        return $this->belongsTo(Position::class, 'KODE_JABATAN', 'KODE');
    }

    public function location()
    {
        return $this->belongsTo(Warehouse::class, 'KODE_LOKASI', 'KODE');
    }
}
