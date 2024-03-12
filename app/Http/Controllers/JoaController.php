<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Helpers\GetUserFromToken;
use App\Models\City;
use App\Models\Commodity;
use App\Models\ContainerType;
use App\Models\Cost;
use App\Models\CostGroup;
use App\Models\CostRate;
use App\Models\Customer;
use App\Models\Harbor;
use App\Models\Joa;
use App\Models\JoaOtherCost;
use App\Models\OrderType;
use App\Models\PraJoa;
use App\Models\PraJoaOtherCost;
use App\Models\Service;
use App\Models\Size;
use App\Models\ThcLolo;
use App\Models\TruckRoute;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class JoaController extends Controller
{




    public function indexWeb()
    {
        try {
            $prajoas = Joa::orderBy('NOMOR', 'asc')->get();
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


    public function update(Request $request, $KODE)
    {
        //use validator here
        $validator = Validator::make($request->all(), [
            'KODE_CUSTOMER' => 'required',
            'KODE_VENDOR_PELAYARAN_FORWARDING' => 'required',
            'KODE_POL' => 'required',
            'KODE_POD' => 'required',
            'KODE_UK_CONTAINER' => 'required',
            'KODE_JENIS_CONTAINER' => 'required',
            'KODE_JENIS_ORDER' => 'required',
            'KODE_COMMODITY' => 'required',
            'KODE_SERVICE' => 'required',
            'THC_POL_INCL' => 'required',
            'KODE_THC_POL' => '',
            'LOLO_POL_DALAM_LUAR' => 'required',
            'LOLO_POL_INCL' => 'required',
            'THC_POD_INCL' => 'required',
            'KODE_THC_POD' => '',
            'LOLO_POD_DALAM_LUAR' => 'required',
            'LOLO_POD_INCL' => 'required',
            'STATUS' => 'required',
            'REWORK_INCL' => 'required',
            'NOMINAL_REWORK' => '',
            'KETERANGAN_REWORK' => '',
            'BURUH_MUAT_INCL' => 'required',
            'KODE_HPP_BIAYA_BURUH_MUAT' => '',
            'ALAT_BERAT_POL_INCL' => 'required',
            'NOMINAL_ALAT_BERAT_POL' => '',
            'KETERANGAN_ALAT_BERAT_POL' => '',
            'BURUH_STRIPPING_INCL' => 'required',
            'KODE_HPP_BIAYA_BURUH_STRIPPING' => '',
            'BURUH_BONGKAR_INCL' => 'required',
            'KODE_HPP_BIAYA_BURUH_BONGKAR' => '',
            'ALAT_BERAT_POD_STRIPPING_INCL' => 'required',
            'NOMINAL_ALAT_BERAT_POD_STRIPPING' => '',
            'KETERANGAN_ALAT_BERAT_POD_STRIPPING' => '',
            'ALAT_BERAT_POD_BONGKAR_INCL' => 'required',
            'NOMINAL_ALAT_BERAT_POD_BONGKAR' => '',
            'KETERANGAN_ALAT_BERAT_POD_BONGKAR' => '',
            'ASURANSI_INCL' => 'required',
            'NOMINAL_TSI' => '',
            'PERSEN_ASURANSI' => 'nullable|numeric|min:0|max:100',
            'TRUCK_POL_INCL' => 'required',
            'KODE_RUTE_TRUCK_POL' => '',
            'TRUCK_POD_INCL' => 'required',
            'KODE_RUTE_TRUCK_POD' => '',
            'FEE_AGENT_POL_INCL' => 'required',
            'NOMINAL_FEE_AGENT_POL' => '',
            'KETERANGAN_FEE_AGENT_POL' => '',
            'FEE_AGENT_POD_INCL' => 'required',
            'NOMINAL_FEE_AGENT_POD' => '',
            'KETERANGAN_FEE_AGENT_POD' => '',
            'TOESLAG_INCL' => 'required',
            'NOMINAL_TOESLAG' => '',
            'KETERANGAN_TOESLAG' => '',
            'SEAL_INCL' => 'required',
            'KODE_HPP_BIAYA_SEAL' => '',
            'OPS_INCL' => 'required',
            'KODE_HPP_BIAYA_OPS' => '',
            'KARANTINA_INCL' => 'required',
            'NOMINAL_KARANTINA' => '',
            'KETERANGAN_KARANTINA' => '',
            'CASHBACK_INCL' => 'required',
            'NOMINAL_CASHBACK' => '',
            'KETERANGAN_CASHBACK' => '',
            'CLAIM_INCL' => 'required',
            'NOMINAL_CLAIM' => '',
            'KETERANGAN_CLAIM' => '',
            'BIAYA_LAIN_INCL' => 'required',
            'KODE_BIAYA_LAIN' => 'array',
            // 'KODE_HPP_BIAYA' => 'array',

            'BL_INCL' => 'required',
            'NOMINAL_BL' => '',
            'KETERANGAN_BL' => '',
            'DO_INCL' => 'required',
            'NOMINAL_DO' => '',
            'KETERANGAN_DO' => '',
            'APBS_INCL' => 'required',
            'NOMINAL_APBS' => '',
            'KETERANGAN_APBS' => '',
            'CLEANING_INCL' => 'required',
            'NOMINAL_CLEANING' => '',
            'KETERANGAN_CLEANING' => '',
            'DOC_INCL' => 'required',
            'NOMINAL_DOC' => '',
            'KETERANGAN_DOC' => '',
            'HARGA_JUAL' => '',
        ], []);

        //if validator fails
        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }

        // Check uniqueness for NAMA case insensitive except for current KODE
        // $prajoas = Joa::where(DB::raw('LOWER("NAMA_BIAYA")'), strtolower($request->NAMA_BIAYA))->where('NOMOR', '!=', $KODE)->first();
        // if ($prajoas) {
        //     return ApiResponse::json(false, ['NAMA_BIAYA' => ['The NAMA_BIAYA has already been taken.']], null, 422);
        // }
        // try {
        $validatedData = $validator->validated();



        $prajoa = new PraJoa();
        $prajoa->NOMOR = $this->getLocalNextRevisionId($KODE);
        $prajoa->KODE_CUSTOMER = $validatedData['KODE_CUSTOMER'];
        $prajoa->KODE_VENDOR_PELAYARAN_FORWARDING = $validatedData['KODE_VENDOR_PELAYARAN_FORWARDING'];
        $prajoa->KODE_POL = $validatedData['KODE_POL'];
        $prajoa->KODE_POD = $validatedData['KODE_POD'];

        $prajoa->KODE_UK_CONTAINER = $validatedData['KODE_UK_CONTAINER'];
        $prajoa->KODE_JENIS_CONTAINER = $validatedData['KODE_JENIS_CONTAINER'];
        $prajoa->KODE_JENIS_ORDER = $validatedData['KODE_JENIS_ORDER'];
        $prajoa->KODE_COMMODITY = $validatedData['KODE_COMMODITY'];
        $prajoa->KODE_SERVICE = $validatedData['KODE_SERVICE'];
        $prajoa->THC_POL_INCL = $validatedData['THC_POL_INCL'];
        $prajoa->KODE_THC_POL = $validatedData['KODE_THC_POL'];
        $prajoa->LOLO_POL_DALAM_LUAR = $validatedData['LOLO_POL_DALAM_LUAR'];
        $prajoa->LOLO_POL_INCL = $validatedData['LOLO_POL_INCL'];
        $prajoa->THC_POD_INCL = $validatedData['THC_POD_INCL'];
        $prajoa->KODE_THC_POD = $validatedData['KODE_THC_POD'];

        $prajoa->LOLO_POD_DALAM_LUAR = $validatedData['LOLO_POD_DALAM_LUAR'];
        $prajoa->LOLO_POD_INCL = $validatedData['LOLO_POD_INCL'];
        $prajoa->STATUS = $validatedData['STATUS'];
        $prajoa->REWORK_INCL = $validatedData['REWORK_INCL'];
        $prajoa->NOMINAL_REWORK = $validatedData['NOMINAL_REWORK'];
        $prajoa->KETERANGAN_REWORK = $validatedData['KETERANGAN_REWORK'];
        $prajoa->BURUH_MUAT_INCL = $validatedData['BURUH_MUAT_INCL'];
        $prajoa->KODE_HPP_BIAYA_BURUH_MUAT = $validatedData['KODE_HPP_BIAYA_BURUH_MUAT'];
        $prajoa->ALAT_BERAT_POL_INCL = $validatedData['ALAT_BERAT_POL_INCL'];
        $prajoa->NOMINAL_ALAT_BERAT_POL = $validatedData['NOMINAL_ALAT_BERAT_POL'];
        $prajoa->KETERANGAN_ALAT_BERAT_POL = $validatedData['KETERANGAN_ALAT_BERAT_POL'];
        $prajoa->BURUH_STRIPPING_INCL = $validatedData['BURUH_STRIPPING_INCL'];
        $prajoa->KODE_HPP_BIAYA_BURUH_STRIPPING = $validatedData['KODE_HPP_BIAYA_BURUH_STRIPPING'];
        $prajoa->BURUH_BONGKAR_INCL = $validatedData['BURUH_BONGKAR_INCL'];

        $prajoa->KODE_HPP_BIAYA_BURUH_BONGKAR = $validatedData['KODE_HPP_BIAYA_BURUH_BONGKAR'];
        $prajoa->ALAT_BERAT_POD_STRIPPING_INCL = $validatedData['ALAT_BERAT_POD_STRIPPING_INCL'];
        $prajoa->NOMINAL_ALAT_BERAT_POD_STRIPPING = $validatedData['NOMINAL_ALAT_BERAT_POD_STRIPPING'];
        $prajoa->KETERANGAN_ALAT_BERAT_POD_STRIPPING = $validatedData['KETERANGAN_ALAT_BERAT_POD_STRIPPING'];
        $prajoa->ALAT_BERAT_POD_BONGKAR_INCL = $validatedData['ALAT_BERAT_POD_BONGKAR_INCL'];
        $prajoa->NOMINAL_ALAT_BERAT_POD_BONGKAR = $validatedData['NOMINAL_ALAT_BERAT_POD_BONGKAR'];
        $prajoa->KETERANGAN_ALAT_BERAT_POD_BONGKAR = $validatedData['KETERANGAN_ALAT_BERAT_POD_BONGKAR'];
        $prajoa->ASURANSI_INCL = $validatedData['ASURANSI_INCL'];
        $prajoa->NOMINAL_TSI = $validatedData['NOMINAL_TSI'];
        $prajoa->PERSEN_ASURANSI = $validatedData['PERSEN_ASURANSI'];
        $prajoa->TRUCK_POL_INCL = $validatedData['TRUCK_POL_INCL'];
        $prajoa->KODE_RUTE_TRUCK_POL = $validatedData['KODE_RUTE_TRUCK_POL'];
        $prajoa->TRUCK_POD_INCL = $validatedData['TRUCK_POD_INCL'];
        $prajoa->KODE_RUTE_TRUCK_POD = $validatedData['KODE_RUTE_TRUCK_POD'];
        $prajoa->FEE_AGENT_POL_INCL = $validatedData['FEE_AGENT_POL_INCL'];
        $prajoa->NOMINAL_FEE_AGENT_POL = $validatedData['NOMINAL_FEE_AGENT_POL'];
        $prajoa->KETERANGAN_FEE_AGENT_POL = $validatedData['KETERANGAN_FEE_AGENT_POL'];
        $prajoa->FEE_AGENT_POD_INCL = $validatedData['FEE_AGENT_POD_INCL'];
        $prajoa->NOMINAL_FEE_AGENT_POD = $validatedData['NOMINAL_FEE_AGENT_POD'];
        $prajoa->KETERANGAN_FEE_AGENT_POD = $validatedData['KETERANGAN_FEE_AGENT_POD'];

        $prajoa->TOESLAG_INCL = $validatedData['TOESLAG_INCL'];
        $prajoa->NOMINAL_TOESLAG = $validatedData['NOMINAL_TOESLAG'];
        $prajoa->KETERANGAN_TOESLAG = $validatedData['KETERANGAN_TOESLAG'];
        $prajoa->SEAL_INCL = $validatedData['SEAL_INCL'];
        $prajoa->KODE_HPP_BIAYA_SEAL = $validatedData['KODE_HPP_BIAYA_SEAL'];
        $prajoa->OPS_INCL = $validatedData['OPS_INCL'];
        $prajoa->KODE_HPP_BIAYA_OPS = $validatedData['KODE_HPP_BIAYA_OPS'];
        $prajoa->KARANTINA_INCL = $validatedData['KARANTINA_INCL'];
        $prajoa->NOMINAL_KARANTINA = $validatedData['NOMINAL_KARANTINA'];
        $prajoa->KETERANGAN_KARANTINA = $validatedData['KETERANGAN_KARANTINA'];
        $prajoa->CASHBACK_INCL = $validatedData['CASHBACK_INCL'];
        $prajoa->NOMINAL_CASHBACK = $validatedData['NOMINAL_CASHBACK'];
        $prajoa->KETERANGAN_CASHBACK = $validatedData['KETERANGAN_CASHBACK'];
        $prajoa->CLAIM_INCL = $validatedData['CLAIM_INCL'];
        $prajoa->NOMINAL_CLAIM = $validatedData['NOMINAL_CLAIM'];
        $prajoa->KETERANGAN_CLAIM = $validatedData['KETERANGAN_CLAIM'];
        $prajoa->BIAYA_LAIN_INCL = $validatedData['BIAYA_LAIN_INCL'];

        $prajoa->BL_INCL = $validatedData['BL_INCL'];
        $prajoa->NOMINAL_BL = $validatedData['NOMINAL_BL'];
        $prajoa->KETERANGAN_BL = $validatedData['KETERANGAN_BL'];
        $prajoa->DO_INCL = $validatedData['DO_INCL'];
        $prajoa->NOMINAL_DO = $validatedData['NOMINAL_DO'];
        $prajoa->KETERANGAN_DO = $validatedData['KETERANGAN_DO'];
        $prajoa->APBS_INCL = $validatedData['APBS_INCL'];
        $prajoa->NOMINAL_APBS = $validatedData['NOMINAL_APBS'];
        $prajoa->KETERANGAN_APBS = $validatedData['KETERANGAN_APBS'];
        $prajoa->CLEANING_INCL = $validatedData['CLEANING_INCL'];
        $prajoa->NOMINAL_CLEANING = $validatedData['NOMINAL_CLEANING'];
        $prajoa->KETERANGAN_CLEANING = $validatedData['KETERANGAN_CLEANING'];
        $prajoa->DOC_INCL = $validatedData['DOC_INCL'];
        $prajoa->NOMINAL_DOC = $validatedData['NOMINAL_DOC'];
        $prajoa->KETERANGAN_DOC = $validatedData['KETERANGAN_DOC'];
        $prajoa->HARGA_JUAL = $validatedData['HARGA_JUAL'];


        $prajoa->save();


        $id = $prajoa->NOMOR;



        if (!$prajoa) {
            return ApiResponse::json(false, 'Failed to insert data', null, 500);
        }


        $prajoar = Joa::where('NOMOR', $id)->first();
        $otherCostsData = [];
        foreach ($validatedData['KODE_BIAYA_LAIN'] as $othercost) {
            if (!$othercost) {
                continue;
            }
            $new_othercost = new JoaOtherCost();

            $new_othercost->KODE_HPP_BIAYA = $othercost;
            $new_othercost->NOMOR_PRAJOA = $id;
            $new_othercost->save();
            $otherCostsData[] = $new_othercost;


            if (!$new_othercost) {
                return ApiResponse::json(false, 'Failed to insert data', null, 500);
            }
        }


        //convert to array
        $prajoar = $prajoar->toArray();



        //include other costs
        $prajoar['OTHER_COSTS'] = $otherCostsData;




        $customer = $prajoa->KODE_CUSTOMER ? Customer::where('KODE', $prajoa->KODE_CUSTOMER)->first() : null;
        $prajoar['NAMA_CUSTOMER'] = $customer ? $customer->NAMA : null;

        $vendor = optional(Vendor::where('KODE', $prajoa->KODE_VENDOR_PELAYARAN_FORWARDING)->first());
        $prajoar['VENDOR_PELAYARAN_FORWARDING'] = $vendor ? $vendor->NAMA : null;

        // pol
        $pol = optional(Harbor::where('KODE', $prajoa->KODE_POL)->first());
        $prajoar['POL'] = $pol ? $pol->NAMA_PELABUHAN : null;
        // POD
        $pod = optional(Harbor::where('KODE', $prajoa->KODE_POD)->first());
        $prajoar['POD'] = $pod ? $pod->NAMA_PELABUHAN : null;

        // KODE_UK_CONTAINER
        $kode_uk_container = optional(Size::where('KODE', $prajoa->KODE_UK_CONTAINER)->first());
        $prajoar['UK_CONTAINER'] = $kode_uk_container ? $kode_uk_container->NAMA : null;


        // KODE_JENIS_CONTAINER
        $kode_jenis_container = optional(ContainerType::where('KODE', $prajoa->KODE_JENIS_CONTAINER)->first());
        $prajoar['JENIS_CONTAINER'] = $kode_jenis_container ? $kode_jenis_container->NAMA : null;


        // KODE_JENIS_ORDER
        $KODE_JENIS_ORDER = optional(OrderType::where('KODE', $prajoa->KODE_JENIS_ORDER)->first());
        $prajoar['JENIS_ORDER'] = $KODE_JENIS_ORDER ? $KODE_JENIS_ORDER->NAMA : null;



        // KODE_COMMODITY
        $KODE_COMMODITY = optional(Commodity::where('KODE', $prajoa->KODE_COMMODITY)->first());
        $prajoar['COMMODITY'] = $KODE_COMMODITY ? $KODE_COMMODITY->NAMA : null;

        // SERVICE
        $KODE_SERVICE = optional(Service::where('KODE', $prajoa->KODE_SERVICE)->first());
        $prajoar['SERVICE'] = $KODE_SERVICE ? $KODE_SERVICE->NAMA : null;

        // THC_POL
        $THC_POL = optional(ThcLolo::where('KODE', $prajoa->KODE_THC_POL)->first());
        $prajoar['THC_POL'] = $THC_POL ? $THC_POL->THC : null;

        // THC_POD
        $THC_POD = optional(ThcLolo::where('KODE', $prajoa->KODE_THC_POD)->first());
        $prajoar['THC_POD'] = $THC_POD ? $THC_POD->THC : null;

        //HPP_BIAYA_BURUH_MUAT
        $HPP_BIAYA_BURUH_MUAT = optional(CostRate::where('KODE', $prajoa->KODE_HPP_BIAYA_BURUH_MUAT)->first());
        $prajoar['HPP_BIAYA_BURUH_MUAT'] = $HPP_BIAYA_BURUH_MUAT ? $HPP_BIAYA_BURUH_MUAT->TARIF : null;

        // HPP_BIAYA_BURUH_STRIPPING
        $HPP_BIAYA_BURUH_STRIPPING = optional(CostRate::where('KODE', $prajoa->KODE_HPP_BIAYA_BURUH_STRIPPING)->first());
        $prajoar['HPP_BIAYA_BURUH_STRIPPING'] = $HPP_BIAYA_BURUH_STRIPPING ? $HPP_BIAYA_BURUH_STRIPPING->TARIF : null;

        //HPP_BIAYA_BURUH_BONGKAR
        $HPP_BIAYA_BURUH_BONGKAR = optional(CostRate::where('KODE', $prajoa->KODE_HPP_BIAYA_BURUH_BONGKAR)->first());
        $prajoar['HPP_BIAYA_BURUH_BONGKAR'] = $HPP_BIAYA_BURUH_BONGKAR ? $HPP_BIAYA_BURUH_BONGKAR->TARIF : null;


        //HPP_BIAYA_SEAL
        $HPP_BIAYA_SEAL = optional(CostRate::where('KODE', $prajoa->KODE_HPP_BIAYA_SEAL)->first());
        $prajoar['HPP_BIAYA_SEAL'] = $HPP_BIAYA_SEAL ? $HPP_BIAYA_SEAL->TARIF : null;

        // HPP_BIAYA_OPS
        $HPP_BIAYA_OPS = optional(CostRate::where('KODE', $prajoa->KODE_HPP_BIAYA_OPS)->first());
        $prajoar['HPP_BIAYA_OPS'] = $HPP_BIAYA_OPS ? $HPP_BIAYA_OPS->TARIF : null;

        //RUTE_TRUCK_POL
        $RUTE_TRUCK_POL = optional(TruckRoute::where('KODE', $prajoa->KODE_RUTE_TRUCK_POL)->first());
        $prajoar['RUTE_TRUCK_POL'] = $RUTE_TRUCK_POL ? $RUTE_TRUCK_POL->RUTE_ASAL . $RUTE_TRUCK_POL->RUTE_TUJUAN : null;

        // RUTE_TRUCK_POD
        $RUTE_TRUCK_POD = optional(TruckRoute::where('KODE', $prajoa->KODE_RUTE_TRUCK_POD)->first());
        $prajoar['RUTE_TRUCK_POD'] = $RUTE_TRUCK_POD ? $RUTE_TRUCK_POD->RUTE_ASAL . $RUTE_TRUCK_POD->RUTE_TUJUAN : null;




        return ApiResponse::json(true, 'Pra Joa successfully updated', $prajoar);
        // } catch (\Exception $e) {
        //     return ApiResponse::json(false, $e->getMessage(), null, 500);
        // }
    }

    public function findByKode($KODE)
    {
        try {
            $prajoa = Joa::where('NOMOR', $KODE)->first();
            if (!$prajoa) {
                return ApiResponse::json(false, 'Data not found', null, 404);
            }

            $prajoaOtherCosts = JoaOtherCost::where('NOMOR_JOA', $prajoa->NOMOR)->get();
            $prajoa->KODE_BIAYA_LAIN = $prajoaOtherCosts->toArray();
            $prajoa = $prajoa->toArray();

            return ApiResponse::json(true, 'Data retrieved successfully',  $prajoa);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to retrieve data', null, 500);
        }
    }

    public function destroy($KODE)
    {
        try {
            $city = Joa::findOrFail($KODE);
            $city->delete();
            return ApiResponse::json(true, 'Pra Joa successfully deleted', ['NOMOR' => $KODE]);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to delete prajoa', null, 500);
        }
    }

    public function trash()
    {
        $prajoas = Joa::onlyTrashed()->get();

        //convert to array

        foreach ($prajoas as $prajoa) {


            $customer = $prajoa['KODE_CUSTOMER'] ? Customer::where('KODE', $prajoa['KODE_CUSTOMER'])->first() : null;
            $prajoa['NAMA_CUSTOMER'] = $customer ? $customer->NAMA : null;


            $vendor = optional(Vendor::where('KODE', $prajoa['KODE_VENDOR_PELAYARAN_FORWARDING'])->first());
            $prajoa['VENDOR_PELAYARAN_FORWARDING'] = $vendor ? $vendor->NAMA : null;

            // pol
            $pol = optional(Harbor::where('KODE', $prajoa['KODE_POL'])->first());
            $prajoa['POL'] = $pol ? $pol->NAMA_PELABUHAN : null;
            // POD
            $pod = optional(Harbor::where('KODE', $prajoa['KODE_POD'])->first());
            $prajoa['POD'] = $pod ? $pod->NAMA_PELABUHAN : null;

            // KODE_UK_CONTAINER
            $kode_uk_container = optional(Size::where('KODE', $prajoa['KODE_UK_CONTAINER'])->first());
            $prajoa['UK_CONTAINER'] = $kode_uk_container ? $kode_uk_container->NAMA : null;


            // KODE_JENIS_CONTAINER
            $kode_jenis_container = optional(ContainerType::where('KODE', $prajoa['KODE_JENIS_CONTAINER'])->first());
            $prajoa['JENIS_CONTAINER'] = $kode_jenis_container ? $kode_jenis_container->NAMA : null;


            // KODE_JENIS_ORDER
            $KODE_JENIS_ORDER = optional(OrderType::where('KODE', $prajoa['KODE_JENIS_ORDER'])->first());
            $prajoa['JENIS_ORDER'] = $KODE_JENIS_ORDER ? $KODE_JENIS_ORDER->NAMA : null;



            // KODE_COMMODITY
            $KODE_COMMODITY = optional(Commodity::where('KODE', $prajoa['KODE_COMMODITY'])->first());
            $prajoa['COMMODITY'] = $KODE_COMMODITY ? $KODE_COMMODITY->NAMA : null;

            // SERVICE
            $KODE_SERVICE = optional(Service::where('KODE', $prajoa['KODE_SERVICE'])->first());
            $prajoa['SERVICE'] = $KODE_SERVICE ? $KODE_SERVICE->NAMA : null;

            // THC_POL
            $THC_POL = optional(ThcLolo::where('KODE', $prajoa['KODE_THC_POL'])->first());
            $prajoa['THC_POL'] = $THC_POL ? $THC_POL->THC : null;

            // THC_POD
            $THC_POD = optional(ThcLolo::where('KODE', $prajoa['KODE_THC_POD'])->first());
            $prajoa['THC_POD'] = $THC_POD ? $THC_POD->THC : null;

            //HPP_BIAYA_BURUH_MUAT
            $HPP_BIAYA_BURUH_MUAT = optional(CostRate::where('KODE', $prajoa['KODE_HPP_BIAYA_BURUH_MUAT'])->first());
            $prajoa['HPP_BIAYA_BURUH_MUAT'] = $HPP_BIAYA_BURUH_MUAT ? $HPP_BIAYA_BURUH_MUAT->TARIF : null;

            // HPP_BIAYA_BURUH_STRIPPING
            $HPP_BIAYA_BURUH_STRIPPING = optional(CostRate::where('KODE', $prajoa['KODE_HPP_BIAYA_BURUH_STRIPPING'])->first());
            $prajoa['HPP_BIAYA_BURUH_STRIPPING'] = $HPP_BIAYA_BURUH_STRIPPING ? $HPP_BIAYA_BURUH_STRIPPING->TARIF : null;

            //HPP_BIAYA_BURUH_BONGKAR
            $HPP_BIAYA_BURUH_BONGKAR = optional(CostRate::where('KODE', $prajoa['KODE_HPP_BIAYA_BURUH_BONGKAR'])->first());
            $prajoa['HPP_BIAYA_BURUH_BONGKAR'] = $HPP_BIAYA_BURUH_BONGKAR ? $HPP_BIAYA_BURUH_BONGKAR->TARIF : null;


            //HPP_BIAYA_SEAL
            $HPP_BIAYA_SEAL = optional(CostRate::where('KODE', $prajoa['KODE_HPP_BIAYA_SEAL'])->first());
            $prajoa['HPP_BIAYA_SEAL'] = $HPP_BIAYA_SEAL ? $HPP_BIAYA_SEAL->TARIF : null;

            // HPP_BIAYA_OPS
            $HPP_BIAYA_OPS = optional(CostRate::where('KODE', $prajoa['KODE_HPP_BIAYA_OPS'])->first());
            $prajoa['HPP_BIAYA_OPS'] = $HPP_BIAYA_OPS ? $HPP_BIAYA_OPS->TARIF : null;

            //RUTE_TRUCK_POL
            $RUTE_TRUCK_POL = optional(TruckRoute::where('KODE', $prajoa['KODE_RUTE_TRUCK_POL'])->first());
            $prajoa['RUTE_TRUCK_POL'] = $RUTE_TRUCK_POL ? $RUTE_TRUCK_POL->RUTE_ASAL . $RUTE_TRUCK_POL->RUTE_TUJUAN : null;

            // RUTE_TRUCK_POD
            $RUTE_TRUCK_POD = optional(TruckRoute::where('KODE', $prajoa['KODE_RUTE_TRUCK_POD'])->first());
            $prajoa['RUTE_TRUCK_POD'] = $RUTE_TRUCK_POD ? $RUTE_TRUCK_POD->RUTE_ASAL . $RUTE_TRUCK_POD->RUTE_TUJUAN : null;
        }


        $prajoas = $prajoas->toArray();



        return ApiResponse::json(true, 'Trash bin fetched', $prajoas);
    }

    public function restore($id)
    {
        $restored = Joa::onlyTrashed()->findOrFail($id);
        $restored->restore();

        return ApiResponse::json(true, 'Restored', ['NOMOR' => $id]);
    }

    public function getNextId()
    {
        $nextId = $this->getLocalNextId();

        $nextId = substr($nextId, 0, -2);



        return ApiResponse::json(true, 'Next NOMOR retrieved successfully', $nextId);
    }

    public function getLocalNextId()
    {
        // Get the maximum JOA.XXXXXX ID from the database

        //get today's date
        $currentDay = date('d');
        $currentMonth = date('m');
        $currentYear = date('y');

        $maxId = DB::table('joas')
            ->where('NOMOR', 'LIKE', 'JOA.' . $currentDay . $currentMonth . $currentYear . '%')
            ->orderByRaw('CAST(SUBSTRING("NOMOR" FROM \'\\-([0-9]+)\\-\') AS INTEGER) DESC, "NOMOR" DESC')
            ->value('NOMOR');



        // $maxId = DB::table('s')
        //     ->where('NOMOR', 'LIKE', 'JOA.%')
        //     ->orderByRaw('CAST(SUBSTRING("NOMOR" FROM \'\\-([0-9]+)\\-\') AS INTEGER) DESC, "NOMOR" DESC')
        //     ->value('NOMOR');







        // $maxid = DB::table("s")
        //     ->select("max (cast(substring('nomor' from '-(\d+)-') as integer)) as max_id")
        //     ->where("NOMOR", "like", 'JOA.%')
        //     ->get();



        if ($maxId) {

            // Find the position of the first '-' character
            $firstDashPos = strpos($maxId, '-');

            // Find the position of the next '-' character, starting the search from the position after the first '-'
            $nextDashPos = strpos($maxId, '-', $firstDashPos + 1);

            // Extract the substring between the first '-' and the next '-' (excluding the first '-')
            $substring = substr($maxId, $firstDashPos + 1, $nextDashPos - $firstDashPos - 1);

            // Convert the substring to an integer
            $lastSequence = (int)$substring;

            $lastRevision = 1;

            // Get the current day, month, and year

            // Check if the current day, month, and year are the same as the last entry
            if (substr($maxId, 4, 6) === $currentDay . $currentMonth . $currentYear) {
                // If the current day, month, and year are the same, increment the sequence number
                $nextSequence = $lastSequence + 1;
            } else {
                // If the current day, month, and year are different, reset the sequence number to 1
                $nextSequence = 1;
            }

            // Create the next ID by concatenating 'JOA' with the current day, month, and year, sequence, and revision
            $nextId = 'JOA.' . $currentDay . $currentMonth . $currentYear . '-' . $nextSequence . '-' . $lastRevision;
        } else {
            // If no existing IDs found, start with 1 for sequence and revision
            $currentDay = date('d');
            $currentMonth = date('m');
            $currentYear = date('y');
            $nextId = 'JOA.' . $currentDay . $currentMonth . $currentYear . '-1-1';
        }

        return $nextId;
    }

    public function getLocalNextRevisionId($idNow)
    {
        if ($idNow) {
            // Extract the last revision number and sub-revision number from the ID
            $parts = explode('-', $idNow);

            // Count the number of elements in the array
            $numParts = count($parts);

            if ($numParts == 3) {
                $kode = $parts[2] + 1;
                return $parts[0] . "-" . $parts[1] . "-" . $kode;
            } else {
                return $parts[0] . "-" . $parts[1] . "-1";
            }
        } else {
            // If no existing revision ID found, start with 1 for the revision number
            $nextId = 'JOA.' . date('dmY') . '-1' . '-1';
        }

        return $nextId;
    }
}
