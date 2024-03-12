<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Harbor extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $guarded = [];
    public $timestamps = false;
    protected $primaryKey = 'KODE';
    //type string
    protected $keyType = 'string';

    protected static $relations_to_check = ['cost_rates', 'special_prices', 'thc_lolo_port_loadings', 'thc_lolo_port_discharges'];

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

    public function city()
    {
        return $this->belongsTo(City::class, 'KODE_KOTA', 'KODE');
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

    public function thc_lolo_port_discharges()
    {
        return $this->hasMany(ThcLolo::class, 'KODE_PELABUHAN', 'KODE');
    }
    public function special_prices()
    {

        return $this->hasMany(SpecialPrice::class, 'KODE_POL', 'KODE')->orWhere('KODE_POD', 'KODE');
    }
    public function thc_lolo_port_loadings()
    {
        return $this->hasMany(ThcLolo::class, 'KODE_PELABUHAN', 'KODE');
    }
    public function cost_rates()
    {
        return $this->hasMany(CostRate::class, 'KODE_PELABUHAN_ASAL', 'KODE')->orWhere('KODE_PELABUHAN_TUJUAN', 'KODE');
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
