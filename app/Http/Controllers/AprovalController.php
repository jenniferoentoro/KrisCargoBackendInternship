<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\City;
use App\Models\Commodity;
use App\Models\ContainerType;
use App\Models\Cost;
use App\Models\CostGroup;
use App\Models\CostRate;
use App\Models\Customer;
use App\Models\Harbor;
use App\Models\OrderType;
use App\Models\PraJoa;
use App\Models\PraJoaOtherCost;
use App\Models\Service;
use App\Models\Size;
use App\Models\ThcLolo;
use App\Models\TruckRoute;
use App\Models\Vendor;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class AprovalController extends Controller
{

    // public function trash()
    // {
    //     $prajoas = PraJoa::onlyTrashed()->get();

    //     //convert to array
    //     $prajoas = $prajoas->toArray();


    //     return ApiResponse::json(true, 'Trash bin fetched', $prajoas);
    // }

    // get prajoa which SUDAH_DIAPPROVE is true
    public function approved()
    {
        $prajoas = PraJoa::where('SUDAH_DIAPPROVE', true)->get();

        foreach ($prajoas as $prajoa) {

            $customer = $prajoa->KODE_CUSTOMER ? Customer::where('KODE', $prajoa->KODE_CUSTOMER)->first() : null;
            $prajoa->NAMA_CUSTOMER = $customer ? $customer->NAMA : null;

            $vendor = optional(Vendor::where('KODE', $prajoa->KODE_VENDOR_PELAYARAN_FORWARDING)->first());
            $prajoa->VENDOR_PELAYARAN_FORWARDING = $vendor ? $vendor->NAMA : null;

            // pol
            $pol = optional(Harbor::where('KODE', $prajoa->KODE_POL)->first());
            $prajoa->POL = $pol ? $pol->NAMA_PELABUHAN : null;
            // POD
            $pod = optional(Harbor::where('KODE', $prajoa->KODE_POD)->first());
            $prajoa->POD = $pod ? $pod->NAMA_PELABUHAN : null;

            // KODE_UK_CONTAINER
            $kode_uk_container = optional(Size::where('KODE', $prajoa->KODE_UK_CONTAINER)->first());
            $prajoa->UK_CONTAINER = $kode_uk_container ? $kode_uk_container->NAMA : null;


            // KODE_JENIS_CONTAINER
            $kode_jenis_container = optional(ContainerType::where('KODE', $prajoa->KODE_JENIS_CONTAINER)->first());
            $prajoa->JENIS_CONTAINER = $kode_jenis_container ? $kode_jenis_container->NAMA : null;


            // KODE_JENIS_ORDER
            $KODE_JENIS_ORDER = optional(OrderType::where('KODE', $prajoa->KODE_JENIS_ORDER)->first());
            $prajoa->JENIS_ORDER = $KODE_JENIS_ORDER ? $KODE_JENIS_ORDER->NAMA : null;



            // KODE_COMMODITY
            $KODE_COMMODITY = optional(Commodity::where('KODE', $prajoa->KODE_COMMODITY)->first());
            $prajoa->COMMODITY = $KODE_COMMODITY ? $KODE_COMMODITY->NAMA : null;

            // SERVICE
            $KODE_SERVICE = optional(Service::where('KODE', $prajoa->KODE_SERVICE)->first());
            $prajoa->SERVICE = $KODE_SERVICE ? $KODE_SERVICE->NAMA : null;

            // THC_POL
            $THC_POL = optional(ThcLolo::where('KODE', $prajoa->KODE_THC_POL)->first());
            $prajoa->THC_POL = $THC_POL ? $THC_POL->THC : null;

            // THC_POD
            $THC_POD = optional(ThcLolo::where('KODE', $prajoa->KODE_THC_POD)->first());
            $prajoa->THC_POD = $THC_POD ? $THC_POD->THC : null;

            //HPP_BIAYA_BURUH_MUAT
            $HPP_BIAYA_BURUH_MUAT = optional(CostRate::where('KODE', $prajoa->KODE_HPP_BIAYA_BURUH_MUAT)->first());
            $prajoa->HPP_BIAYA_BURUH_MUAT = $HPP_BIAYA_BURUH_MUAT ? $HPP_BIAYA_BURUH_MUAT->TARIF : null;

            // HPP_BIAYA_BURUH_STRIPPING
            $HPP_BIAYA_BURUH_STRIPPING = optional(CostRate::where('KODE', $prajoa->KODE_HPP_BIAYA_BURUH_STRIPPING)->first());
            $prajoa->HPP_BIAYA_BURUH_STRIPPING = $HPP_BIAYA_BURUH_STRIPPING ? $HPP_BIAYA_BURUH_STRIPPING->TARIF : null;

            //HPP_BIAYA_BURUH_BONGKAR
            $HPP_BIAYA_BURUH_BONGKAR = optional(CostRate::where('KODE', $prajoa->KODE_HPP_BIAYA_BURUH_BONGKAR)->first());
            $prajoa->HPP_BIAYA_BURUH_BONGKAR = $HPP_BIAYA_BURUH_BONGKAR ? $HPP_BIAYA_BURUH_BONGKAR->TARIF : null;


            //HPP_BIAYA_SEAL
            $HPP_BIAYA_SEAL = optional(CostRate::where('KODE', $prajoa->KODE_HPP_BIAYA_SEAL)->first());
            $prajoa->HPP_BIAYA_SEAL = $HPP_BIAYA_SEAL ? $HPP_BIAYA_SEAL->TARIF : null;

            // HPP_BIAYA_OPS
            $HPP_BIAYA_OPS = optional(CostRate::where('KODE', $prajoa->KODE_HPP_BIAYA_OPS)->first());
            $prajoa->HPP_BIAYA_OPS = $HPP_BIAYA_OPS ? $HPP_BIAYA_OPS->TARIF : null;

            //RUTE_TRUCK_POL
            $RUTE_TRUCK_POL = optional(TruckRoute::where('KODE', $prajoa->KODE_RUTE_TRUCK_POL)->first());
            $prajoa->RUTE_TRUCK_POL = $RUTE_TRUCK_POL ? $RUTE_TRUCK_POL->RUTE_ASAL . $RUTE_TRUCK_POL->RUTE_TUJUAN : null;

            // RUTE_TRUCK_POD
            $RUTE_TRUCK_POD = optional(TruckRoute::where('KODE', $prajoa->KODE_RUTE_TRUCK_POD)->first());
            $prajoa->RUTE_TRUCK_POD = $RUTE_TRUCK_POD ? $RUTE_TRUCK_POD->RUTE_ASAL . $RUTE_TRUCK_POD->RUTE_TUJUAN : null;
        }

        //convert to array
        $prajoas = $prajoas->toArray();
        return ApiResponse::json(true, 'Get approved prajoa', $prajoas);
    }

    public function dropdown()
    {
        //get all vendor that's JV.1 or JV6
        $vendors = Vendor::where('KODE_JENIS_VENDOR', 'JV.1')->orWhere('KODE_JENIS_VENDOR', 'JV.6')->get();
        $vendors = $vendors->toArray();
        //get all harbors
        $harbors = Harbor::all();
        $harbors = $harbors->toArray();
        //get all sizess
        $sizes = Size::all();
        $sizes = $sizes->toArray();
        //get all container types
        $containerTypes = ContainerType::all();
        $containerTypes = $containerTypes->toArray();
        //get all order types
        $orderTypes = OrderType::all();
        $orderTypes = $orderTypes->toArray();
        //get all commodities
        $commodities = Commodity::all();
        $commodities = $commodities->toArray();
        //get all services
        $services = Service::all();
        $services = $services->toArray();
        //get all thc lolo
        $thcLolos = ThcLolo::all();
        $thcLolos = $thcLolos->toArray();
        //get all cost rates
        $costRates = CostRate::all();
        $costRates = $costRates->toArray();
        //get all truck routes
        $truckRoutes = TruckRoute::all();
        $truckRoutes = $truckRoutes->toArray();
        //get all costs
        $costs = Cost::all();
        $costs = $costs->toArray();
        //get all cost group
        $costGroups = CostGroup::all();
        $costGroups = $costGroups->toArray();
        //get all customers
        $customers = Customer::all();
        $customers = $customers->toArray();
        //g/et all truck routes
        $truckRoutes = TruckRoute::all();

        $truckRoutes = $truckRoutes->toArray();
        //get all city
        $cities = City::all();
        $cities = $cities->toArray();
        //return all data
        return ApiResponse::json(true, null, [
            'vendors' => $vendors,
            'harbors' => $harbors,
            'sizes' => $sizes,
            'containerTypes' => $containerTypes,
            'orderTypes' => $orderTypes,
            'commodities' => $commodities,
            'services' => $services,
            'thcLolos' => $thcLolos,
            'costRates' => $costRates,
            'truckRoutes' => $truckRoutes,
            'costs' => $costs,
            'costGroups' => $costGroups,
            'customers' => $customers,
            'cities' => $cities,
        ]);
    }


    // update SUDAH_DIAPPROVE
    public function updateSudahJadiJoa(Request $request, $KODE)
    {

        try {
            $prajoa = PraJoa::where('NOMOR', $KODE)->first();
            if (!$prajoa) {
                return ApiResponse::json(false, 'Data not found', null, 404);
            }

            $prajoa->SUDAH_DIAPPROVE = true;
            $prajoa->save();

            return ApiResponse::json(true, 'Data approved successfully',  $prajoa);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to update data', null, 500);
        }
    }

    public function updateUnApproved(Request $request, $KODE)
    {

        try {
            $prajoa = PraJoa::where('NOMOR', $KODE)->first();
            if (!$prajoa) {
                return ApiResponse::json(false, 'Data not found', null, 404);
            }

            $prajoa->SUDAH_DIAPPROVE = false;
            $prajoa->save();

            return ApiResponse::json(true, 'Data unapproved successfully',  $prajoa);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to update data', null, 500);
        }
    }


    public function indexWeb()
    {
        try {
            $prajoas = PraJoa::where('SUDAH_DIAPPROVE', false)->get();

            foreach ($prajoas as $prajoa) {

                $customer = $prajoa->KODE_CUSTOMER ? Customer::where('KODE', $prajoa->KODE_CUSTOMER)->first() : null;
                $prajoa->NAMA_CUSTOMER = $customer ? $customer->NAMA : null;

                $vendor = optional(Vendor::where('KODE', $prajoa->KODE_VENDOR_PELAYARAN_FORWARDING)->first());
                $prajoa->VENDOR_PELAYARAN_FORWARDING = $vendor ? $vendor->NAMA : null;

                // pol
                $pol = optional(Harbor::where('KODE', $prajoa->KODE_POL)->first());
                $prajoa->POL = $pol ? $pol->NAMA_PELABUHAN : null;
                // POD
                $pod = optional(Harbor::where('KODE', $prajoa->KODE_POD)->first());
                $prajoa->POD = $pod ? $pod->NAMA_PELABUHAN : null;

                // KODE_UK_CONTAINER
                $kode_uk_container = optional(Size::where('KODE', $prajoa->KODE_UK_CONTAINER)->first());
                $prajoa->UK_CONTAINER = $kode_uk_container ? $kode_uk_container->NAMA : null;


                // KODE_JENIS_CONTAINER
                $kode_jenis_container = optional(ContainerType::where('KODE', $prajoa->KODE_JENIS_CONTAINER)->first());
                $prajoa->JENIS_CONTAINER = $kode_jenis_container ? $kode_jenis_container->NAMA : null;


                // KODE_JENIS_ORDER
                $KODE_JENIS_ORDER = optional(OrderType::where('KODE', $prajoa->KODE_JENIS_ORDER)->first());
                $prajoa->JENIS_ORDER = $KODE_JENIS_ORDER ? $KODE_JENIS_ORDER->NAMA : null;



                // KODE_COMMODITY
                $KODE_COMMODITY = optional(Commodity::where('KODE', $prajoa->KODE_COMMODITY)->first());
                $prajoa->COMMODITY = $KODE_COMMODITY ? $KODE_COMMODITY->NAMA : null;

                // SERVICE
                $KODE_SERVICE = optional(Service::where('KODE', $prajoa->KODE_SERVICE)->first());
                $prajoa->SERVICE = $KODE_SERVICE ? $KODE_SERVICE->NAMA : null;

                // THC_POL
                $THC_POL = optional(ThcLolo::where('KODE', $prajoa->KODE_THC_POL)->first());
                $prajoa->THC_POL = $THC_POL ? $THC_POL->THC : null;

                // THC_POD
                $THC_POD = optional(ThcLolo::where('KODE', $prajoa->KODE_THC_POD)->first());
                $prajoa->THC_POD = $THC_POD ? $THC_POD->THC : null;

                //HPP_BIAYA_BURUH_MUAT
                $HPP_BIAYA_BURUH_MUAT = optional(CostRate::where('KODE', $prajoa->KODE_HPP_BIAYA_BURUH_MUAT)->first());
                $prajoa->HPP_BIAYA_BURUH_MUAT = $HPP_BIAYA_BURUH_MUAT ? $HPP_BIAYA_BURUH_MUAT->TARIF : null;

                // HPP_BIAYA_BURUH_STRIPPING
                $HPP_BIAYA_BURUH_STRIPPING = optional(CostRate::where('KODE', $prajoa->KODE_HPP_BIAYA_BURUH_STRIPPING)->first());
                $prajoa->HPP_BIAYA_BURUH_STRIPPING = $HPP_BIAYA_BURUH_STRIPPING ? $HPP_BIAYA_BURUH_STRIPPING->TARIF : null;

                //HPP_BIAYA_BURUH_BONGKAR
                $HPP_BIAYA_BURUH_BONGKAR = optional(CostRate::where('KODE', $prajoa->KODE_HPP_BIAYA_BURUH_BONGKAR)->first());
                $prajoa->HPP_BIAYA_BURUH_BONGKAR = $HPP_BIAYA_BURUH_BONGKAR ? $HPP_BIAYA_BURUH_BONGKAR->TARIF : null;


                //HPP_BIAYA_SEAL
                $HPP_BIAYA_SEAL = optional(CostRate::where('KODE', $prajoa->KODE_HPP_BIAYA_SEAL)->first());
                $prajoa->HPP_BIAYA_SEAL = $HPP_BIAYA_SEAL ? $HPP_BIAYA_SEAL->TARIF : null;

                // HPP_BIAYA_OPS
                $HPP_BIAYA_OPS = optional(CostRate::where('KODE', $prajoa->KODE_HPP_BIAYA_OPS)->first());
                $prajoa->HPP_BIAYA_OPS = $HPP_BIAYA_OPS ? $HPP_BIAYA_OPS->TARIF : null;

                //RUTE_TRUCK_POL
                $RUTE_TRUCK_POL = optional(TruckRoute::where('KODE', $prajoa->KODE_RUTE_TRUCK_POL)->first());
                $prajoa->RUTE_TRUCK_POL = $RUTE_TRUCK_POL ? $RUTE_TRUCK_POL->RUTE_ASAL . $RUTE_TRUCK_POL->RUTE_TUJUAN : null;

                // RUTE_TRUCK_POD
                $RUTE_TRUCK_POD = optional(TruckRoute::where('KODE', $prajoa->KODE_RUTE_TRUCK_POD)->first());
                $prajoa->RUTE_TRUCK_POD = $RUTE_TRUCK_POD ? $RUTE_TRUCK_POD->RUTE_ASAL . $RUTE_TRUCK_POD->RUTE_TUJUAN : null;
            }


            return ApiResponse::json(true, 'Data retrieved successfully',  $prajoas);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to retrieve data', null, 500);
        }
    }
}
