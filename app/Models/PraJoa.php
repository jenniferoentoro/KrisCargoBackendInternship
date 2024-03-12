<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PraJoa extends Model
{
    protected $guarded = [];
    use HasFactory;
    use SoftDeletes;
    // public $timestamps = false;
    protected $primaryKey = 'NOMOR';
    protected $keyType = 'string';

    protected $casts = [
        'PERSEN_ASURANSI' => 'float',
        // Other attribute castings
    ];

    //belongsto
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'KODE_CUSTOMER', 'KODE');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'KODE_VENDOR_PELAYARAN_FORWARDING', 'KODE');
    }

    public function pol()
    {
        return $this->belongsTo(Harbor::class, 'KODE_POL', 'KODE');
    }

    public function pod()
    {
        return $this->belongsTo(Harbor::class, 'KODE_POD', 'KODE');
    }

    public function uk_container()
    {
        return $this->belongsTo(Size::class, 'KODE_UK_CONTAINER', 'KODE');
    }

    public function jenis_container()
    {
        return $this->belongsTo(ContainerType::class, 'KODE_JENIS_CONTAINER', 'KODE');
    }

    public function jenis_order()
    {
        return $this->belongsTo(OrderType::class, 'KODE_JENIS_ORDER', 'KODE');
    }

    public function commodity()
    {
        return $this->belongsTo(Commodity::class, 'KODE_COMMODITY', 'KODE');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'KODE_SERVICE', 'KODE');
    }

    public function thc_pol()
    {
        return $this->belongsTo(ThcLolo::class, 'KODE_THC_POL', 'KODE');
    }

    public function thc_pod()
    {
        return $this->belongsTo(ThcLolo::class, 'KODE_THC_POD', 'KODE');
    }

    //hpp biaya buruh muat
    public function hpp_biaya_buruh_muat()
    {
        return $this->belongsTo(CostRate::class, 'KODE_HPP_BIAYA_BURUH_MUAT', 'KODE');
    }

    // hpp biaya buruh stripping
    public function hpp_biaya_buruh_stripping()
    {
        return $this->belongsTo(CostRate::class, 'KODE_HPP_BIAYA_BURUH_STRIPPING', 'KODE');
    }

    // hpp biaya buruh bongkar
    public function hpp_biaya_buruh_bongkar()
    {
        return $this->belongsTo(CostRate::class, 'KODE_HPP_BIAYA_BURUH_BONGKAR', 'KODE');
    }

    // kode rute truck pol
    public function rute_truck_pol()
    {
        return $this->belongsTo(CostRate::class, 'KODE_RUTE_TRUCK_POL', 'KODE');
    }

    // kode rute truck pod
    public function rute_truck_pod()
    {
        return $this->belongsTo(CostRate::class, 'KODE_RUTE_TRUCK_POD', 'KODE');
    }

    // kode hpp biaya seal
    public function hpp_biaya_seal()
    {
        return $this->belongsTo(CostRate::class, 'KODE_HPP_BIAYA_SEAL', 'KODE');
    }

    // kode hpp biaya ops
    public function hpp_biaya_ops()
    {
        return $this->belongsTo(CostRate::class, 'KODE_HPP_BIAYA_OPS', 'KODE');
    }
}
