<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class City extends Model
{
    protected $guarded = [];
    use HasFactory;
    use SoftDeletes;
    public $timestamps = false;
    protected $primaryKey = 'KODE';
    protected $keyType = 'string';

    // harbor and customer groups relation to check
    protected static $relations_to_check = ['harbors', 'customer_groups', 'warehouses'];

    public function province()
    {
        return $this->belongsTo(Province::class, 'KODE_PROVINSI', 'KODE');
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

    public function customer_groups()
    {
        return $this->hasMany(CustomerGroup::class, 'KODE_KOTA', 'KODE');
    }
    public function harbors()
    {
        return $this->hasMany(Harbor::class, 'KODE_KOTA', 'KODE');
    }

    public function warehouses()
    {
        return $this->hasMany(Warehouse::class, 'KODE_KOTA', 'KODE');
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
