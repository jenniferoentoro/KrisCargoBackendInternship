<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CostRate extends Model
{
    protected $guarded = [];
    public $timestamps = false;
    protected $primaryKey = 'KODE';
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

    public function cost()
    {
        return $this->belongsTo(Cost::class, 'KODE_BIAYA', 'KODE');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'KODE_VENDOR', 'KODE');
    }

    public function harborOrigin()
    {
        return $this->belongsTo(Harbor::class, 'KODE_PELABUHAN_ASAL', 'KODE');
    }

    public function harborDestination()
    {
        return $this->belongsTo(Harbor::class, 'KODE_PELABUHAN_TUJUAN', 'KODE');
    }

    public function commodity()
    {
        return $this->belongsTo(Commodity::class, 'KODE_COMMODITY', 'KODE');
    }

    public function size()
    {
        return $this->belongsTo(Size::class, 'UK_KONTAINER', 'KODE');
    }
}
