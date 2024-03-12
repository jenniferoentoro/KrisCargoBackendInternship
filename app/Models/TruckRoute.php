<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TruckRoute extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];
    public $timestamps = false;
    protected $primaryKey = 'KODE';
    //primary key type string
    protected $keyType = 'string';

    protected static $relations_to_check = ['truck_prices'];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($resource) {
            foreach (static::$relations_to_check as $relation) {
                if ($resource->{$relation}()->count() > 0) {
                    throw new \Exception("Cannot delete province because it has related records.");
                }
            }
        });
    }

    public function hasRelatedRecords()
    {
        foreach (static::$relations_to_check as $relation) {
            if ($this->{$relation}()->count() > 0) {
                return true;
            }
        }

        return false;
    }

    // public function truck_prices()
    // {
    //     return $this->hasMany(TruckPrice::class, 'KD_RUTE_TRUCK', 'KODE');
    // }

    public function city_from()
    {
        return $this->belongsTo(City::class, 'KD_KOTA_ASAL', 'KODE');
    }

    public function city_to()
    {
        return $this->belongsTo(City::class, 'KD_KOTA_TUJUAN', 'KODE');
    }

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
}
