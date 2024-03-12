<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class TruckPrice extends Model
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

    public function commodity()
    {
        return $this->belongsTo(Commodity::class, 'KODE_COMMODITY', 'KODE');
    }

    public function truck()
    {
        return $this->belongsTo(Truck::class, 'KODE_TRUCK', 'KODE');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'KODE_CUSTOMER', 'KODE');
    }

    public function truck_route()
    {
        return $this->belongsTo(TruckRoute::class, 'KODE_RUTE_TRUCK', 'KODE');
    }

    // public function getCustomValues()
    // {
    //     $attributes = parent::getAttributes();

    //     foreach ($attributes as $key => $value) {
    //         //format date to dd-mm-yyyy
    //         if ($this->isDateAttribute($value)) {
    //             $attributes[$key] = Carbon::parse($value)->format('d-m-Y');
    //         }
    //         // convert integer to using . as thousand separator
    //         else if (is_numeric($value)) {
    //             $attributes[$key] = number_format($value, 0, '', '.');
    //         }
    //     }

    //     return $attributes;
    // }

    // public function isDateAttribute($value)
    // {
    //     // Use regex pattern to detect date format (yyyy-mm-dd)
    //     $pattern = '/^\d{4}-\d{2}-\d{2}$/';
    //     return preg_match($pattern, $value) === 1;
    // }
}
