<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CostGroup extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $guarded = [];
    public $timestamps = false;
    protected $primaryKey = 'KODE';
    //keytpe string
    protected $keyType = 'string';

    protected static $relations_to_check = ['costs', 'accounts'];

    public function costs()
    {
        return $this->hasMany(Cost::class, 'KD_KEL_BIAYA', 'KODE');
    }

    public function accounts()
    {
        return $this->hasMany(Account::class, 'KODE_COST_GROUP', 'KODE');
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
