<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    protected $guarded = [];
    use HasFactory;
    use SoftDeletes;
    public $timestamps = false;
    protected $primaryKey = 'KODE';
    //keytype string
    protected $keyType = 'string';


    protected static $relations_to_check = ['special_prices'];

    public function customerGroup()
    {
        return $this->belongsTo(CustomerGroup::class, 'KODE_GROUP', 'KODE');
    }

    public function cityKTP()
    {
        return $this->belongsTo(City::class, 'KODE_KOTA_KTP', 'KODE');
    }

    public function cityNPWP()
    {
        return $this->belongsTo(City::class, 'KODE_KOTA_NPWP', 'KODE');
    }

    public function businessType()
    {
        return $this->belongsTo(BusinessType::class, 'KODE_USAHA', 'KODE');
    }

    public function salesStaff()
    {
        return $this->belongsTo(Staff::class, 'KODE_SALES', 'KODE');
    }

    public function arStaff()
    {
        return $this->belongsTo(Staff::class, 'KODE_AR', 'KODE');
    }

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

    public function special_prices()
    {
        return $this->hasMany(SpecialPrice::class, 'KODE_CUSTOMER', 'KODE');
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
