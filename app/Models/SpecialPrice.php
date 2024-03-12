<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SpecialPrice extends Model
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

    public function product()
    {
        return $this->belongsTo(Product::class, 'KODE_PRODUK', 'KODE');
    }

    public function pol()
    {
        return $this->belongsTo(Harbor::class, 'KODE_POL', 'KODE');
    }

    public function pod()
    {
        return $this->belongsTo(Harbor::class, 'KODE_POD', 'KODE');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'KODE_CUSTOMER', 'KODE');
    }
}
