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

class PraJoaController extends Controller
{

    public function dataTableJson(Request $request)
    {
        // try {
        $order = $request->input('order')[0];
        $columnIndex = (int) $order['column'];
        $sortDirection = $order['dir'];

        $columns = $request->input('columns');
        $columnToSort = $columns[$columnIndex]['data'];

        $query = PraJoa::query();

        $hasSearch = $request->has('search') && !empty($request->input('search')['value']);
        $dateColumns = []; // Add your date column names here
        // Apply search filter if applicable
        if ($hasSearch) {
            $searchValue = strtoupper($request->input('search')['value']);
            $attributes = PraJoa::first()->getAttributes(); // Get all attribute names
            $query->where(function ($q) use ($searchValue, $attributes, $dateColumns) {
                foreach ($attributes as $attribute => $value) {

                    if (in_array($attribute, $dateColumns)) {
                        // Modify the attribute value if it's a date column
                        $q->orWhere(DB::raw("TO_CHAR(\"$attribute\", 'dd-mm-yyyy')"), 'LIKE', "%$searchValue%");
                    } else {
                        $q->orWhere($attribute, 'LIKE', "%$searchValue%");
                    }
                }
            });

            $relatedColumns = [
                'customer' => [
                    'table' => 'customers',
                    'column' => 'NAMA',
                ],
                'vendor' => [
                    'table' => 'vendors',
                    'column' => 'NAMA',
                ],
                'pol' => [
                    'table' => 'harbors',
                    'column' => 'NAMA_PELABUHAN',
                ],
                'pod' => [
                    'table' => 'harbors',
                    'column' => 'NAMA_PELABUHAN',
                ],
                'uk_container' => [
                    'table' => 'sizes',
                    'column' => 'NAMA',
                ],
                'jenis_container' => [
                    'table' => 'container_types',
                    'column' => 'NAMA',
                ],
                'jenis_order' => [
                    'table' => 'order_types',
                    'column' => 'NAMA',
                ],
                'commodity' => [
                    'table' => 'commodities',
                    'column' => 'NAMA',
                ],
                'service' => [
                    'table' => 'services',
                    'column' => 'NAMA',
                ],
                'thc_pol' => [
                    'table' => 'thc_lolos',
                    'column' => 'THC',
                ],
                'thc_pod' => [
                    'table' => 'thc_lolos',
                    'column' => 'THC',
                ],
                'hpp_biaya_buruh_muat' => [
                    'table' => 'cost_rates',
                    'column' => 'TARIF',
                ],
                'hpp_biaya_buruh_stripping' => [
                    'table' => 'cost_rates',
                    'column' => 'TARIF',
                ],
                'hpp_biaya_buruh_bongkar' => [
                    'table' => 'cost_rates',
                    'column' => 'TARIF',
                ],
                'rute_truck_pol' => [
                    'table' => 'truck_routes',
                    'column' => 'KODE',
                ],
                'rute_truck_pod' => [
                    'table' => 'truck_routes',
                    'column' => 'KODE',
                ],
                'hpp_biaya_seal' => [
                    'table' => 'cost_rates',
                    'column' => 'TARIF',
                ],
                'hpp_biaya_ops' => [
                    'table' => 'cost_rates',
                    'column' => 'TARIF',
                ],


            ];

            foreach ($relatedColumns as $relation => $relatedColumn) {
                $query->orWhereHas($relation, function ($q) use ($searchValue, $relatedColumn) {
                    $q->where($relatedColumn['column'], 'LIKE', "%$searchValue%");
                });
            }
        }

        // $query->orderByRaw('CAST(SUBSTRING("KODE" FROM \'\\.([0-9]+)$\') AS INTEGER) ASC, "KODE" ASC');
        // $query->orderByRaw("substring(\"NOMOR\" from '\\.([0-9]+)')::integer ASC, \"NOMOR\" ASC");
        // order by created_at desc
        $query->orderBy('created_at', 'desc');



        $allData = $query->get();



        if (!empty($columnToSort)) {
            foreach ($allData as $prajoa) {
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
            $sortBoolean = $sortDirection === 'asc' ? false : true;
            //check if columnToSort is KODE
            if ($columnIndex === 1) {
                //include the sort boolean
                // $allData = $allData->sortBy(function ($data) {
                //     return (int) substr($data->KODE, strpos($data->KODE, '.') + 1);
                // }, SORT_REGULAR, $sortBoolean);
                //all data order by created_at desc date
                $allData = $allData->sortByDesc('created_at');
            } else {
                $allData = $allData->sortBy($columnToSort, SORT_REGULAR, $sortBoolean);
            }
        }



        // Apply pagination and limit
        $limit = $request->get('length', 10);
        $total_data_after_search = $query->count();
        if ($limit == -1) {
            $limit = $total_data_after_search; // Use the total count after search filters
        }
        $page = $request->get('start', 0) / $limit + 1;
        $skip = ($page - 1) * $limit;

        // $customers = array_slice($allDataArray, $skip, $limit);
        $prajoas = $allData->slice($skip)->take($limit);



        // ... rest of your code to enrich the data ...
        if (empty($columnToSort)) {
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
        }



        // Prepare the response data structure expected by the frontend

        $response = [
            'draw' => $request->input('draw'),
            'recordsTotal' => PraJoa::count(),
            'recordsFiltered' => $total_data_after_search,
            'data' => $prajoas->values()->toArray(),
        ];



        return ApiResponse::json(true, 'Data retrieved successfully', $response);
        // } catch (\Exception $e) {
        //     return ApiResponse::json(false, $e, null, 500);
        // }
    }

    public function dataTableAdvJson(Request $request)
    {
        //related to function
        $relationColumnsAndTo = [
            3 => ['customer', 'NAMA'],
            4 => ['vendor', 'NAMA'],
            5 => ['POL', 'NAMA_PELABUHAN'],
            6 => ['POD', 'NAMA_PELABUHAN'],
            7 => ['uk_container', 'NAMA'],
            8 => ['jenis_container', 'NAMA'],
            9 => ['jenis_order', 'NAMA'],
            10 => ['commodity', 'NAMA'],
            11 => ['service', 'NAMA'],
            13 => ['thc_pol', 'THC'],
            17 => ['thc_pod', 'THC'],
            25 => ['hpp_biaya_buruh_muat', 'TARIF'],
            30 => ['hpp_biaya_buruh_stripping', 'TARIF'],
            32 => ['hpp_biaya_buruh_bongkar', 'TARIF'],
            43 => ['rute_truck_pol', 'KODE'],
            45 => ['rute_truck_pod', 'KODE'],
            56 => ['hpp_biaya_seal', 'TARIF'],
            58 => ['hpp_biaya_ops', 'TARIF'],
        ];
        try {
            $order = $request->input('order')[0];
            $columnIndex = (int) $order['column'];
            $sortDirection = $order['dir'];

            $columns = $request->input('columns');
            $columnToSort = $columns[$columnIndex]['data'];

            $query = PraJoa::query();

            //check for each column hasSearch columns[x][search][value]
            $columns = $request->input('columns');
            $hasSearch = false;
            foreach ($columns as $index => $column) {
                if (isset($column['search']['value']) && !empty($column['search']['value'])) {
                    $hasSearch = true;
                    break;
                }
            }


            $dateColumns = []; // Add your date column names here

            if ($hasSearch) {
                foreach ($columns as $index => $column) {
                    if (isset($column['search']['value']) && !empty($column['search']['value'])) {
                        if ($column['searchable'] === 'true') {
                            $searchValue = strtoupper($column['search']['value']);
                            // Remove parentheses from search value
                            $searchValue = str_replace(['(', ')'], '', $searchValue);

                            if (array_key_exists($index, $relationColumnsAndTo)) {
                                [$relation, $relatedColumn] = $relationColumnsAndTo[$index];

                                $query->whereHas($relation, function ($q) use ($searchValue, $relatedColumn) {
                                    $q->where($relatedColumn, 'LIKE', "%$searchValue%");
                                });
                            } else {
                                $columnName = $columns[$index]['data'];
                                if (in_array($columnName, $dateColumns)) {
                                    // Modify the attribute value if it's a date column
                                    $query->where(DB::raw("TO_CHAR(\"$columnName\", 'dd-mm-yyyy')"), 'LIKE', "%$searchValue%");
                                } else {
                                    $query->where($columnName, 'LIKE', "%$searchValue%");
                                }
                            }
                        }
                    }
                }
            }

            // $query->orderByRaw('CAST(SUBSTRING("KODE" FROM \'\\.([0-9]+)$\') AS INTEGER) ASC, "KODE" ASC');
            // order by created_at desc
            $query->orderBy('created_at', 'desc');

            $allData = $query->get();



            if (!empty($columnToSort)) {
                foreach ($allData as $prajoa) {

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
                $sortBoolean = $sortDirection === 'asc' ? false : true;
                //check if columnToSort is KODE
                if ($columnIndex === 1) {

                    //include the sort boolean
                    $allData = $allData->sortByDesc('created_at');
                } else {
                    $allData = $allData->sortBy($columnToSort, SORT_REGULAR, $sortBoolean);
                }
            }

            // Convert the collection of cities to an array

            // Apply pagination and limit
            $limit = $request->get('length', 10);
            $total_data_after_search = $query->count();
            if ($limit == -1) {
                $limit = $total_data_after_search; // Use the total count after search filters
            }
            $page = $request->get('start', 0) / $limit + 1;
            $skip = ($page - 1) * $limit;

            $prajoas = $allData->slice($skip)->take($limit);

            if (empty($columnToSort)) {
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
            }


            // ... rest of your code to enrich the data ...




            $response = [
                'draw' => $request->input('draw'),
                'recordsTotal' => PraJoa::count(),
                'recordsFiltered' => $total_data_after_search,
                'data' => $prajoas->values()->toArray(),
            ];

            return ApiResponse::json(true, 'Data retrieved successfully', $response);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }

    public function findByKode($KODE)
    {
        try {
            $prajoa = PraJoa::where('NOMOR', $KODE)->first();
            if (!$prajoa) {
                return ApiResponse::json(false, 'Data not found', null, 404);
            }

            $prajoaOtherCosts = PraJoaOtherCost::where('NOMOR_PRAJOA', $prajoa->NOMOR)->get();
            //for each othercosts, get the TARIF
            foreach ($prajoaOtherCosts as $prajoaOtherCost) {
                $prajoaOtherCost->TARIF = CostRate::where('KODE', $prajoaOtherCost->KODE_HPP_BIAYA)->first()->TARIF;
            }

            $prajoa->KODE_BIAYA_LAIN = $prajoaOtherCosts->toArray();
            //get the costrate TARIF
            $prajoa->TARIF_HPP_BIAYA_BURUH_MUAT = $prajoa->KODE_HPP_BIAYA_BURUH_MUAT ? CostRate::where('KODE', $prajoa->KODE_HPP_BIAYA_BURUH_MUAT)->first()->TARIF : null;
            $prajoa->TARIF_HPP_BIAYA_BURUH_STRIPPING = $prajoa->KODE_HPP_BIAYA_BURUH_STRIPPING ? CostRate::where('KODE', $prajoa->KODE_HPP_BIAYA_BURUH_STRIPPING)->first()->TARIF : null;
            $prajoa->TARIF_HPP_BIAYA_BURUH_BONGKAR = $prajoa->KODE_HPP_BIAYA_BURUH_BONGKAR ? CostRate::where('KODE', $prajoa->KODE_HPP_BIAYA_BURUH_BONGKAR)->first()->TARIF : null;
            $prajoa->TARIF_HPP_BIAYA_SEAL = $prajoa->KODE_HPP_BIAYA_SEAL ? CostRate::where('KODE', $prajoa->KODE_HPP_BIAYA_SEAL)->first()->TARIF : null;
            $prajoa->TARIF_HPP_BIAYA_OPS = $prajoa->KODE_HPP_BIAYA_OPS ? CostRate::where('KODE', $prajoa->KODE_HPP_BIAYA_OPS)->first()->TARIF : null;

            $prajoa = $prajoa->toArray();

            return ApiResponse::json(true, 'Data retrieved successfully',  $prajoa);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to retrieve data', null, 500);
        }
    }



    public function indexWeb()
    {
        try {
            $prajoas = PraJoa::orderBy('NOMOR', 'asc')->get();
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
                $prajoa->RUTE_TRUCK_POL = $RUTE_TRUCK_POL ? $RUTE_TRUCK_POL->RUTE_ASAL . "-" . $RUTE_TRUCK_POL->RUTE_TUJUAN : null;

                // RUTE_TRUCK_POD
                $RUTE_TRUCK_POD = optional(TruckRoute::where('KODE', $prajoa->KODE_RUTE_TRUCK_POD)->first());
                $prajoa->RUTE_TRUCK_POD = $RUTE_TRUCK_POD ? $RUTE_TRUCK_POD->RUTE_ASAL . "-" . $RUTE_TRUCK_POD->RUTE_TUJUAN : null;
            }


            return ApiResponse::json(true, 'Data retrieved successfully',  $prajoas);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to retrieve data', null, 500);
        }
    }

    public function storeWeb(Request $request)
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

        // try {
        $validatedData = $validator->validated();

        $kodeAmbil = $this->getLocalNextId();
        $kodeAmbil = substr($kodeAmbil, 0, -2);



        $prajoa = new PraJoa();
        $prajoa->NOMOR = $kodeAmbil;
        $prajoa->KODE_CUSTOMER = $request->get('KODE_CUSTOMER');
        $prajoa->KODE_VENDOR_PELAYARAN_FORWARDING = $request->get('KODE_VENDOR_PELAYARAN_FORWARDING');
        $prajoa->KODE_POL = $request->get('KODE_POL');
        $prajoa->KODE_POD = $request->get('KODE_POD');
        $prajoa->KODE_UK_CONTAINER = $request->get('KODE_UK_CONTAINER');
        $prajoa->KODE_JENIS_CONTAINER = $request->get('KODE_JENIS_CONTAINER');
        $prajoa->KODE_JENIS_ORDER = $request->get('KODE_JENIS_ORDER');
        $prajoa->KODE_COMMODITY = $request->get('KODE_COMMODITY');
        $prajoa->KODE_SERVICE = $request->get('KODE_SERVICE');
        $prajoa->THC_POL_INCL = $request->get('THC_POL_INCL');
        $prajoa->KODE_THC_POL = $request->get('KODE_THC_POL');
        $prajoa->LOLO_POL_DALAM_LUAR = $request->get('LOLO_POL_DALAM_LUAR');
        $prajoa->LOLO_POL_INCL = $request->get('LOLO_POL_INCL');
        $prajoa->THC_POD_INCL = $request->get('THC_POD_INCL');
        $prajoa->KODE_THC_POD = $request->get('KODE_THC_POD');
        $prajoa->LOLO_POD_DALAM_LUAR = $request->get('LOLO_POD_DALAM_LUAR');
        $prajoa->LOLO_POD_INCL = $request->get('LOLO_POD_INCL');
        $prajoa->STATUS = $request->get('STATUS');
        $prajoa->REWORK_INCL = $request->get('REWORK_INCL');
        $prajoa->NOMINAL_REWORK = $request->get('NOMINAL_REWORK');
        $prajoa->KETERANGAN_REWORK = $request->get('KETERANGAN_REWORK');
        $prajoa->BURUH_MUAT_INCL = $request->get('BURUH_MUAT_INCL');
        $prajoa->KODE_HPP_BIAYA_BURUH_MUAT = $request->get('KODE_HPP_BIAYA_BURUH_MUAT');
        $prajoa->ALAT_BERAT_POL_INCL = $request->get('ALAT_BERAT_POL_INCL');
        $prajoa->NOMINAL_ALAT_BERAT_POL = $request->get('NOMINAL_ALAT_BERAT_POL');
        $prajoa->KETERANGAN_ALAT_BERAT_POL = $request->get('KETERANGAN_ALAT_BERAT_POL');
        $prajoa->BURUH_STRIPPING_INCL = $request->get('BURUH_STRIPPING_INCL');
        $prajoa->KODE_HPP_BIAYA_BURUH_STRIPPING = $request->get('KODE_HPP_BIAYA_BURUH_STRIPPING');
        $prajoa->BURUH_BONGKAR_INCL = $request->get('BURUH_BONGKAR_INCL');
        $prajoa->KODE_HPP_BIAYA_BURUH_BONGKAR = $request->get('KODE_HPP_BIAYA_BURUH_BONGKAR');
        $prajoa->ALAT_BERAT_POD_STRIPPING_INCL = $request->get('ALAT_BERAT_POD_STRIPPING_INCL');
        $prajoa->NOMINAL_ALAT_BERAT_POD_STRIPPING = $request->get('NOMINAL_ALAT_BERAT_POD_STRIPPING');
        $prajoa->KETERANGAN_ALAT_BERAT_POD_STRIPPING = $request->get('KETERANGAN_ALAT_BERAT_POD_STRIPPING');
        $prajoa->ALAT_BERAT_POD_BONGKAR_INCL = $request->get('ALAT_BERAT_POD_BONGKAR_INCL');
        $prajoa->NOMINAL_ALAT_BERAT_POD_BONGKAR = $request->get('NOMINAL_ALAT_BERAT_POD_BONGKAR');
        $prajoa->KETERANGAN_ALAT_BERAT_POD_BONGKAR = $request->get('KETERANGAN_ALAT_BERAT_POD_BONGKAR');
        $prajoa->ASURANSI_INCL = $request->get('ASURANSI_INCL');
        $prajoa->NOMINAL_TSI = $request->get('NOMINAL_TSI');
        $prajoa->PERSEN_ASURANSI = $request->get('PERSEN_ASURANSI');
        $prajoa->TRUCK_POL_INCL = $request->get('TRUCK_POL_INCL');
        $prajoa->KODE_RUTE_TRUCK_POL = $request->get('KODE_RUTE_TRUCK_POL');
        $prajoa->TRUCK_POD_INCL = $request->get('TRUCK_POD_INCL');
        $prajoa->KODE_RUTE_TRUCK_POD = $request->get('KODE_RUTE_TRUCK_POD');
        $prajoa->FEE_AGENT_POL_INCL = $request->get('FEE_AGENT_POL_INCL');
        $prajoa->NOMINAL_FEE_AGENT_POL = $request->get('NOMINAL_FEE_AGENT_POL');
        $prajoa->KETERANGAN_FEE_AGENT_POL = $request->get('KETERANGAN_FEE_AGENT_POL');
        $prajoa->FEE_AGENT_POD_INCL = $request->get('FEE_AGENT_POD_INCL');
        $prajoa->NOMINAL_FEE_AGENT_POD = $request->get('NOMINAL_FEE_AGENT_POD');
        $prajoa->KETERANGAN_FEE_AGENT_POD = $request->get('KETERANGAN_FEE_AGENT_POD');

        $prajoa->TOESLAG_INCL = $request->get('TOESLAG_INCL');
        $prajoa->NOMINAL_TOESLAG = $request->get('NOMINAL_TOESLAG');
        $prajoa->KETERANGAN_TOESLAG = $request->get('KETERANGAN_TOESLAG');
        $prajoa->SEAL_INCL = $request->get('SEAL_INCL');
        $prajoa->KODE_HPP_BIAYA_SEAL = $request->get('KODE_HPP_BIAYA_SEAL');
        $prajoa->OPS_INCL = $request->get('OPS_INCL');
        $prajoa->KODE_HPP_BIAYA_OPS = $request->get('KODE_HPP_BIAYA_OPS');
        $prajoa->KARANTINA_INCL = $request->get('KARANTINA_INCL');
        $prajoa->NOMINAL_KARANTINA = $request->get('NOMINAL_KARANTINA');
        $prajoa->KETERANGAN_KARANTINA = $request->get('KETERANGAN_KARANTINA');
        $prajoa->CASHBACK_INCL = $request->get('CASHBACK_INCL');
        $prajoa->NOMINAL_CASHBACK = $request->get('NOMINAL_CASHBACK');
        $prajoa->KETERANGAN_CASHBACK = $request->get('KETERANGAN_CASHBACK');
        $prajoa->CLAIM_INCL = $request->get('CLAIM_INCL');
        $prajoa->NOMINAL_CLAIM = $request->get('NOMINAL_CLAIM');
        $prajoa->KETERANGAN_CLAIM = $request->get('KETERANGAN_CLAIM');
        $prajoa->BIAYA_LAIN_INCL = $request->get('BIAYA_LAIN_INCL');
        // $prajoa->KODE_BIAYA_LAIN = $request->get('KODE_BIAYA_LAIN');

        $prajoa->BL_INCL = $request->get('BL_INCL');
        $prajoa->NOMINAL_BL = $request->get('NOMINAL_BL');
        $prajoa->KETERANGAN_BL = $request->get('KETERANGAN_BL');
        $prajoa->DO_INCL = $request->get('DO_INCL');
        $prajoa->NOMINAL_DO = $request->get('NOMINAL_DO');
        $prajoa->KETERANGAN_DO = $request->get('KETERANGAN_DO');
        $prajoa->APBS_INCL = $request->get('APBS_INCL');
        $prajoa->NOMINAL_APBS = $request->get('NOMINAL_APBS');
        $prajoa->KETERANGAN_APBS = $request->get('KETERANGAN_APBS');
        $prajoa->CLEANING_INCL = $request->get('CLEANING_INCL');
        $prajoa->NOMINAL_CLEANING = $request->get('NOMINAL_CLEANING');
        $prajoa->KETERANGAN_CLEANING = $request->get('KETERANGAN_CLEANING');
        $prajoa->DOC_INCL = $request->get('DOC_INCL');
        $prajoa->NOMINAL_DOC = $request->get('NOMINAL_DOC');
        $prajoa->KETERANGAN_DOC = $request->get('KETERANGAN_DOC');
        $prajoa->HARGA_JUAL = $request->get('HARGA_JUAL');

        $prajoa->save();

        $id = $prajoa->NOMOR;

        if (!$prajoa) {
            return ApiResponse::json(false, 'Failed to insert data', null, 500);
        }

        $prajoar = PraJoa::where('NOMOR', $id)->first();
        $othercost = [];
        //if reqiset get('KODE_BIAYA_LAIN') is not empty
        if ($request->get('KODE_BIAYA_LAIN')) {
            foreach ($request->get('KODE_BIAYA_LAIN') as $othercost) {
                if (!$othercost) {
                    continue;
                }
                $new_othercost = new PraJoaOtherCost();

                $new_othercost->KODE_HPP_BIAYA = $othercost;
                $new_othercost->NOMOR_PRAJOA = $id;
                $new_othercost->save();
                $othercost[] = $new_othercost;

                if (!$new_othercost) {
                    return ApiResponse::json(false, 'Failed to insert data', null, 500);
                }
            }
        }


        //convert to array
        $prajoar = $prajoar->toArray();

        //convert other costs to array
        // $new_othercost = $new_othercost->toArray();

        // //include other costs
        $prajoar['OTHER_COSTS'] = $othercost;

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




        return ApiResponse::json(true, "Data inserted successfully with KODE $id", $prajoar, 201);
        // } catch (\Exception $e) {
        //     return ApiResponse::json(false, $e, null, 500);
        // }
    }

    public function approveMarketing(Request $request, $KODE)
    {
        //get user from token
        $providedToken = $request->bearerToken();


        if (!$providedToken) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }


        try {
            //get user id from token
            $user = GetUserFromToken::getUser($providedToken);
            if (!$user) {
                return ApiResponse::json(false, 'Unauthorized', null, 401);
            }
            //get user role
            $userRole = $user->KODE_JABATAN;
            if ($userRole != 4) {
                return ApiResponse::json(false, 'Unauthorized', null, 401);
            }
            $prajoa = PraJoa::findOrFail($KODE);
            $prajoa->SUDAH_DIAPPROVE = 1;
            $prajoa->save();
            return ApiResponse::json(true, 'Pra JOA approved', $prajoa);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to approve Pra JOA', null, 500);
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

    // public function approveUser(Request $request, $KODE)
    // {
    //     //get user from token
    //     $providedToken = $request->bearerToken();


    //     if (!$providedToken) {
    //         return response()->json(['error' => 'Unauthorized'], 401);
    //     }


    //     try {
    //         //get user id from token
    //         $user = GetUserFromToken::getUser($providedToken);
    //         if (!$user) {
    //             return ApiResponse::json(false, 'Unauthorized', null, 401);
    //         }
    //         //get user role
    //         $userRole = $user->KODE_JABATAN;
    //         if ($userRole != 4) {
    //             return ApiResponse::json(false, 'Unauthorized', null, 401);
    //         }
    //         $prajoa = PraJoa::findOrFail($KODE);
    //         $prajoa->SUDAH_DIAPPROVE = 1;
    //         $prajoa->save();
    //         return ApiResponse::json(true, 'Pra JOA approved', $prajoa);
    //     } catch (\Exception $e) {
    //         return ApiResponse::json(false, 'Failed to approve Pra JOA', null, 500);
    //     }
    // }



    // public function updateOld(Request $request, $KODE)
    // {
    //     //use validator here
    //     $validator = Validator::make($request->all(), [
    //         'KODE_CUSTOMER' => 'required',
    //         'KODE_VENDOR_PELAYARAN_FORWARDING' => 'required',
    //         'KODE_POL' => 'required',
    //         'KODE_POD' => 'required',
    //         'KODE_UK_CONTAINER' => 'required',
    //         'KODE_JENIS_CONTAINER' => 'required',
    //         'KODE_JENIS_ORDER' => 'required',
    //         'KODE_COMMODITY' => 'required',
    //         'KODE_SERVICE' => 'required',
    //         'THC_POL_INCL' => 'required',
    //         'KODE_THC_POL' => '',
    //         'LOLO_POL_DALAM_LUAR' => 'required',
    //         'LOLO_POL_INCL' => 'required',
    //         'THC_POD_INCL' => 'required',
    //         'KODE_THC_POD' => '',
    //         'LOLO_POD_DALAM_LUAR' => 'required',
    //         'LOLO_POD_INCL' => 'required',
    //         'STATUS' => 'required',
    //         'REWORK_INCL' => 'required',
    //         'NOMINAL_REWORK' => '',
    //         'KETERANGAN_REWORK' => '',
    //         'BURUH_MUAT_INCL' => 'required',
    //         'KODE_HPP_BIAYA_BURUH_MUAT' => '',
    //         'ALAT_BERAT_POL_INCL' => 'required',
    //         'NOMINAL_ALAT_BERAT_POL' => '',
    //         'KETERANGAN_ALAT_BERAT_POL' => '',
    //         'BURUH_STRIPPING_INCL' => 'required',
    //         'KODE_HPP_BIAYA_BURUH_STRIPPING' => '',
    //         'BURUH_BONGKAR_INCL' => 'required',
    //         'KODE_HPP_BIAYA_BURUH_BONGKAR' => '',
    //         'ALAT_BERAT_POD_STRIPPING_INCL' => 'required',
    //         'NOMINAL_ALAT_BERAT_POD_STRIPPING' => '',
    //         'KETERANGAN_ALAT_BERAT_POD_STRIPPING' => '',
    //         'ALAT_BERAT_POD_BONGKAR_INCL' => 'required',
    //         'NOMINAL_ALAT_BERAT_POD_BONGKAR' => '',
    //         'KETERANGAN_ALAT_BERAT_POD_BONGKAR' => '',
    //         'ASURANSI_INCL' => 'required',
    //         'NOMINAL_TSI' => '',
    //         'PERSEN_ASURANSI' => '',
    //         'TRUCK_POL_INCL' => 'required',
    //         'KODE_RUTE_TRUCK_POL' => '',
    //         'TRUCK_POD_INCL' => 'required',
    //         'KODE_RUTE_TRUCK_POD' => '',
    //         'FEE_AGENT_POL_INCL' => 'required',
    //         'NOMINAL_FEE_AGENT_POL' => '',
    //         'KETERANGAN_FEE_AGENT_POL' => '',
    //         'FEE_AGENT_POD_INCL' => 'required',
    //         'NOMINAL_FEE_AGENT_POD' => '',
    //         'KETERANGAN_FEE_AGENT_POD' => '',
    //         'TOESLAG_INCL' => 'required',
    //         'NOMINAL_TOESLAG' => '',
    //         'KETERANGAN_TOESLAG' => '',
    //         'SEAL_INCL' => 'required',
    //         'KODE_HPP_BIAYA_SEAL' => '',
    //         'OPS_INCL' => 'required',
    //         'KODE_HPP_BIAYA_OPS' => '',
    //         'KARANTINA_INCL' => 'required',
    //         'NOMINAL_KARANTINA' => '',
    //         'KETERANGAN_KARANTINA' => '',
    //         'CASHBACK_INCL' => 'required',
    //         'NOMINAL_CASHBACK' => '',
    //         'KETERANGAN_CASHBACK' => '',
    //         'CLAIM_INCL' => 'required',
    //         'NOMINAL_CLAIM' => '',
    //         'KETERANGAN_CLAIM' => '',
    //         'BIAYA_LAIN_INCL' => 'required',
    //         'KODE_BIAYA_LAIN' => 'array',
    //         // 'KODE_HPP_BIAYA' => 'array',

    //         'BL_INCL' => 'required',
    //         'NOMINAL_BL' => '',
    //         'KETERANGAN_BL' => '',
    //         'DO_INCL' => 'required',
    //         'NOMINAL_DO' => '',
    //         'KETERANGAN_DO' => '',
    //         'APBS_INCL' => 'required',
    //         'NOMINAL_APBS' => '',
    //         'KETERANGAN_APBS' => '',
    //         'CLEANING_INCL' => 'required',
    //         'NOMINAL_CLEANING' => '',
    //         'KETERANGAN_CLEANING' => '',
    //         'DOC_INCL' => 'required',
    //         'NOMINAL_DOC' => '',
    //         'KETERANGAN_DOC' => '',
    //     ], []);

    //     //if validator fails
    //     if ($validator->fails()) {
    //         $errors = $validator->errors();
    //         return ApiResponse::json(false, $errors, null, 422);
    //     }

    //     // Check uniqueness for NAMA case insensitive except for current KODE
    //     // $prajoas = PraJoa::where(DB::raw('LOWER("NAMA_BIAYA")'), strtolower($request->NAMA_BIAYA))->where('NOMOR', '!=', $KODE)->first();
    //     // if ($prajoas) {
    //     //     return ApiResponse::json(false, ['NAMA_BIAYA' => ['The NAMA_BIAYA has already been taken.']], null, 422);
    //     // }
    //     // try {
    //     $validatedData = $validator->validated();


    //     $prajoa_prev = PraJoa::findOrFail($KODE);
    //     //insert new prajoa with revision number + 1
    //     // return $this->getLocalNextRevisionId($prajoa_prev->NOMOR);

    //     $prajoa_prev->NOMOR = $this->getLocalNextRevisionId($prajoa_prev->NOMOR);
    //     $prajoa_prev->KODE_CUSTOMER = $validatedData['KODE_CUSTOMER'];
    //     $prajoa_prev->KODE_VENDOR_PELAYARAN_FORWARDING = $validatedData['KODE_VENDOR_PELAYARAN_FORWARDING'];
    //     $prajoa_prev->KODE_POL = $validatedData['KODE_POL'];
    //     $prajoa_prev->KODE_POD = $validatedData['KODE_POD'];
    //     $prajoa_prev->KODE_UK_CONTAINER = $validatedData['KODE_UK_CONTAINER'];
    //     $prajoa_prev->KODE_JENIS_CONTAINER = $validatedData['KODE_JENIS_CONTAINER'];
    //     $prajoa_prev->KODE_JENIS_ORDER = $validatedData['KODE_JENIS_ORDER'];
    //     $prajoa_prev->KODE_COMMODITY = $validatedData['KODE_COMMODITY'];
    //     $prajoa_prev->KODE_SERVICE = $validatedData['KODE_SERVICE'];
    //     $prajoa_prev->THC_POL_INCL = $validatedData['THC_POL_INCL'];
    //     $prajoa_prev->KODE_THC_POL = $validatedData['KODE_THC_POL'];
    //     $prajoa_prev->LOLO_POL_DALAM_LUAR = $validatedData['LOLO_POL_DALAM_LUAR'];
    //     $prajoa_prev->LOLO_POL_INCL = $validatedData['LOLO_POL_INCL'];
    //     $prajoa_prev->THC_POD_INCL = $validatedData['THC_POD_INCL'];
    //     $prajoa_prev->KODE_THC_POD = $validatedData['KODE_THC_POD'];
    //     $prajoa_prev->LOLO_POD_DALAM_LUAR = $validatedData['LOLO_POD_DALAM_LUAR'];
    //     $prajoa_prev->LOLO_POD_INCL = $validatedData['LOLO_POD_INCL'];
    //     $prajoa_prev->STATUS = $validatedData['STATUS'];
    //     $prajoa_prev->REWORK_INCL = $validatedData['REWORK_INCL'];
    //     $prajoa_prev->NOMINAL_REWORK = $validatedData['NOMINAL_REWORK'];
    //     $prajoa_prev->KETERANGAN_REWORK = $validatedData['KETERANGAN_REWORK'];
    //     $prajoa_prev->BURUH_MUAT_INCL = $validatedData['BURUH_MUAT_INCL'];
    //     $prajoa_prev->KODE_HPP_BIAYA_BURUH_MUAT = $validatedData['KODE_HPP_BIAYA_BURUH_MUAT'];
    //     $prajoa_prev->ALAT_BERAT_POL_INCL = $validatedData['ALAT_BERAT_POL_INCL'];
    //     $prajoa_prev->NOMINAL_ALAT_BERAT_POL = $validatedData['NOMINAL_ALAT_BERAT_POL'];
    //     $prajoa_prev->KETERANGAN_ALAT_BERAT_POL = $validatedData['KETERANGAN_ALAT_BERAT_POL'];
    //     $prajoa_prev->BURUH_STRIPPING_INCL = $validatedData['BURUH_STRIPPING_INCL'];
    //     $prajoa_prev->KODE_HPP_BIAYA_BURUH_STRIPPING = $validatedData['KODE_HPP_BIAYA_BURUH_STRIPPING'];
    //     $prajoa_prev->BURUH_BONGKAR_INCL = $validatedData['BURUH_BONGKAR_INCL'];
    //     $prajoa_prev->KODE_HPP_BIAYA_BURUH_BONGKAR = $validatedData['KODE_HPP_BIAYA_BURUH_BONGKAR'];
    //     $prajoa_prev->ALAT_BERAT_POD_STRIPPING_INCL = $validatedData['ALAT_BERAT_POD_STRIPPING_INCL'];
    //     $prajoa_prev->NOMINAL_ALAT_BERAT_POD_STRIPPING = $validatedData['NOMINAL_ALAT_BERAT_POD_STRIPPING'];
    //     $prajoa_prev->KETERANGAN_ALAT_BERAT_POD_STRIPPING = $validatedData['KETERANGAN_ALAT_BERAT_POD_STRIPPING'];
    //     $prajoa_prev->ALAT_BERAT_POD_BONGKAR_INCL = $validatedData['ALAT_BERAT_POD_BONGKAR_INCL'];
    //     $prajoa_prev->NOMINAL_ALAT_BERAT_POD_BONGKAR = $validatedData['NOMINAL_ALAT_BERAT_POD_BONGKAR'];
    //     $prajoa_prev->KETERANGAN_ALAT_BERAT_POD_BONGKAR = $validatedData['KETERANGAN_ALAT_BERAT_POD_BONGKAR'];
    //     $prajoa_prev->ASURANSI_INCL = $validatedData['ASURANSI_INCL'];
    //     $prajoa_prev->NOMINAL_TSI = $validatedData['NOMINAL_TSI'];
    //     $prajoa_prev->PERSEN_ASURANSI = $validatedData['PERSEN_ASURANSI'];
    //     $prajoa_prev->TRUCK_POL_INCL = $validatedData['TRUCK_POL_INCL'];
    //     $prajoa_prev->KODE_RUTE_TRUCK_POL = $validatedData['KODE_RUTE_TRUCK_POL'];
    //     $prajoa_prev->TRUCK_POD_INCL = $validatedData['TRUCK_POD_INCL'];
    //     $prajoa_prev->KODE_RUTE_TRUCK_POD = $validatedData['KODE_RUTE_TRUCK_POD'];
    //     $prajoa_prev->FEE_AGENT_POL_INCL = $validatedData['FEE_AGENT_POL_INCL'];
    //     $prajoa_prev->NOMINAL_FEE_AGENT_POL = $validatedData['NOMINAL_FEE_AGENT_POL'];
    //     $prajoa_prev->KETERANGAN_FEE_AGENT_POL = $validatedData['KETERANGAN_FEE_AGENT_POL'];
    //     $prajoa_prev->FEE_AGENT_POD_INCL = $validatedData['FEE_AGENT_POD_INCL'];
    //     $prajoa_prev->NOMINAL_FEE_AGENT_POD = $validatedData['NOMINAL_FEE_AGENT_POD'];
    //     $prajoa_prev->KETERANGAN_FEE_AGENT_POD = $validatedData['KETERANGAN_FEE_AGENT_POD'];

    //     $prajoa_prev->TOESLAG_INCL = $validatedData['TOESLAG_INCL'];
    //     $prajoa_prev->NOMINAL_TOESLAG = $validatedData['NOMINAL_TOESLAG'];
    //     $prajoa_prev->KETERANGAN_TOESLAG = $validatedData['KETERANGAN_TOESLAG'];
    //     $prajoa_prev->SEAL_INCL = $validatedData['SEAL_INCL'];
    //     $prajoa_prev->KODE_HPP_BIAYA_SEAL = $validatedData['KODE_HPP_BIAYA_SEAL'];
    //     $prajoa_prev->OPS_INCL = $validatedData['OPS_INCL'];
    //     $prajoa_prev->KODE_HPP_BIAYA_OPS = $validatedData['KODE_HPP_BIAYA_OPS'];
    //     $prajoa_prev->KARANTINA_INCL = $validatedData['KARANTINA_INCL'];
    //     $prajoa_prev->NOMINAL_KARANTINA = $validatedData['NOMINAL_KARANTINA'];
    //     $prajoa_prev->KETERANGAN_KARANTINA = $validatedData['KETERANGAN_KARANTINA'];
    //     $prajoa_prev->CASHBACK_INCL = $validatedData['CASHBACK_INCL'];
    //     $prajoa_prev->NOMINAL_CASHBACK = $validatedData['NOMINAL_CASHBACK'];
    //     $prajoa_prev->KETERANGAN_CASHBACK = $validatedData['KETERANGAN_CASHBACK'];
    //     $prajoa_prev->CLAIM_INCL = $validatedData['CLAIM_INCL'];
    //     $prajoa_prev->NOMINAL_CLAIM = $validatedData['NOMINAL_CLAIM'];
    //     $prajoa_prev->KETERANGAN_CLAIM = $validatedData['KETERANGAN_CLAIM'];
    //     $prajoa_prev->BIAYA_LAIN_INCL = $validatedData['BIAYA_LAIN_INCL'];
    //     //$prajoa_prev->KODE_BIAYA_LAIN = $validatedData['KODE_BIAYA_LAIN'];

    //     $prajoa_prev->BL_INCL = $validatedData['BL_INCL'];
    //     $prajoa_prev->NOMINAL_BL = $validatedData['NOMINAL_BL'];
    //     $prajoa_prev->KETERANGAN_BL = $validatedData['KETERANGAN_BL'];
    //     $prajoa_prev->DO_INCL = $validatedData['DO_INCL'];
    //     $prajoa_prev->NOMINAL_DO = $validatedData['NOMINAL_DO'];
    //     $prajoa_prev->KETERANGAN_DO = $validatedData['KETERANGAN_DO'];
    //     $prajoa_prev->APBS_INCL = $validatedData['APBS_INCL'];
    //     $prajoa_prev->NOMINAL_APBS = $validatedData['NOMINAL_APBS'];
    //     $prajoa_prev->KETERANGAN_APBS = $validatedData['KETERANGAN_APBS'];
    //     $prajoa_prev->CLEANING_INCL = $validatedData['CLEANING_INCL'];
    //     $prajoa_prev->NOMINAL_CLEANING = $validatedData['NOMINAL_CLEANING'];
    //     $prajoa_prev->KETERANGAN_CLEANING = $validatedData['KETERANGAN_CLEANING'];
    //     $prajoa_prev->DOC_INCL = $validatedData['DOC_INCL'];
    //     $prajoa_prev->NOMINAL_DOC = $validatedData['NOMINAL_DOC'];
    //     $prajoa_prev->KETERANGAN_DOC = $validatedData['KETERANGAN_DOC'];

    //     $prajoa_prev->save();

    //     $id = $prajoa_prev->NOMOR;

    //     if (!$prajoa_prev) {
    //         return ApiResponse::json(false, 'Failed to insert data', null, 500);
    //     }


    //     foreach ($validatedData['KODE_BIAYA_LAIN'] as $othercost) {
    //         if (!$othercost) {
    //             continue;
    //         }
    //         $new_othercost = new PraJoaOtherCost();

    //         $new_othercost->KODE_HPP_BIAYA = $othercost;
    //         $new_othercost->NOMOR_PRAJOA = $id;
    //         $new_othercost->save();

    //         if (!$new_othercost) {
    //             return ApiResponse::json(false, 'Failed to insert data', null, 500);
    //         }
    //     }
    //     $prajoar = PraJoa::where('NOMOR', $id)->first();

    //     //convert to array
    //     $prajoar = $prajoar->toArray();

    //     //convert other costs to array
    //     $other_cost = $new_othercost->toArray();

    //     //include other costs
    //     $prajoar['OTHER_COSTS'] = $other_cost;
    //     return ApiResponse::json(true, 'Pra Joa successfully updated', $prajoar);
    //     // } catch (\Exception $e) {
    //     //     return ApiResponse::json(false, $e->getMessage(), null, 500);
    //     // }
    // }


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
        // $prajoas = PraJoa::where(DB::raw('LOWER("NAMA_BIAYA")'), strtolower($request->NAMA_BIAYA))->where('NOMOR', '!=', $KODE)->first();
        // if ($prajoas) {
        //     return ApiResponse::json(false, ['NAMA_BIAYA' => ['The NAMA_BIAYA has already been taken.']], null, 422);
        // }
        // try {
        $validatedData = $validator->validated();



        $prajoa = new PraJoa();
        $prajoa->NOMOR = $this->getLocalNextRevisionId($KODE);
        $prajoa->KODE_CUSTOMER = $request->get('KODE_CUSTOMER');
        $prajoa->KODE_VENDOR_PELAYARAN_FORWARDING = $request->get('KODE_VENDOR_PELAYARAN_FORWARDING');
        $prajoa->KODE_POL = $request->get('KODE_POL');
        $prajoa->KODE_POD = $request->get('KODE_POD');

        $prajoa->KODE_UK_CONTAINER = $request->get('KODE_UK_CONTAINER');
        $prajoa->KODE_JENIS_CONTAINER = $request->get('KODE_JENIS_CONTAINER');
        $prajoa->KODE_JENIS_ORDER = $request->get('KODE_JENIS_ORDER');
        $prajoa->KODE_COMMODITY = $request->get('KODE_COMMODITY');
        $prajoa->KODE_SERVICE = $request->get('KODE_SERVICE');
        $prajoa->THC_POL_INCL = $request->get('THC_POL_INCL');
        $prajoa->KODE_THC_POL = $request->get('KODE_THC_POL');
        $prajoa->LOLO_POL_DALAM_LUAR = $request->get('LOLO_POL_DALAM_LUAR');
        $prajoa->LOLO_POL_INCL = $request->get('LOLO_POL_INCL');
        $prajoa->THC_POD_INCL = $request->get('THC_POD_INCL');
        $prajoa->KODE_THC_POD = $request->get('KODE_THC_POD');

        $prajoa->LOLO_POD_DALAM_LUAR = $request->get('LOLO_POD_DALAM_LUAR');
        $prajoa->LOLO_POD_INCL = $request->get('LOLO_POD_INCL');
        $prajoa->STATUS = $request->get('STATUS');
        $prajoa->REWORK_INCL = $request->get('REWORK_INCL');
        $prajoa->NOMINAL_REWORK = $request->get('NOMINAL_REWORK');
        $prajoa->KETERANGAN_REWORK = $request->get('KETERANGAN_REWORK');
        $prajoa->BURUH_MUAT_INCL = $request->get('BURUH_MUAT_INCL');
        $prajoa->KODE_HPP_BIAYA_BURUH_MUAT = $request->get('KODE_HPP_BIAYA_BURUH_MUAT');
        $prajoa->ALAT_BERAT_POL_INCL = $request->get('ALAT_BERAT_POL_INCL');
        $prajoa->NOMINAL_ALAT_BERAT_POL = $request->get('NOMINAL_ALAT_BERAT_POL');
        $prajoa->KETERANGAN_ALAT_BERAT_POL = $request->get('KETERANGAN_ALAT_BERAT_POL');
        $prajoa->BURUH_STRIPPING_INCL = $request->get('BURUH_STRIPPING_INCL');
        $prajoa->KODE_HPP_BIAYA_BURUH_STRIPPING = $request->get('KODE_HPP_BIAYA_BURUH_STRIPPING');
        $prajoa->BURUH_BONGKAR_INCL = $request->get('BURUH_BONGKAR_INCL');

        $prajoa->KODE_HPP_BIAYA_BURUH_BONGKAR = $request->get('KODE_HPP_BIAYA_BURUH_BONGKAR');
        $prajoa->ALAT_BERAT_POD_STRIPPING_INCL = $request->get('ALAT_BERAT_POD_STRIPPING_INCL');
        $prajoa->NOMINAL_ALAT_BERAT_POD_STRIPPING = $request->get('NOMINAL_ALAT_BERAT_POD_STRIPPING');
        $prajoa->KETERANGAN_ALAT_BERAT_POD_STRIPPING = $request->get('KETERANGAN_ALAT_BERAT_POD_STRIPPING');
        $prajoa->ALAT_BERAT_POD_BONGKAR_INCL = $request->get('ALAT_BERAT_POD_BONGKAR_INCL');
        $prajoa->NOMINAL_ALAT_BERAT_POD_BONGKAR = $request->get('NOMINAL_ALAT_BERAT_POD_BONGKAR');
        $prajoa->KETERANGAN_ALAT_BERAT_POD_BONGKAR = $request->get('KETERANGAN_ALAT_BERAT_POD_BONGKAR');
        $prajoa->ASURANSI_INCL = $request->get('ASURANSI_INCL');
        $prajoa->NOMINAL_TSI = $request->get('NOMINAL_TSI');
        $prajoa->PERSEN_ASURANSI = $request->get('PERSEN_ASURANSI');
        $prajoa->TRUCK_POL_INCL = $request->get('TRUCK_POL_INCL');
        $prajoa->KODE_RUTE_TRUCK_POL = $request->get('KODE_RUTE_TRUCK_POL');
        $prajoa->TRUCK_POD_INCL = $request->get('TRUCK_POD_INCL');
        $prajoa->KODE_RUTE_TRUCK_POD = $request->get('KODE_RUTE_TRUCK_POD');
        $prajoa->FEE_AGENT_POL_INCL = $request->get('FEE_AGENT_POL_INCL');
        $prajoa->NOMINAL_FEE_AGENT_POL = $request->get('NOMINAL_FEE_AGENT_POL');
        $prajoa->KETERANGAN_FEE_AGENT_POL = $request->get('KETERANGAN_FEE_AGENT_POL');
        $prajoa->FEE_AGENT_POD_INCL = $request->get('FEE_AGENT_POD_INCL');
        $prajoa->NOMINAL_FEE_AGENT_POD = $request->get('NOMINAL_FEE_AGENT_POD');
        $prajoa->KETERANGAN_FEE_AGENT_POD = $request->get('KETERANGAN_FEE_AGENT_POD');

        $prajoa->TOESLAG_INCL = $request->get('TOESLAG_INCL');
        $prajoa->NOMINAL_TOESLAG = $request->get('NOMINAL_TOESLAG');
        $prajoa->KETERANGAN_TOESLAG = $request->get('KETERANGAN_TOESLAG');
        $prajoa->SEAL_INCL = $request->get('SEAL_INCL');
        $prajoa->KODE_HPP_BIAYA_SEAL = $request->get('KODE_HPP_BIAYA_SEAL');
        $prajoa->OPS_INCL = $request->get('OPS_INCL');
        $prajoa->KODE_HPP_BIAYA_OPS = $request->get('KODE_HPP_BIAYA_OPS');
        $prajoa->KARANTINA_INCL = $request->get('KARANTINA_INCL');
        $prajoa->NOMINAL_KARANTINA = $request->get('NOMINAL_KARANTINA');
        $prajoa->KETERANGAN_KARANTINA = $request->get('KETERANGAN_KARANTINA');
        $prajoa->CASHBACK_INCL = $request->get('CASHBACK_INCL');
        $prajoa->NOMINAL_CASHBACK = $request->get('NOMINAL_CASHBACK');
        $prajoa->KETERANGAN_CASHBACK = $request->get('KETERANGAN_CASHBACK');
        $prajoa->CLAIM_INCL = $request->get('CLAIM_INCL');
        $prajoa->NOMINAL_CLAIM = $request->get('NOMINAL_CLAIM');
        $prajoa->KETERANGAN_CLAIM = $request->get('KETERANGAN_CLAIM');
        $prajoa->BIAYA_LAIN_INCL = $request->get('BIAYA_LAIN_INCL');

        // $prajoa->KODE_BIAYA_LAIN = $request->get('KODE_BIAYA_LAIN');

        $prajoa->BL_INCL = $request->get('BL_INCL');
        $prajoa->NOMINAL_BL = $request->get('NOMINAL_BL');
        $prajoa->KETERANGAN_BL = $request->get('KETERANGAN_BL');
        $prajoa->DO_INCL = $request->get('DO_INCL');
        $prajoa->NOMINAL_DO = $request->get('NOMINAL_DO');
        $prajoa->KETERANGAN_DO = $request->get('KETERANGAN_DO');
        $prajoa->APBS_INCL = $request->get('APBS_INCL');
        $prajoa->NOMINAL_APBS = $request->get('NOMINAL_APBS');
        $prajoa->KETERANGAN_APBS = $request->get('KETERANGAN_APBS');
        $prajoa->CLEANING_INCL = $request->get('CLEANING_INCL');
        $prajoa->NOMINAL_CLEANING = $request->get('NOMINAL_CLEANING');
        $prajoa->KETERANGAN_CLEANING = $request->get('KETERANGAN_CLEANING');
        $prajoa->DOC_INCL = $request->get('DOC_INCL');
        $prajoa->NOMINAL_DOC = $request->get('NOMINAL_DOC');
        $prajoa->KETERANGAN_DOC = $request->get('KETERANGAN_DOC');
        $prajoa->HARGA_JUAL = $request->get('HARGA_JUAL');


        $prajoa->save();


        $id = $prajoa->NOMOR;



        if (!$prajoa) {
            return ApiResponse::json(false, 'Failed to insert data', null, 500);
        }


        $prajoar = PraJoa::where('NOMOR', $id)->first();
        $otherCostsData = [];
        if ($request->get('KODE_BIAYA_LAIN')) {
            foreach ($request->get('KODE_BIAYA_LAIN') as $othercost) {
                if (!$othercost) {
                    continue;
                }
                $new_othercost = new PraJoaOtherCost();

                $new_othercost->KODE_HPP_BIAYA = $othercost;
                $new_othercost->NOMOR_PRAJOA = $id;
                $new_othercost->save();
                $otherCostsData[] = $new_othercost;


                if (!$new_othercost) {
                    return ApiResponse::json(false, 'Failed to insert data', null, 500);
                }
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

    public function destroy($KODE)
    {
        try {
            $city = PraJoa::findOrFail($KODE);
            $city->delete();
            return ApiResponse::json(true, 'Pra Joa successfully deleted', ['NOMOR' => $KODE]);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to delete prajoa', null, 500);
        }
    }

    public function trash()
    {
        $prajoas = PraJoa::onlyTrashed()->get();

        //convert to array
        $prajoas = $prajoas->toArray();


        return ApiResponse::json(true, 'Trash bin fetched', $prajoas);
    }

    public function restore($id)
    {
        $restored = PraJoa::onlyTrashed()->findOrFail($id);
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
        // Get the maximum PJA.XXXXXX ID from the database

        //get today's date
        $currentDay = date('d');
        $currentMonth = date('m');
        $currentYear = date('y');

        $maxId = DB::table('pra_joas')
            ->where('NOMOR', 'LIKE', 'PJA.' . $currentDay . $currentMonth . $currentYear . '%')
            ->orderByRaw('CAST(SUBSTRING("NOMOR" FROM \'\\-([0-9]+)\\-\') AS INTEGER) DESC, "NOMOR" DESC')
            ->value('NOMOR');



        // $maxId = DB::table('pra_joas')
        //     ->where('NOMOR', 'LIKE', 'PJA.%')
        //     ->orderByRaw('CAST(SUBSTRING("NOMOR" FROM \'\\-([0-9]+)\\-\') AS INTEGER) DESC, "NOMOR" DESC')
        //     ->value('NOMOR');







        // $maxid = DB::table("pra_joas")
        //     ->select("max (cast(substring('nomor' from '-(\d+)-') as integer)) as max_id")
        //     ->where("NOMOR", "like", 'PJA.%')
        //     ->get();



        if ($maxId) {
            $lastSequence = null;
            // Find the position of the first '-' character
            $firstDashPos = strpos($maxId, '-');
            //get max id index firstDashPos + 1

            // Find the position of the next '-' character, starting the search from the position after the first '-'
            $nextDashPos = strpos($maxId, '-', $firstDashPos + 1);
            if ($nextDashPos === false) {
                // If no next '-' is found, the ID does not have a revision number
                // substring from firstdashpos + 1 to end
                $substring = substr($maxId, $firstDashPos + 1);
                $lastSequence = (int)$substring;
            } else {
                // Extract the substring between the first '-' and the next '-' (excluding the first '-')
                $substring = substr($maxId, $firstDashPos + 1, $nextDashPos - $firstDashPos - 1);

                // Convert the substring to an integer
                $lastSequence = (int)$substring;
            }



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

            // Create the next ID by concatenating 'PJA' with the current day, month, and year, sequence, and revision
            $nextId = 'PJA.' . $currentDay . $currentMonth . $currentYear . '-' . $nextSequence . '-' . $lastRevision;
        } else {
            // If no existing IDs found, start with 1 for sequence and revision
            $currentDay = date('d');
            $currentMonth = date('m');
            $currentYear = date('y');
            $nextId = 'PJA.' . $currentDay . $currentMonth . $currentYear . '-1-1';
        }

        return $nextId;
    }
    // public function getLocalNextRevisionId($idNow)
    // {
    //     if ($idNow) {
    //         // Extract the last revision number and sub-revision number from the ID
    //         $parts = explode('-', $idNow);

    //         // Count the number of elements in the array
    //         $numParts = count($parts);

    //         if ($numParts == 3) {
    //             $kode = $parts[2] + 1;
    //             return $parts[0] . "-" . $parts[1] . "-" . $kode;
    //         } else {
    //             return $parts[0] . "-" . $parts[1] . "-1";
    //         }
    //     } else {
    //         // If no existing revision ID found, start with '1' for the revision number
    //         $nextId = 'PJA.' . date('dmY') . '-1';
    //         return $nextId;
    //     }
    // }

    public function getLocalNextRevisionId($idNow)
    {
        if ($idNow) {
            // Extract the base ID and sequence from the provided ID
            $parts = explode('-', $idNow);
            $baseId = $parts[0];
            $sequence = $parts[1];



            // Count the number of IDs with the same base ID and sequence in the database
            $count = PraJoa::withTrashed()->where('NOMOR', 'LIKE', "$baseId-$sequence-%")->count();

            // Calculate the next revision number
            $nextRevision = $count + 1;

            // Construct the next ID with the same base ID, sequence, and the incremented revision number
            return "$baseId-$sequence-$nextRevision";
        } else {
            // If no existing revision ID found, start with '1' for the revision number
            $nextId = 'PJA.' . date('dmY') . '-1';
            return $nextId;
        }
    }
}
