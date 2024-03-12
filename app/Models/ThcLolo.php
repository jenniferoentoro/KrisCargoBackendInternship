<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ThcLolo extends Model
{
    use HasFactory;

    use SoftDeletes;
    protected $guarded = [];
    public $timestamps = false;
    protected $primaryKey = 'KODE';
    protected $keyType = 'string';

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

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'KODE_VENDOR', 'KODE');
    }

    public function harbor()
    {
        return $this->belongsTo(Harbor::class, 'KODE_PELABUHAN', 'KODE');
    }

    public function size()
    {
        return $this->belongsTo(Size::class, 'KODE_UK_KONTAINER', 'KODE');
    }

    public function containerType()
    {
        return $this->belongsTo(ContainerType::class, 'KODE_JENIS_KONTAINER', 'KODE');
    }
}
