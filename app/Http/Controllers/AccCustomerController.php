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
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccCustomerController extends Controller
{

    // public function trash()
    // {
    //     $prajoas = PraJoa::onlyTrashed()->get();

    //     //convert to array
    //     $prajoas = $prajoas->toArray();


    //     return ApiResponse::json(true, 'Trash bin fetched', $prajoas);
    // }

    // get prajoa which SUDAH_JADI_JOA is true
    public function approved()
    {
        $prajoas = PraJoa::where('SUDAH_JADI_JOA', true)->get();

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


    public function getLocalNextId()
    {
        // Get the maximum PJA.XXXXXX ID from the database

        //get today's date
        $currentDay = date('d');
        $currentMonth = date('m');
        $currentYear = date('y');

        $maxId = DB::table('joas')
            ->where('NOMOR', 'LIKE', 'JOA.' . $currentDay . $currentMonth . $currentYear . '%')
            ->orderByRaw('CAST(SUBSTRING("NOMOR" FROM \'\\-([0-9]+)\\-\') AS INTEGER) DESC, "NOMOR" DESC')
            ->value('NOMOR');



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


    // update SUDAH_JADI_JOA
    public function updateSudahJadiJoa(Request $request, $KODE)
    {

        try {
            $prajoa = PraJoa::where('NOMOR', $KODE)->first();
            if (!$prajoa) {
                return ApiResponse::json(false, 'Data not found', null, 404);
            }

            $prajoa->SUDAH_JADI_JOA = true;
            $prajoa->save();

            // get prajoa details
            $prajoa_details = PraJoaOtherCost::where('NOMOR_PRAJOA', $KODE)->get();

            // convert to array
            $prajoa->KODE_BIAYA_LAIN = $prajoa_details->toArray();

            // create JOA and JOA details to table JOA and JOAOtherCosts based on $prajoa

            $kodeAmbil = $this->getLocalNextId();
            $kodeAmbil = substr($kodeAmbil, 0, -2);


            $joa = new Joa();
            $joa->NOMOR = $kodeAmbil;
            $joa->KODE_CUSTOMER = $prajoa['KODE_CUSTOMER'];
            $joa->KODE_VENDOR_PELAYARAN_FORWARDING = $prajoa['KODE_VENDOR_PELAYARAN_FORWARDING'];
            $joa->KODE_POL = $prajoa['KODE_POL'];
            $joa->KODE_POD = $prajoa['KODE_POD'];
            $joa->KODE_UK_CONTAINER = $prajoa['KODE_UK_CONTAINER'];
            $joa->KODE_JENIS_CONTAINER = $prajoa['KODE_JENIS_CONTAINER'];
            $joa->KODE_JENIS_ORDER = $prajoa['KODE_JENIS_ORDER'];
            $joa->KODE_COMMODITY = $prajoa['KODE_COMMODITY'];
            $joa->KODE_SERVICE = $prajoa['KODE_SERVICE'];
            $joa->THC_POL_INCL = $prajoa['THC_POL_INCL'];
            $joa->KODE_THC_POL = $prajoa['KODE_THC_POL'];
            $joa->LOLO_POL_DALAM_LUAR = $prajoa['LOLO_POL_DALAM_LUAR'];
            $joa->LOLO_POL_INCL = $prajoa['LOLO_POL_INCL'];
            $joa->THC_POD_INCL = $prajoa['THC_POD_INCL'];
            $joa->KODE_THC_POD = $prajoa['KODE_THC_POD'];
            $joa->LOLO_POD_DALAM_LUAR = $prajoa['LOLO_POD_DALAM_LUAR'];
            $joa->LOLO_POD_INCL = $prajoa['LOLO_POD_INCL'];
            $joa->STATUS = $prajoa['STATUS'];
            $joa->REWORK_INCL = $prajoa['REWORK_INCL'];
            $joa->NOMINAL_REWORK = $prajoa['NOMINAL_REWORK'];
            $joa->KETERANGAN_REWORK = $prajoa['KETERANGAN_REWORK'];
            $joa->BURUH_MUAT_INCL = $prajoa['BURUH_MUAT_INCL'];
            $joa->KODE_HPP_BIAYA_BURUH_MUAT = $prajoa['KODE_HPP_BIAYA_BURUH_MUAT'];
            $joa->ALAT_BERAT_POL_INCL = $prajoa['ALAT_BERAT_POL_INCL'];
            $joa->NOMINAL_ALAT_BERAT_POL = $prajoa['NOMINAL_ALAT_BERAT_POL'];
            $joa->KETERANGAN_ALAT_BERAT_POL = $prajoa['KETERANGAN_ALAT_BERAT_POL'];
            $joa->BURUH_STRIPPING_INCL = $prajoa['BURUH_STRIPPING_INCL'];
            $joa->KODE_HPP_BIAYA_BURUH_STRIPPING = $prajoa['KODE_HPP_BIAYA_BURUH_STRIPPING'];
            $joa->BURUH_BONGKAR_INCL = $prajoa['BURUH_BONGKAR_INCL'];
            $joa->KODE_HPP_BIAYA_BURUH_BONGKAR = $prajoa['KODE_HPP_BIAYA_BURUH_BONGKAR'];
            $joa->ALAT_BERAT_POD_STRIPPING_INCL = $prajoa['ALAT_BERAT_POD_STRIPPING_INCL'];
            $joa->NOMINAL_ALAT_BERAT_POD_STRIPPING = $prajoa['NOMINAL_ALAT_BERAT_POD_STRIPPING'];
            $joa->KETERANGAN_ALAT_BERAT_POD_STRIPPING = $prajoa['KETERANGAN_ALAT_BERAT_POD_STRIPPING'];
            $joa->ALAT_BERAT_POD_BONGKAR_INCL = $prajoa['ALAT_BERAT_POD_BONGKAR_INCL'];
            $joa->NOMINAL_ALAT_BERAT_POD_BONGKAR = $prajoa['NOMINAL_ALAT_BERAT_POD_BONGKAR'];
            $joa->KETERANGAN_ALAT_BERAT_POD_BONGKAR = $prajoa['KETERANGAN_ALAT_BERAT_POD_BONGKAR'];
            $joa->ASURANSI_INCL = $prajoa['ASURANSI_INCL'];
            $joa->NOMINAL_TSI = $prajoa['NOMINAL_TSI'];
            $joa->PERSEN_ASURANSI = $prajoa['PERSEN_ASURANSI'];
            $joa->TRUCK_POL_INCL = $prajoa['TRUCK_POL_INCL'];
            $joa->KODE_RUTE_TRUCK_POL = $prajoa['KODE_RUTE_TRUCK_POL'];
            $joa->TRUCK_POD_INCL = $prajoa['TRUCK_POD_INCL'];
            $joa->KODE_RUTE_TRUCK_POD = $prajoa['KODE_RUTE_TRUCK_POD'];
            $joa->FEE_AGENT_POL_INCL = $prajoa['FEE_AGENT_POL_INCL'];
            $joa->NOMINAL_FEE_AGENT_POL = $prajoa['NOMINAL_FEE_AGENT_POL'];
            $joa->KETERANGAN_FEE_AGENT_POL = $prajoa['KETERANGAN_FEE_AGENT_POL'];
            $joa->FEE_AGENT_POD_INCL = $prajoa['FEE_AGENT_POD_INCL'];
            $joa->NOMINAL_FEE_AGENT_POD = $prajoa['NOMINAL_FEE_AGENT_POD'];
            $joa->KETERANGAN_FEE_AGENT_POD = $prajoa['KETERANGAN_FEE_AGENT_POD'];

            $joa->TOESLAG_INCL = $prajoa['TOESLAG_INCL'];
            $joa->NOMINAL_TOESLAG = $prajoa['NOMINAL_TOESLAG'];
            $joa->KETERANGAN_TOESLAG = $prajoa['KETERANGAN_TOESLAG'];
            $joa->SEAL_INCL = $prajoa['SEAL_INCL'];
            $joa->KODE_HPP_BIAYA_SEAL = $prajoa['KODE_HPP_BIAYA_SEAL'];
            $joa->OPS_INCL = $prajoa['OPS_INCL'];
            $joa->KODE_HPP_BIAYA_OPS = $prajoa['KODE_HPP_BIAYA_OPS'];
            $joa->KARANTINA_INCL = $prajoa['KARANTINA_INCL'];
            $joa->NOMINAL_KARANTINA = $prajoa['NOMINAL_KARANTINA'];
            $joa->KETERANGAN_KARANTINA = $prajoa['KETERANGAN_KARANTINA'];
            $joa->CASHBACK_INCL = $prajoa['CASHBACK_INCL'];
            $joa->NOMINAL_CASHBACK = $prajoa['NOMINAL_CASHBACK'];
            $joa->KETERANGAN_CASHBACK = $prajoa['KETERANGAN_CASHBACK'];
            $joa->CLAIM_INCL = $prajoa['CLAIM_INCL'];
            $joa->NOMINAL_CLAIM = $prajoa['NOMINAL_CLAIM'];
            $joa->KETERANGAN_CLAIM = $prajoa['KETERANGAN_CLAIM'];
            $joa->BIAYA_LAIN_INCL = $prajoa['BIAYA_LAIN_INCL'];

            $joa->BL_INCL = $prajoa['BL_INCL'];
            $joa->NOMINAL_BL = $prajoa['NOMINAL_BL'];
            $joa->KETERANGAN_BL = $prajoa['KETERANGAN_BL'];
            $joa->DO_INCL = $prajoa['DO_INCL'];
            $joa->NOMINAL_DO = $prajoa['NOMINAL_DO'];
            $joa->KETERANGAN_DO = $prajoa['KETERANGAN_DO'];
            $joa->APBS_INCL = $prajoa['APBS_INCL'];
            $joa->NOMINAL_APBS = $prajoa['NOMINAL_APBS'];
            $joa->KETERANGAN_APBS = $prajoa['KETERANGAN_APBS'];
            $joa->CLEANING_INCL = $prajoa['CLEANING_INCL'];
            $joa->NOMINAL_CLEANING = $prajoa['NOMINAL_CLEANING'];
            $joa->KETERANGAN_CLEANING = $prajoa['KETERANGAN_CLEANING'];
            $joa->DOC_INCL = $prajoa['DOC_INCL'];
            $joa->NOMINAL_DOC = $prajoa['NOMINAL_DOC'];
            $joa->KETERANGAN_DOC = $prajoa['KETERANGAN_DOC'];

            $joa->save();

            $id = $joa->NOMOR;


            if (!$joa) {
                return ApiResponse::json(false, 'Failed to insert data', null, 500);
            }

            $joar = Joa::where('NOMOR', $id)->first();
            $othercost = [];
            try {


                foreach ($prajoa['KODE_BIAYA_LAIN'] as $othercost) {
                    if (!$othercost) {
                        continue;
                    }
                    $new_othercost = new JoaOtherCost();


                    $new_othercost->KODE_HPP_BIAYA = $othercost['KODE_HPP_BIAYA'];

                    $new_othercost->NOMOR_JOA = $kodeAmbil;

                    $new_othercost->save();
                    // return ApiResponse::json(false, 'Data not found', $prajoa, 404);

                    $othercost[] = $new_othercost;


                    // if (!$new_othercost) {
                    //     return ApiResponse::json(false, 'Failed to insert data', null, 500);
                    // }
                }
            } catch (\Exception $e) {
                return ApiResponse::json(false, 'Failed to insert data', $e->getMessage(), 500);
            }


            //convert to array
            $joar = $joar->toArray();

            //convert other costs to array
            // $new_othercost = $new_othercost->toArray();

            // //include other costs
            $joar['OTHER_COSTS'] = $othercost;

            $customer = $joa->KODE_CUSTOMER ? Customer::where('KODE', $joa->KODE_CUSTOMER)->first() : null;
            $joar['NAMA_CUSTOMER'] = $customer ? $customer->NAMA : null;

            $vendor = optional(Vendor::where('KODE', $joa->KODE_VENDOR_PELAYARAN_FORWARDING)->first());
            $joar['VENDOR_PELAYARAN_FORWARDING'] = $vendor ? $vendor->NAMA : null;

            // pol
            $pol = optional(Harbor::where('KODE', $joa->KODE_POL)->first());
            $joar['POL'] = $pol ? $pol->NAMA_PELABUHAN : null;
            // POD
            $pod = optional(Harbor::where('KODE', $joa->KODE_POD)->first());
            $joar['POD'] = $pod ? $pod->NAMA_PELABUHAN : null;

            // KODE_UK_CONTAINER
            $kode_uk_container = optional(Size::where('KODE', $joa->KODE_UK_CONTAINER)->first());
            $joar['UK_CONTAINER'] = $kode_uk_container ? $kode_uk_container->NAMA : null;


            // KODE_JENIS_CONTAINER
            $kode_jenis_container = optional(ContainerType::where('KODE', $joa->KODE_JENIS_CONTAINER)->first());
            $joar['JENIS_CONTAINER'] = $kode_jenis_container ? $kode_jenis_container->NAMA : null;


            // KODE_JENIS_ORDER
            $KODE_JENIS_ORDER = optional(OrderType::where('KODE', $joa->KODE_JENIS_ORDER)->first());
            $joar['JENIS_ORDER'] = $KODE_JENIS_ORDER ? $KODE_JENIS_ORDER->NAMA : null;



            // KODE_COMMODITY
            $KODE_COMMODITY = optional(Commodity::where('KODE', $joa->KODE_COMMODITY)->first());
            $joar['COMMODITY'] = $KODE_COMMODITY ? $KODE_COMMODITY->NAMA : null;

            // SERVICE
            $KODE_SERVICE = optional(Service::where('KODE', $joa->KODE_SERVICE)->first());
            $joar['SERVICE'] = $KODE_SERVICE ? $KODE_SERVICE->NAMA : null;

            // THC_POL
            $THC_POL = optional(ThcLolo::where('KODE', $joa->KODE_THC_POL)->first());
            $joar['THC_POL'] = $THC_POL ? $THC_POL->THC : null;

            // THC_POD
            $THC_POD = optional(ThcLolo::where('KODE', $joa->KODE_THC_POD)->first());
            $joar['THC_POD'] = $THC_POD ? $THC_POD->THC : null;

            //HPP_BIAYA_BURUH_MUAT
            $HPP_BIAYA_BURUH_MUAT = optional(CostRate::where('KODE', $joa->KODE_HPP_BIAYA_BURUH_MUAT)->first());
            $joar['HPP_BIAYA_BURUH_MUAT'] = $HPP_BIAYA_BURUH_MUAT ? $HPP_BIAYA_BURUH_MUAT->TARIF : null;

            // HPP_BIAYA_BURUH_STRIPPING
            $HPP_BIAYA_BURUH_STRIPPING = optional(CostRate::where('KODE', $joa->KODE_HPP_BIAYA_BURUH_STRIPPING)->first());
            $joar['HPP_BIAYA_BURUH_STRIPPING'] = $HPP_BIAYA_BURUH_STRIPPING ? $HPP_BIAYA_BURUH_STRIPPING->TARIF : null;

            //HPP_BIAYA_BURUH_BONGKAR
            $HPP_BIAYA_BURUH_BONGKAR = optional(CostRate::where('KODE', $joa->KODE_HPP_BIAYA_BURUH_BONGKAR)->first());
            $joar['HPP_BIAYA_BURUH_BONGKAR'] = $HPP_BIAYA_BURUH_BONGKAR ? $HPP_BIAYA_BURUH_BONGKAR->TARIF : null;


            //HPP_BIAYA_SEAL
            $HPP_BIAYA_SEAL = optional(CostRate::where('KODE', $joa->KODE_HPP_BIAYA_SEAL)->first());
            $joar['HPP_BIAYA_SEAL'] = $HPP_BIAYA_SEAL ? $HPP_BIAYA_SEAL->TARIF : null;

            // HPP_BIAYA_OPS
            $HPP_BIAYA_OPS = optional(CostRate::where('KODE', $joa->KODE_HPP_BIAYA_OPS)->first());
            $joar['HPP_BIAYA_OPS'] = $HPP_BIAYA_OPS ? $HPP_BIAYA_OPS->TARIF : null;

            //RUTE_TRUCK_POL
            $RUTE_TRUCK_POL = optional(TruckRoute::where('KODE', $joa->KODE_RUTE_TRUCK_POL)->first());
            $joar['RUTE_TRUCK_POL'] = $RUTE_TRUCK_POL ? $RUTE_TRUCK_POL->RUTE_ASAL . $RUTE_TRUCK_POL->RUTE_TUJUAN : null;

            // RUTE_TRUCK_POD
            $RUTE_TRUCK_POD = optional(TruckRoute::where('KODE', $joa->KODE_RUTE_TRUCK_POD)->first());
            $joar['RUTE_TRUCK_POD'] = $RUTE_TRUCK_POD ? $RUTE_TRUCK_POD->RUTE_ASAL . $RUTE_TRUCK_POD->RUTE_TUJUAN : null;




            return ApiResponse::json(true, "Data inserted successfully with KODE $id", $joar, 201);

            return ApiResponse::json(true, 'Data approved successfully',  $joa);
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

            $prajoa->SUDAH_JADI_JOA = false;
            $prajoa->save();

            return ApiResponse::json(true, 'Data unapproved successfully',  $prajoa);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to update data', null, 500);
        }
    }


    public function indexWeb()
    {
        try {
            $prajoas = PraJoa::where('SUDAH_JADI_JOA', false)->get();

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
