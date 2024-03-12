<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HppTruck extends Model
{
    protected $guarded = [];
    public $timestamps = false;
    protected $primaryKey = 'KODE';
    //keytype string
    protected $keyType = 'string';
    use HasFactory;
    use SoftDeletes;

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

    public function truck()
    {
        return $this->belongsTo(Truck::class, 'KODE_TRUCK', 'KODE');
    }

    public function commodity()
    {
        return $this->belongsTo(Commodity::class, 'KODE_COMMODITY', 'KODE');
    }

    public function truck_route()
    {
        return $this->belongsTo(TruckRoute::class, 'KODE_RUTE_TRUCK', 'KODE');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'KODE_VENDOR', 'KODE');
    }
}
