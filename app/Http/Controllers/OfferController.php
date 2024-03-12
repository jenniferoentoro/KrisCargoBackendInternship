<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Commodity;
use App\Models\ContainerType;
use App\Models\Customer;
use App\Models\Harbor;
use App\Models\Size;
use App\Models\Truck;
use App\Models\Offer;
use App\Models\OfferDetail;
use App\Models\OrderType;
use App\Models\Service;
use App\Models\Staff;
use App\Models\TruckRoute;
use App\Models\Unit;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OfferController extends Controller
{
    public function findByKode($KODE)
    {
        try {
            $User = Offer::where('KODE', $KODE)->first();
            if (!$User) {
                return ApiResponse::json(false, 'Data not found', null, 404);
            }
            $offerDetail = OfferDetail::where('KODE_PENAWARAN', $User->KODE)->get();
            foreach ($offerDetail as $key => $value) {
                unset($value->KODE_PENAWARAN);
                unset($value->deleted_at);
                unset($value->updated_at);
                unset($value->created_at);
                $offerDetail[$key] = $value;
            }
            $User->OFFER_DETAIL = $offerDetail->toArray();
            $User = $User->toArray();
            return ApiResponse::json(true, 'Data retrieved successfully',  $User);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to retrieve data', null, 500);
        }
    }

    public function dropdown()
    {
        //get all customers
        $customers = Customer::all();
        $customers = $customers->toArray();
        //get all harbors
        $harbors = Harbor::all();
        $harbors = $harbors->toArray();
        //get all sizes
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
        //get all truck routes
        $truckRoutes = TruckRoute::all();
        $truckRoutes = $truckRoutes->toArray();
        //get all unit
        $units = Unit::all();
        $units = $units->toArray();
        //get position where KODE is JBT.6
        $staffs = Staff::where('KODE_JABATAN', 'JBT.6')->orWhere('KODE_JABATAN', 'JBT.7')->orWhere('KODE_JABATAN', 'JBT.16')->get();
        $staffs = $staffs->toArray();

        //return all data
        return ApiResponse::json(true, null, [
            'harbors' => $harbors,
            'sizes' => $sizes,
            'containerTypes' => $containerTypes,
            'orderTypes' => $orderTypes,
            'commodities' => $commodities,
            'services' => $services,
            'truckRoutes' => $truckRoutes,
            'customers' => $customers,
            'units' => $units,
            'staffs' => $staffs,
        ]);
    }


    public function indexWeb()
    {
        try {
            $penawarans = Offer::orderBy('KODE', 'asc')->get();



            foreach ($penawarans as $penawaran) {

                // $penawaran->offer_details = OfferDetail::where('KODE_PENAWARAN', $penawaran->KODE)->get();
                $penawaran->offer_details = OfferDetail::where('KODE_PENAWARAN', $penawaran->KODE)->get()->toArray();


                //get staff name
                $staff = Staff::where('KODE', $penawaran->SALES)->first();
                $penawaran->SALES = $staff ? $staff->NAMA : null;

                // foreach ($penawaran->offer_details as $offer_detail) {
                // }
            }
            // return ApiResponse::json(false, $jen, null, 500);
            return ApiResponse::json(true, 'Data retrieved successfully', $penawarans->toArray());
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to retrieve data', null, 500);
        }
    }

    public function storeWeb(Request $request)
    {
        //use validator here
        $validator = Validator::make($request->all(), [


            'KODE_CUSTOMER' => '',
            'KODE_JENIS_ORDER' => '',
            'RATE_STATUS' => '',
            'NAMA_CUSTOMER' => '',
            'CONTACT_PERSON' => '',
            'EMAIL' => '',
            'STATUS' => 'required',
            'PPN' => '',
            'PPN_PERCENTAGE' => '',
            'PPH' => '',
            'PAYMENT' => 'required',
            'TOP' => 'required',
            'KETERANGAN_TOP' => 'required',
            'KETERANGAN_TAMBAHAN' => '',
            'SALES' => 'required',
            'TANGGAL' => 'required|date',

            'KODE_PRAJOA' => 'array',
            'KODE_POL' => 'array',
            'KODE_POD' => 'array',
            'KODE_DOOR_POL' => 'array',
            'KODE_DOOR_POD' => 'array',
            'KODE_UK_KONTAINER' => 'array',
            'KODE_JENIS_CONTAINER' => 'array',
            'STUFFING' => 'array',
            'STRIPPING' => 'array',
            'BURUH_MUAT' => 'array',
            'BURUH_MUAT_KET' => 'array',
            'BURUH_SALIN' => 'array',
            'BURUH_SALIN_KET' => 'array',
            'BURUH_BONGKAR' => 'array',
            'ASURANSI' => 'required|array',
            'TSI' => 'array',
            'TSI_NOMINAL' => 'array',
            'FREE_TIME_STORAGE' => 'required|array',
            'FREE_TIME_DEMURRAGE' => 'required|array',
            'KODE_COMMODITY' => 'required|array',
            'KODE_SERVICE' => 'required|array',
            'HARGA' => 'array',
            'SATUAN_HARGA' => 'required|array',
        ], [
            'KODE_CUSTOMER.required' => 'Kode Customer is required',
            'KODE_JENIS_ORDER.required' => 'Kode Jenis Order is required',
            'RATE_STATUS.required' => 'Rate Status is required',
            'NAMA_CUSTOMER.required' => 'Nama Customer is required',
            'CONTACT_PERSON.required' => 'Contact Person is required',
            'EMAIL.required' => 'Email is required',
            'STATUS.required' => 'Status is required',
            'PPN.required' => 'PPN is required',
            'PPN_PERCENTAGE.required' => 'PPN Percentage is required',
            'PPH.required' => 'PPH is required',
            'PAYMENT.required' => 'Payment is required',
            'TOP.required' => 'Top is required',
            'KETERANGAN_TOP.required' => 'Keterangan Top is required',
            'KETERANGAN_TAMBAHAN.required' => 'Keterangan Tambahan is required',
            'SALES.required' => 'Sales is required',
            'TANGGAL.required' => 'Tanggal is required',
            'TANGGAL.date' => 'Tanggal must be a date',
            'KODE_PRAJOA.array' => 'Kode Prajoa must be an array',
            'KODE_POL.array' => 'Kode Pol must be an array',
            'KODE_POD.array' => 'Kode Pod must be an array',
            'KODE_DOOR_POL.array' => 'Kode Door Pol must be an array',
            'KODE_DOOR_POD.array' => 'Kode Door Pod must be an array',
            'KODE_UK_KONTAINER.array' => 'Kode Uk Kontainer must be an array',
            'KODE_JENIS_CONTAINER.array' => 'Kode Jenis Container must be an array',
            'STUFFING.array' => 'Stuffing must be an array',
            'STRIPPING.array' => 'Stripping must be an array',
            'BURUH_MUAT.array' => 'Buruuh Muat must be an array',
            'BURUH_MUAT_KET.array' => 'Buruuh Muat Ket must be an array',
            'BURUH_SALIN.array' => 'Buruuh Salin must be an array',
            'BURUH_SALIN_KET.array' => 'Buruuh Salin Ket must be an array',
            'BURUH_BONGKAR.array' => 'Buruuh Bongkar must be an array',
            'ASURANSI.required' => 'Asuransi is required',
            'ASURANSI.array' => 'Asuransi must be an array',
            'TSI.array' => 'TSI must be an array',
            'TSI_NOMINAL.array' => 'TSI Nominal must be an array',
            'FREE_TIME_STORAGE.required' => 'Free Time Storage is required',
            'FREE_TIME_STORAGE.array' => 'Free Time Storage must be an array',
            'FREE_TIME_DEMURRAGE.required' => 'Free Time Demurrage is required',
            'FREE_TIME_DEMURRAGE.array' => 'Free Time Demurrage must be an array',
            'KODE_COMMODITY.required' => 'Kode Commodity is required',
            'KODE_COMMODITY.array' => 'Kode Commodity must be an array',
            'KODE_SERVICE.required' => 'Kode Service is required',
            'KODE_SERVICE.array' => 'Kode Service must be an array',
            'HARGA.array' => 'Harga must be an array',
            'SATUAN_HARGA.required' => 'Satuan Harga is required',
            'SATUAN_HARGA.array' => 'Satuan Harga must be an array',
        ]);


        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }




        try {
            $validatedData = $validator->validated();

            //for i in tsi check if it's between 0-100
            foreach ($validatedData['TSI'] as $tsi) {
                if ($tsi < 0 || $tsi > 100) {
                    return ApiResponse::json(false, ['TSI[]' => ['TSI must be 0-100']], null, 422);
                }
            }
            $new_truck_price = new Offer();

            $new_truck_price->KODE = $this->getLocalNextId($validatedData['KODE_JENIS_ORDER']);
            $new_truck_price->KODE_JENIS_ORDER = $validatedData['KODE_JENIS_ORDER'];
            $new_truck_price->KODE_CUSTOMER = $validatedData['KODE_CUSTOMER'];
            $new_truck_price->TANGGAL = $validatedData['TANGGAL'];
            $new_truck_price->RATE_STATUS = $validatedData['RATE_STATUS'];
            $new_truck_price->NAMA_CUSTOMER = $validatedData['NAMA_CUSTOMER'];
            $new_truck_price->CONTACT_PERSON = $validatedData['CONTACT_PERSON'];
            $new_truck_price->EMAIL = $validatedData['EMAIL'];


            $new_truck_price->STATUS = $validatedData['STATUS'];

            $new_truck_price->PPN = $validatedData['PPN'];
            $new_truck_price->PPN_PERCENTAGE = $validatedData['PPN_PERCENTAGE'];

            $new_truck_price->PPH = $validatedData['PPH'];

            $new_truck_price->PAYMENT = $validatedData['PAYMENT'];
            $new_truck_price->TOP = $validatedData['TOP'];

            $new_truck_price->KETERANGAN_TOP = $validatedData['KETERANGAN_TOP'];
            $new_truck_price->KETERANGAN_TAMBAHAN = $validatedData['KETERANGAN_TAMBAHAN'];
            $new_truck_price->SALES = $validatedData['SALES'];


            $new_truck_price->save();

            if (!$new_truck_price) {
                return ApiResponse::json(false, 'Failed to insert data', null, 500);
            }
            $id = $new_truck_price->KODE;
            // return ApiResponse::json(false, "aa", null, 500);

            $penawaransr = Offer::where('KODE', $id)->first();

            foreach ($validatedData['KODE_COMMODITY'] as $index => $offerDetailData) {
                // return ApiResponse::json(false, $offerDetailData, null, 500);
                $new_offer_details = new OfferDetail();
                $new_offer_details->KODE = $this->getLocalNextIdDetails();
                $new_offer_details->KODE_PENAWARAN = $id;
                $new_offer_details->KODE_PRAJOA = $validatedData['KODE_PRAJOA'][$index];


                $new_offer_details->KODE_POL = $validatedData['KODE_POL'][$index];
                $new_offer_details->KODE_POD = $validatedData['KODE_POD'][$index];
                $new_offer_details->KODE_DOOR_POL = $validatedData['KODE_DOOR_POL'][$index];
                $new_offer_details->KODE_DOOR_POD = $validatedData['KODE_DOOR_POD'][$index];

                $new_offer_details->KODE_UK_KONTAINER = $validatedData['KODE_UK_KONTAINER'][$index];
                $new_offer_details->KODE_JENIS_CONTAINER = $validatedData['KODE_JENIS_CONTAINER'][$index];
                $new_offer_details->STUFFING = $validatedData['STUFFING'][$index];
                $new_offer_details->STRIPPING = $validatedData['STRIPPING'][$index];
                $new_offer_details->BURUH_MUAT = $validatedData['BURUH_MUAT'][$index];
                $new_offer_details->BURUH_MUAT_KET = $validatedData['BURUH_MUAT_KET'][$index];
                $new_offer_details->BURUH_SALIN = $validatedData['BURUH_SALIN'][$index];
                $new_offer_details->BURUH_SALIN_KET = $validatedData['BURUH_SALIN_KET'][$index];
                $new_offer_details->BURUH_BONGKAR = $validatedData['BURUH_BONGKAR'][$index];
                $new_offer_details->ASURANSI = $validatedData['ASURANSI'][$index];
                $new_offer_details->TSI = $validatedData['TSI'][$index];
                $new_offer_details->TSI_NOMINAL = $validatedData['TSI_NOMINAL'][$index];
                $new_offer_details->FREE_TIME_STORAGE = $validatedData['FREE_TIME_STORAGE'][$index];
                $new_offer_details->FREE_TIME_DEMURRAGE = $validatedData['FREE_TIME_DEMURRAGE'][$index];
                $new_offer_details->HARGA = $validatedData['HARGA'][$index];
                $new_offer_details->SATUAN_HARGA = $validatedData['SATUAN_HARGA'][$index];
                $new_offer_details->KODE_COMMODITY = $validatedData['KODE_COMMODITY'][$index];
                $new_offer_details->KODE_SERVICE = $validatedData['KODE_SERVICE'][$index];
                // return ApiResponse::json(false, $new_offer_details, null, 500);
                $new_offer_details->save();
                // return ApiResponse::json(false, 'aaaaaaa', null, 500);

                if (!$new_offer_details) {
                    return ApiResponse::json(false, 'Failed to insert data', null, 500);
                }
            }

            $OfferFinal = Offer::where('KODE', $id)->first();

            // $OfferFinal->NAMA_JABATAN = Position::where('KODE', $OfferFinal->KODE_JABATAN)->first()->NAMA;
            // $OfferFinal->NAMA_LOKASI = Warehouse::where('KODE', $OfferFinal->KODE_LOKASI)->first()->NAMA;


            // insert offerdetails to offerFinal
            $offerDetailss = OfferDetail::where('KODE_PENAWARAN', $id)->get();
            $OfferFinal->offer_details = $offerDetailss->toArray();






            return ApiResponse::json(true, "Data inserted successfully with KODE $OfferFinal->KODE", $OfferFinal->toArray(), 201);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e->getMessage(), null, 500);
        }
    }

    public function update(Request $request, $KODE)
    {
        //use validator here
        // $validator = Validator::make($request->all(), [
        //     'KODE_CUSTOMER' => '',
        //     'KODE_JENIS_ORDER' => '',
        //     'RATE_STATUS' => '',
        //     'NAMA_CUSTOMER' => '',
        //     'CONTACT_PERSON' => '',
        //     'EMAIL' => '',
        //     'STATUS' => 'required',
        //     'PPN' => '',
        //     'PPH' => '',
        //     'PAYMENT' => 'required',
        //     'TOP' => 'required',
        //     'KETERANGAN_TOP' => 'required',
        //     'KETERANGAN_TAMBAHAN' => '',
        //     'SALES' => 'required',
        //     'TANGGAL' => 'required|date',
        //     'offerDetails' => 'required|array',
        //     'offerDetails.*.KODE_PRAJOA' => 'required',
        //     'offerDetails.*.KODE_POL' => '',
        //     'offerDetails.*.KODE_POD' => '',
        //     'offerDetails.*.KODE_DOOR_POL' => '',
        //     'offerDetails.*.KODE_DOOR_POD' => '',
        //     'offerDetails.*.KODE_UK_KONTAINER' => '',
        //     'offerDetails.*.KODE_JENIS_CONTAINER' => '',
        //     'offerDetails.*.STUFFING' => '',
        //     'offerDetails.*.STRIPPING' => '',
        //     'offerDetails.*.BURUH_MUAT' => '',
        //     'offerDetails.*.BURUH_MUAT_KET' => '',
        //     'offerDetails.*.BURUH_SALIN' => '',
        //     'offerDetails.*.BURUH_SALIN_KET' => '',
        //     'offerDetails.*.BURUH_BONGKAR' => '',
        //     'offerDetails.*.ASURANSI' => 'required',
        //     'offerDetails.*.TSI' => '',
        //     'offerDetails.*.FREE_TIME_STORAGE' => 'required',
        //     'offerDetails.*.FREE_TIME_DEMURRAGE' => 'required',
        //     'offerDetails.*.KODE_COMMODITY' => 'required',
        //     'offerDetails.*.KODE_SERVICE' => 'required',
        //     'offerDetails.*.HARGA' => 'required',
        //     'offerDetails.*.SATUAN_HARGA' => 'required',
        // ], [
        //     'KODE_CUSTOMER.required' => 'Kode Customer is required',
        //     'TANGGAL.required' => 'Tanggal is required',
        //     'TANGGAL.date' => 'Tanggal must be a date',
        //     'offerDetails.required' => 'Offer Details is required',
        //     'offerDetails.array' => 'Offer Details must be an array',
        //     'offerDetails.*.KODE_PRAJOA.required' => 'Kode Prajoa is required',
        //     'STATUS.required' => 'Status is required',
        //     'PAYMENT.required' => 'Payment is required',
        //     'TOP.required' => 'Top is required',
        //     'KETERANGAN_TOP.required' => 'Keterangan Top is required',
        //     'SALES.required' => 'Sales is required',
        //     'offerDetails.*.ASURANSI.required' => 'Asuransi is required',
        //     'offerDetails.*.FREE_TIME_STORAGE.required' => 'Free Time Storage is required',
        //     'offerDetails.*.FREE_TIME_DEMURRAGE.required' => 'Free Time Demurrage is required',
        //     'offerDetails.*.KODE_COMMODITY.required' => 'Kode Commodity is required',
        //     'offerDetails.*.KODE_SERVICE.required' => 'Kode Service is required',
        //     'offerDetails.*.HARGA.required' => 'Harga is required',
        //     'offerDetails.*.SATUAN_HARGA.required' => 'Satuan Harga is required',
        // ]);

        $validator = Validator::make($request->all(), [


            'KODE_CUSTOMER' => '',
            'KODE_JENIS_ORDER' => '',
            'RATE_STATUS' => '',
            'NAMA_CUSTOMER' => '',
            'CONTACT_PERSON' => '',
            'EMAIL' => '',
            'STATUS' => 'required',
            'PPN' => '',
            'PPN_PERCENTAGE' => '',
            'PPH' => '',
            'PAYMENT' => 'required',
            'TOP' => 'required',
            'KETERANGAN_TOP' => 'required',
            'KETERANGAN_TAMBAHAN' => '',
            'SALES' => 'required',
            'TANGGAL' => 'required|date',

            'KODE_PRAJOA' => 'array',
            'KODE_POL' => 'array',
            'KODE_POD' => 'array',
            'KODE_DOOR_POL' => 'array',
            'KODE_DOOR_POD' => 'array',
            'KODE_UK_KONTAINER' => 'array',
            'KODE_JENIS_CONTAINER' => 'array',
            'STUFFING' => 'array',
            'STRIPPING' => 'array',
            'BURUH_MUAT' => 'array',
            'BURUH_MUAT_KET' => 'array',
            'BURUH_SALIN' => 'array',
            'BURUH_SALIN_KET' => 'array',
            'BURUH_BONGKAR' => 'array',
            'ASURANSI' => 'required|array',
            'TSI' => 'array',
            'TSI_NOMINAL' => 'array',
            'FREE_TIME_STORAGE' => 'required|array',
            'FREE_TIME_DEMURRAGE' => 'required|array',
            'KODE_COMMODITY' => 'required|array',
            'KODE_SERVICE' => 'required|array',
            'HARGA' => 'array',
            'SATUAN_HARGA' => 'required|array',
        ], [
            'KODE_CUSTOMER.required' => 'Kode Customer is required',
            'KODE_JENIS_ORDER.required' => 'Kode Jenis Order is required',
            'RATE_STATUS.required' => 'Rate Status is required',
            'NAMA_CUSTOMER.required' => 'Nama Customer is required',
            'CONTACT_PERSON.required' => 'Contact Person is required',
            'EMAIL.required' => 'Email is required',
            'STATUS.required' => 'Status is required',
            'PPN.required' => 'PPN is required',
            'PPN_PERCENTAGE.required' => 'PPN Percentage is required',
            'PPH.required' => 'PPH is required',
            'PAYMENT.required' => 'Payment is required',
            'TOP.required' => 'Top is required',
            'KETERANGAN_TOP.required' => 'Keterangan Top is required',
            'KETERANGAN_TAMBAHAN.required' => 'Keterangan Tambahan is required',
            'SALES.required' => 'Sales is required',
            'TANGGAL.required' => 'Tanggal is required',
            'TANGGAL.date' => 'Tanggal must be a date',
            'KODE_PRAJOA.array' => 'Kode Prajoa must be an array',
            'KODE_POL.array' => 'Kode Pol must be an array',
            'KODE_POD.array' => 'Kode Pod must be an array',
            'KODE_DOOR_POL.array' => 'Kode Door Pol must be an array',
            'KODE_DOOR_POD.array' => 'Kode Door Pod must be an array',
            'KODE_UK_KONTAINER.array' => 'Kode Uk Kontainer must be an array',
            'KODE_JENIS_CONTAINER.array' => 'Kode Jenis Container must be an array',
            'STUFFING.array' => 'Stuffing must be an array',
            'STRIPPING.array' => 'Stripping must be an array',
            'BURUH_MUAT.array' => 'Buruuh Muat must be an array',
            'BURUH_MUAT_KET.array' => 'Buruuh Muat Ket must be an array',
            'BURUH_SALIN.array' => 'Buruuh Salin must be an array',
            'BURUH_SALIN_KET.array' => 'Buruuh Salin Ket must be an array',
            'BURUH_BONGKAR.array' => 'Buruuh Bongkar must be an array',
            'ASURANSI.required' => 'Asuransi is required',
            'ASURANSI.array' => 'Asuransi must be an array',
            'TSI.array' => 'TSI must be an array',
            'TSI_NOMINAL.array' => 'TSI Nominal must be an array',
            'FREE_TIME_STORAGE.required' => 'Free Time Storage is required',
            'FREE_TIME_STORAGE.array' => 'Free Time Storage must be an array',
            'FREE_TIME_DEMURRAGE.required' => 'Free Time Demurrage is required',
            'FREE_TIME_DEMURRAGE.array' => 'Free Time Demurrage must be an array',
            'KODE_COMMODITY.required' => 'Kode Commodity is required',
            'KODE_COMMODITY.array' => 'Kode Commodity must be an array',
            'KODE_SERVICE.required' => 'Kode Service is required',
            'KODE_SERVICE.array' => 'Kode Service must be an array',
            'HARGA.array' => 'Harga must be an array',
            'SATUAN_HARGA.required' => 'Satuan Harga is required',
            'SATUAN_HARGA.array' => 'Satuan Harga must be an array',
        ]);


        //if validator fails
        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }

        // Check uniqueness for NAMA case insensitive except for current KODE
        // $penawarans = Offer::where(DB::raw('LOWER("NAMA_BIAYA")'), strtolower($request->NAMA_BIAYA))->where('KODE', '!=', $KODE)->first();
        // if ($penawarans) {
        //     return ApiResponse::json(false, ['NAMA_BIAYA' => ['The NAMA_BIAYA has already been taken.']], null, 422);
        // }
        try {
            $validatedData = $validator->validated();
            //for i in tsi check if it's between 0-100
            foreach ($validatedData['TSI'] as $tsi) {
                if ($tsi < 0 || $tsi > 100) {
                    return ApiResponse::json(false, ['TSI[]' => ['TSI must be 0-100']], null, 422);
                }
            }



            $penawarans = Offer::findOrFail($KODE);



            $penawarans->KODE_JENIS_ORDER = $validatedData['KODE_JENIS_ORDER'];
            $penawarans->KODE_CUSTOMER = $validatedData['KODE_CUSTOMER'];
            $penawarans->TANGGAL = $validatedData['TANGGAL'];
            $penawarans->RATE_STATUS = $validatedData['RATE_STATUS'];
            $penawarans->NAMA_CUSTOMER = $validatedData['NAMA_CUSTOMER'];
            $penawarans->CONTACT_PERSON = $validatedData['CONTACT_PERSON'];
            $penawarans->EMAIL = $validatedData['EMAIL'];
            $penawarans->STATUS = $validatedData['STATUS'];
            $penawarans->PPN = $validatedData['PPN'];
            $penawarans->PPH = $validatedData['PPH'];
            $penawarans->PAYMENT = $validatedData['PAYMENT'];
            $penawarans->TOP = $validatedData['TOP'];
            $penawarans->KETERANGAN_TOP = $validatedData['KETERANGAN_TOP'];
            $penawarans->KETERANGAN_TAMBAHAN = $validatedData['KETERANGAN_TAMBAHAN'];
            $penawarans->SALES = $validatedData['SALES'];


            $penawarans->save();
            if (!$penawarans) {
                return ApiResponse::json(false, 'Failed to update data', null, 500);
            }



            // find offerdetails where kode_penawaran = $KODE
            // $offerDetails2 = OfferDetail::where('KODE_PENAWARAN', $KODE)->get();

            // return ApiResponse::json(false, 'Failed to update data', $request->offerDetails);
            // foreach offerdetails, and update those offerdetails
            // foreach ($offerDetails2 as $offerDetail) {


            // foreach ($request->offerDetails as $offerDetail) {
            //     // return ApiResponse::json(false, $request->offerDetails, NULL, 500);

            //     // get offerDetail's kode


            //     try {

            //         $OFFER_Detail = OfferDetail::findOrFail($offerDetail['KODE']);

            //         // update offer_detail
            //         $OFFER_Detail->KODE_PENAWARAN = $KODE;


            //         $OFFER_Detail->KODE_PRAJOA = $offerDetail['KODE_PRAJOA'];


            //         $OFFER_Detail->KODE_POL = $offerDetail['KODE_POL'];
            //         $OFFER_Detail->KODE_POD = $offerDetail['KODE_POD'];
            //         $OFFER_Detail->KODE_DOOR_POL = $offerDetail['KODE_DOOR_POL'];
            //         $OFFER_Detail->KODE_DOOR_POD = $offerDetail['KODE_DOOR_POD'];

            //         $OFFER_Detail->KODE_UK_KONTAINER = $offerDetail['KODE_UK_KONTAINER'];
            //         $OFFER_Detail->KODE_JENIS_CONTAINER = $offerDetail['KODE_JENIS_CONTAINER'];
            //         $OFFER_Detail->STUFFING = $offerDetail['STUFFING'];
            //         $OFFER_Detail->STRIPPING = $offerDetail['STRIPPING'];
            //         $OFFER_Detail->BURUH_MUAT = $offerDetail['BURUH_MUAT'];
            //         $OFFER_Detail->BURUH_MUAT_KET = $offerDetail['BURUH_MUAT_KET'];
            //         $OFFER_Detail->BURUH_SALIN = $offerDetail['BURUH_SALIN'];
            //         $OFFER_Detail->BURUH_SALIN_KET = $offerDetail['BURUH_SALIN_KET'];

            //         $OFFER_Detail->BURUH_BONGKAR = $offerDetail['BURUH_BONGKAR'];
            //         $OFFER_Detail->ASURANSI = $offerDetail['ASURANSI'];
            //         $OFFER_Detail->TSI = $offerDetail['TSI'];
            //         $OFFER_Detail->FREE_TIME_STORAGE = $offerDetail['FREE_TIME_STORAGE'];
            //         $OFFER_Detail->FREE_TIME_DEMURRAGE = $offerDetail['FREE_TIME_DEMURRAGE'];
            //         $OFFER_Detail->HARGA = $offerDetail['HARGA'];
            //         $OFFER_Detail->SATUAN_HARGA = $offerDetail['SATUAN_HARGA'];
            //         $OFFER_Detail->KODE_COMMODITY = $offerDetail['KODE_COMMODITY'];
            //         $OFFER_Detail->KODE_SERVICE = $offerDetail['KODE_SERVICE'];
            //         $OFFER_Detail->save();
            //     } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            //         $OFFER_Detail = new OfferDetail();
            //         $OFFER_Detail->KODE = $this->getLocalNextIdDetails();
            //         $OFFER_Detail->KODE_PENAWARAN = $KODE;
            //         $OFFER_Detail->KODE_PRAJOA = $offerDetail['KODE_PRAJOA'];


            //         $OFFER_Detail->KODE_POL = $offerDetail['KODE_POL'];
            //         $OFFER_Detail->KODE_POD = $offerDetail['KODE_POD'];
            //         $OFFER_Detail->KODE_DOOR_POL = $offerDetail['KODE_DOOR_POL'];
            //         $OFFER_Detail->KODE_DOOR_POD = $offerDetail['KODE_DOOR_POD'];

            //         $OFFER_Detail->KODE_UK_KONTAINER = $offerDetail['KODE_UK_KONTAINER'];
            //         $OFFER_Detail->KODE_JENIS_CONTAINER = $offerDetail['KODE_JENIS_CONTAINER'];
            //         $OFFER_Detail->STUFFING = $offerDetail['STUFFING'];
            //         $OFFER_Detail->STRIPPING = $offerDetail['STRIPPING'];
            //         $OFFER_Detail->BURUH_MUAT = $offerDetail['BURUH_MUAT'];
            //         $OFFER_Detail->BURUH_MUAT_KET = $offerDetail['BURUH_MUAT_KET'];
            //         $OFFER_Detail->BURUH_SALIN = $offerDetail['BURUH_SALIN'];
            //         $OFFER_Detail->BURUH_SALIN_KET = $offerDetail['BURUH_SALIN_KET'];
            //         $OFFER_Detail->BURUH_BONGKAR = $offerDetail['BURUH_BONGKAR'];
            //         $OFFER_Detail->ASURANSI = $offerDetail['ASURANSI'];
            //         $OFFER_Detail->TSI = $offerDetail['TSI'];
            //         $OFFER_Detail->FREE_TIME_STORAGE = $offerDetail['FREE_TIME_STORAGE'];
            //         $OFFER_Detail->FREE_TIME_DEMURRAGE = $offerDetail['FREE_TIME_DEMURRAGE'];
            //         $OFFER_Detail->HARGA = $offerDetail['HARGA'];
            //         $OFFER_Detail->SATUAN_HARGA = $offerDetail['SATUAN_HARGA'];
            //         $OFFER_Detail->KODE_COMMODITY = $offerDetail['KODE_COMMODITY'];
            //         $OFFER_Detail->KODE_SERVICE = $offerDetail['KODE_SERVICE'];
            //         $OFFER_Detail->save();
            //     }
            // }

            //delete previous offer details
            $offer_details = OfferDetail::where('KODE_PENAWARAN', $KODE)->get();
            foreach ($offer_details as $offer_detail) {
                $offer_detail->delete();
            }


            foreach ($validatedData['KODE_COMMODITY'] as $index => $offerDetailData) {
                // return ApiResponse::json(false, $offerDetailData, null, 500);
                $new_offer_details = new OfferDetail();
                $new_offer_details->KODE = $this->getLocalNextIdDetails();
                $new_offer_details->KODE_PENAWARAN = $KODE;
                $new_offer_details->KODE_PRAJOA = $validatedData['KODE_PRAJOA'][$index];


                $new_offer_details->KODE_POL = $validatedData['KODE_POL'][$index];
                $new_offer_details->KODE_POD = $validatedData['KODE_POD'][$index];
                $new_offer_details->KODE_DOOR_POL = $validatedData['KODE_DOOR_POL'][$index];
                $new_offer_details->KODE_DOOR_POD = $validatedData['KODE_DOOR_POD'][$index];

                $new_offer_details->KODE_UK_KONTAINER = $validatedData['KODE_UK_KONTAINER'][$index];
                $new_offer_details->KODE_JENIS_CONTAINER = $validatedData['KODE_JENIS_CONTAINER'][$index];
                $new_offer_details->STUFFING = $validatedData['STUFFING'][$index];
                $new_offer_details->STRIPPING = $validatedData['STRIPPING'][$index];
                $new_offer_details->BURUH_MUAT = $validatedData['BURUH_MUAT'][$index];
                $new_offer_details->BURUH_MUAT_KET = $validatedData['BURUH_MUAT_KET'][$index];
                $new_offer_details->BURUH_SALIN = $validatedData['BURUH_SALIN'][$index];
                $new_offer_details->BURUH_SALIN_KET = $validatedData['BURUH_SALIN_KET'][$index];
                $new_offer_details->BURUH_BONGKAR = $validatedData['BURUH_BONGKAR'][$index];
                $new_offer_details->ASURANSI = $validatedData['ASURANSI'][$index];
                $new_offer_details->TSI = $validatedData['TSI'][$index];
                $new_offer_details->TSI_NOMINAL = $validatedData['TSI_NOMINAL'][$index];
                $new_offer_details->FREE_TIME_STORAGE = $validatedData['FREE_TIME_STORAGE'][$index];
                $new_offer_details->FREE_TIME_DEMURRAGE = $validatedData['FREE_TIME_DEMURRAGE'][$index];
                $new_offer_details->HARGA = $validatedData['HARGA'][$index];
                $new_offer_details->SATUAN_HARGA = $validatedData['SATUAN_HARGA'][$index];
                $new_offer_details->KODE_COMMODITY = $validatedData['KODE_COMMODITY'][$index];
                $new_offer_details->KODE_SERVICE = $validatedData['KODE_SERVICE'][$index];
                // return ApiResponse::json(false, $new_offer_details, null, 500);
                $new_offer_details->save();
                // return ApiResponse::json(false, 'aaaaaaa', null, 500);

                if (!$new_offer_details) {
                    return ApiResponse::json(false, 'Failed to insert data', null, 500);
                }
            }

            // return ApiResponse::json(false, 'Failed to update data', $OFFER_Detail->KODE_PRAJOA);



            // $resp_penawaran = array(
            //     'KODE' => $penawarans->KODE,
            //     'KODE_RUTE_TRUCK' => $penawarans->KODE_RUTE_TRUCK,
            //     'NAMA_CUSTOMER' => Customer::where('KODE', $penawarans->KODE_CUSTOMER)->first()->NAMA,
            //     // 'KODE_RUTE_TRUCK' => TruckRoute::where('KODE', $penawarans->KODE_RUTE_TRUCK)->first()->KODE,
            //     // 'NAMA_VENDOR' => Vendor::where('KODE', $penawarans->KODE_VENDOR)->first()->NAMA,
            //     'NAMA_COMMODITY' => Commodity::where('KODE', $penawarans->KODE_COMMODITY)->first()->NAMA,
            //     // 'NAMA_UK_KONTAINER' => Size::where('KODE', $penawarans->UK_KONTAINER)->first()->KETERANGAN,
            //     'NAMA_TRUCK' => Truck::where('KODE', $penawarans->KODE_TRUCK)->first()->NAMA,
            //     'BERLAKU' => $penawarans->BERLAKU,

            //     'HARGA_JUAL' => $penawarans->HARGA_JUAL,
            //     'KETERANGAN' => $penawarans->KETERANGAN,
            // );

            $OfferFinal = Offer::where('KODE', $KODE)->first();

            // $OfferFinal->NAMA_JABATAN = Position::where('KODE', $OfferFinal->KODE_JABATAN)->first()->NAMA;
            // $OfferFinal->NAMA_LOKASI = Warehouse::where('KODE', $OfferFinal->KODE_LOKASI)->first()->NAMA;


            // insert offerdetails to offerFinal
            $offerDetailss = OfferDetail::where('KODE_PENAWARAN', $KODE)->get();
            $OfferFinal->offer_details = $offerDetailss->toArray();


            //convert to array
            $OfferFinal = $OfferFinal->toArray();
            return ApiResponse::json(true, 'penawaran successfully updated', $OfferFinal);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e->getMessage(), null, 500);
        }
    }

    public function viewDetail($KODE)
    {

        try {
            $offer = Offer::findOrFail($KODE);
            // if($offer->KODE_JENIS_ORDER == 'LCL'){
            // }

            // add offerdetails to offer
            $offerDetails = OfferDetail::where('KODE_PENAWARAN', $KODE)->get();


            $offer->NAMA_CUSTOMER_LAMA = $offer->KODE_CUSTOMER !== null ? Customer::where('KODE', $offer->KODE_CUSTOMER)->first()->NAMA : null;
            $offer->EMAIL_CUSTOMER = $offer->KODE_CUSTOMER !== null ? Customer::where('KODE', $offer->KODE_CUSTOMER)->first()->EMAIL : null;
            $offer->TELP_CUSTOMER = $offer->KODE_CUSTOMER !== null ? Customer::where('KODE', $offer->KODE_CUSTOMER)->first()->TELP : null;

            $customer = $offer->KODE_CUSTOMER !== null ? Customer::where('KODE', $offer->KODE_CUSTOMER)->first() : null;

            $offer->HP_CUSTOMER = $customer !== null ? $customer->HP : null;
            $offer->CONTACT_PERSON_1 = $customer !== null ? $customer->CONTACT_PERSON_1 : null;

            $offer->NAMA_STAFF = Staff::where('KODE', $offer->SALES)->first()->NAMA;
            $offer->NO_HP_STAFF = Staff::where('KODE', $offer->SALES)->first()->NO_HP;
            // $offer->KODE_JENIS_ORDER = OrderType::where('KODE', $offer->KODE_JENIS_ORDER)->first()->NAMA;

            $types = [];
            foreach ($offerDetails as $offerDetail) {
                $offerDetail['NAMA_COMMODITY'] = Commodity::where('KODE', $offerDetail['KODE_COMMODITY'])->first()->NAMA;
                $offerDetail['NAMA_SERVICE'] = Service::where('KODE', $offerDetail['KODE_SERVICE'])->first()->NAMA;

                // check first if its null
                if ($offerDetail['KODE_UK_KONTAINER'] != null) {
                    $offerDetail['NAMA_UK_KONTAINER'] = Size::where('KODE', $offerDetail['KODE_UK_KONTAINER'])->first()->NAMA;
                } else {
                    $offerDetail['NAMA_UK_KONTAINER'] = "";
                }
                $offerDetail['RUTE'] = optional(Harbor::where('KODE', $offerDetail['KODE_POL'])->first())->NAMA_PELABUHAN . ' - ' . optional(Harbor::where('KODE', $offerDetail['KODE_POD'])->first())->NAMA_PELABUHAN;


                $offerDetail['HARGA'] = "Rp. " . number_format($offerDetail['HARGA'], 0, ',', '.') . ",-";

                if ($offerDetail['SATUAN_HARGA'] != null) {
                    $offerDetail['SATUAN_HARGA'] = Unit::where('KODE', $offerDetail['SATUAN_HARGA'])->first()->NAMA_SATUAN;
                } else {
                    $offerDetail['SATUAN_HARGA'] = "";
                }

                if ($offerDetail['KODE_DOOR_POL'] != null) {
                    $offerDetail['DOOR_POL'] = TruckRoute::where('KODE', $offerDetail['KODE_DOOR_POL'])->first()->RUTE_ASAL . ' - ' . TruckRoute::where('KODE', $offerDetail['KODE_DOOR_POL'])->first()->RUTE_TUJUAN;
                } else {
                    $offerDetail['DOOR_POL'] = "";
                }

                if ($offerDetail['KODE_DOOR_POD'] != null) {
                    $offerDetail['DOOR_POD'] = TruckRoute::where('KODE', $offerDetail['KODE_DOOR_POD'])->first()->RUTE_ASAL . ' - ' . TruckRoute::where('KODE', $offerDetail['KODE_DOOR_POD'])->first()->RUTE_TUJUAN;
                } else {
                    $offerDetail['DOOR_POD'] = "";
                }

                if (($offerDetail['KODE_POL'] == "" || $offerDetail['KODE_POL'] == null) && ($offerDetail['KODE_POD'] == "" || $offerDetail['KODE_POD'] == null)) {
                    array_push($types, "DOOR TO DOOR");
                } else {
                    array_push($types, "PORT TO PORT");
                }
            }

            $allDoorToDoor = true; // Assume all elements are "DOOR TO DOOR"

            foreach ($types as $type) {
                if ($type !== "DOOR TO DOOR") {
                    $allDoorToDoor = false; // At least one element is not "DOOR TO DOOR"
                    break; // No need to continue checking, we already know it's not all "DOOR TO DOOR"
                }
            }

            if ($allDoorToDoor) {
                $offer->DOORPORT = "DOOR TO DOOR";
            } else {
                $offer->DOORPORT = "PORT TO PORT";
            }
            $offer->offer_details = $offerDetails->toArray();



            return ApiResponse::json(true, 'penawaran successfully deleted', ['DATA' => $offer]);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e->getMessage(), null, 500);
        }
    }

    public function destroy($KODE)
    {
        try {
            $city = Offer::findOrFail($KODE);
            $city->delete();
            return ApiResponse::json(true, 'penawaran successfully deleted', ['KODE' => $KODE]);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to delete penawaran', null, 500);
        }
    }

    public function trash()
    {
        try {

            $penawarans = Offer::onlyTrashed()->get();


            // foreach ($penawarans as $penawaran) {

            //     // $penawaran->offer_details = OfferDetail::where('KODE_PENAWARAN', $penawaran->KODE)->get();
            //     // $penawaran->offer_details = OfferDetail::where('KODE_PENAWARAN', $penawaran->KODE)->get()->toArray();


            //     // foreach ($penawaran->offer_details as $offer_detail) {
            //     // }
            // }
            // return ApiResponse::json(false, $jen, null, 500);
            return ApiResponse::json(true, 'Trash Bin Fetched', $penawarans);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to retrieve data', null, 500);
        }
    }

    public function restore($id)
    {
        $restored = Offer::onlyTrashed()->findOrFail($id);
        $restored->restore();

        return ApiResponse::json(true, 'Restored', ['KODE' => $id]);
    }

    public function getNextIdDetails()
    {
        $nextId = $this->getLocalNextIdDetails();

        return ApiResponse::json(true, 'Next KODE retrieved successfully', $nextId);
    }

    public function getLocalNextIdDetails()
    {
        // Get the maximum COM.X ID from the database
        $maxId = OfferDetail::where('KODE', 'LIKE', 'PNWD.%')
            ->withTrashed()
            ->orderByRaw('CAST(SUBSTRING("KODE" FROM \'\\.([0-9]+)$\') AS INTEGER) DESC, "KODE" DESC')
            ->value('KODE');

        if ($maxId) {
            // Extract the number from the maximum ID
            $maxNumber = (int) substr($maxId, strpos($maxId, '.') + 1);

            // Increment the number
            $nextNumber = $maxNumber + 1;
        } else {
            // If no existing IDs found, start with 1
            $nextNumber = 1;
        }

        // Create the next ID by concatenating 'COM.' with the incremented number
        $nextId = 'PNWD.' . $nextNumber;

        return $nextId;
    }

    public function getNextId()
    {
        $nextId = $this->getLocalNextId('PNW');

        return ApiResponse::json(true, 'Next KODE retrieved successfully', $nextId);
    }

    public function getLocalNextId($type)
    {
        // Get the maximum PJA.XXXXXX ID from the database

        //get today's date
        $currentDay = date('d');
        $currentMonth = date('m');
        $currentYear = date('y');

        $maxId = DB::table('offers')
            ->where('KODE', 'LIKE', "$type." . $currentDay . $currentMonth . $currentYear . '%')
            ->orderByRaw('CAST(SUBSTRING("KODE" FROM \'\\-([0-9]+)\\-\') AS INTEGER) DESC, "KODE" DESC')
            ->value('KODE');

        if ($maxId) {

            // Find the position of the first '-' character
            $firstDashPos = strpos($maxId, '-');

            // Find the position of the next '-' character, starting the search from the position after the first '-'
            $nextDashPos = strpos($maxId, '-', $firstDashPos + 1);

            // Extract the substring between the first '-' and the next '-' (excluding the first '-')
            $substring = substr($maxId, $firstDashPos + 1, $nextDashPos - $firstDashPos - 1);

            // Convert the substring to an integer
            $lastSequence = (int)$substring;


            // Get the current day, month, and year

            // Check if the current day, month, and year are the same as the last entry
            if (substr($maxId, 4, 6) === $currentDay . $currentMonth . $currentYear) {
                // If the current day, month, and year are the same, increment the sequence number
                $nextSequence = $lastSequence + 1;
            } else {
                // If the current day, month, and year are different, reset the sequence number to 1
                $nextSequence = 1;
            }

            // Create the next ID by concatenating "$type'"with the current day, month, and year, sequence, and revision
            $nextId = "$type." . $currentDay . $currentMonth . $currentYear . '-' . $nextSequence;
        } else {
            // If no existing IDs found, start with 1 for sequence and revision
            $currentDay = date('d');
            $currentMonth = date('m');
            $currentYear = date('y');
            $nextId = "$type." . $currentDay . $currentMonth . $currentYear . '-1';
        }

        return $nextId;
    }
}
