<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessType extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $guarded = [];
    public $timestamps = false;
    protected $primaryKey = 'KODE';
    //keytype string
    protected $keyType = 'string';

    protected static $relations_to_check = ['customers'];

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

    public function customers()
    {
        return $this->hasMany(Customer::class, 'KODE_USAHA', 'KODE');
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
