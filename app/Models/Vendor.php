<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $guarded = [];
    public $timestamps = false;
    protected $primaryKey = 'KODE';
    protected $keyType = 'string';

    protected static $relations_to_check = ['ships', 'cost_rates', 'thc_lolo_port_loadings', 'thc_lolo_port_discharges'];

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

    public function ships()
    {
        return $this->hasMany(Ship::class, 'KODE_VENDOR', 'KODE');
    }

    public function cost_rates()
    {
        return $this->hasMany(CostRate::class, 'KODE_VENDOR', 'KODE');
    }

    public function thc_lolo_port_loadings()
    {
        return $this->hasMany(ThcLolo::class, 'KODE_VENDOR', 'KODE');
    }

    public function thc_lolo_port_discharges()
    {
        return $this->hasMany(ThcLolo::class, 'KODE_VENDOR', 'KODE');
    }

    public function cityKTP()
    {
        return $this->belongsTo(City::class, 'KODE_KOTA_KTP', 'KODE');
    }

    public function cityNPWP()
    {
        return $this->belongsTo(City::class, 'KODE_KOTA_NPWP', 'KODE');
    }

    public function vendor_type()
    {
        return $this->belongsTo(VendorType::class, 'KODE_JENIS_VENDOR', 'KODE');
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
