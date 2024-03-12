<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use HasFactory;

    use SoftDeletes;
    protected $guarded = [];
    public $timestamps = false;
    protected $primaryKey = 'KODE';
    //keytype string
    protected $keyType = 'string';


    protected static function booted()
    {
        static::saving(function ($model) {
            // Loop through the model's attributes and convert string attributes to uppercase
            foreach ($model->getAttributes() as $key => $value) {
                if (is_string($value)) {
                    // Check if the value is not empty
                    if (!empty($value)) {
                        $model->{$key} = strtoupper($value);
                    }
                }
            }
        });
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'KODE_KOTA', 'KODE');
    }
}