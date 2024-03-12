<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\City;
use App\Models\Commodity;
use App\Models\Cost;
use App\Models\Size;
use App\Models\CostRate;
use App\Models\Customer;
use App\Models\Harbor;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CostRateController extends Controller
{
    //search based on a few KODE
    public function findOceanFreight(Request $request)
    {
        try {
            $currentDate = now(); // Get the current date and time

            $costrate = CostRate::where('KODE_BIAYA', 'NB.1')
                ->where('KODE_VENDOR', $request->KODE_VENDOR)
                ->where('KODE_PELABUHAN_ASAL', $request->KODE_POL)
                ->where('KODE_PELABUHAN_TUJUAN', $request->KODE_POD)
                ->where('KODE_CUSTOMER', $request->KODE_CUSTOMER)
                ->where('KODE_COMMODITY', $request->KODE_COMMODITY)
                ->where('UK_KONTAINER', $request->KODE_UK_CONTAINER)
                ->whereDate('TGL_BERLAKU', '<=', $currentDate) // Filter by TGL_BERLAKU >= current date
                ->orderBy('TGL_BERLAKU', 'desc') // Order by BERLAKU in descending order
                ->first();

            if (!$costrate) {
                return ApiResponse::json(false, 'Data not found', null, 404);
            }

            return ApiResponse::json(true, 'Data retrieved successfully', $costrate);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to retrieve data', null, 500);
        }
    }

    //findFreightSurcharge
    public function findFreightSurcharge(Request $request)
    {
        try {
            $currentDate = now(); // Get the current date and time

            $costrate = CostRate::where('KODE_BIAYA', 'NB.4')
                ->where('KODE_VENDOR', $request->KODE_VENDOR)
                ->where('KODE_PELABUHAN_ASAL', $request->KODE_POL)
                ->where('KODE_PELABUHAN_TUJUAN', $request->KODE_POD)
                ->where('UK_KONTAINER', $request->KODE_UK_CONTAINER)
                ->whereDate('TGL_BERLAKU', '<=', $currentDate) // Filter by TGL_BERLAKU >= current date
                ->orderBy('TGL_BERLAKU', 'desc') // Order by BERLAKU in descending order
                ->first();

            if (!$costrate) {
                return ApiResponse::json(false, 'Data not found', null, 404);
            }

            return ApiResponse::json(true, 'Data retrieved successfully', $costrate);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to retrieve data', null, 500);
        }
    }

    public function dropdown()
    {
        //get all costs
        $costs = Cost::all();
        $costs = $costs->toArray();
        //get all sizess
        $sizes = Size::all();
        $sizes = $sizes->toArray();
        //get all vendor that's JV.1 or JV6
        $vendors = Vendor::where('KODE_JENIS_VENDOR', 'JV.1')->orWhere('KODE_JENIS_VENDOR', 'JV.6')->get();
        $vendors = $vendors->toArray();
        //get all commodities
        $commodities = Commodity::all();
        $commodities = $commodities->toArray();
        //get all harbors
        $harbors = Harbor::all();
        $harbors = $harbors->toArray();
        //get all customers
        $customers = Customer::all();
        $customers = $customers->toArray();


        //return all data
        return ApiResponse::json(true, null, [
            'vendors' => $vendors,
            'harbors' => $harbors,
            'sizes' => $sizes,
            'commodities' => $commodities,
            'costs' => $costs,
            'customers' => $customers,
        ]);
    }


    public function dataTableJson(Request $request)
    {
        // try {
        $order = $request->input('order')[0];
        $columnIndex = (int) $order['column'];
        $sortDirection = $order['dir'];

        $columns = $request->input('columns');
        $columnToSort = $columns[$columnIndex]['data'];

        $query = CostRate::query();

        $hasSearch = $request->has('search') && !empty($request->input('search')['value']);
        $dateColumns = ["TGL_BERLAKU"]; // Add your date column names here

        // Apply search filter if applicable
        if ($hasSearch) {
            $searchValue = strtoupper($request->input('search')['value']);
            $attributes = CostRate::first()->getAttributes(); // Get all attribute names
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

                'cost' => [
                    'table' => 'costs',
                    'column' => 'NAMA_BIAYA',
                ],
                'vendor' => [
                    'table' => 'cost_types',
                    'column' => 'NAMA',
                ],

                'harborOrigin' => [
                    'table' => 'harbors',
                    'column' => 'NAMA_PELABUHAN',
                ],
                'harborDestination' => [
                    'table' => 'harbors',
                    'column' => 'NAMA_PELABUHAN',
                ],
                'commodity' => [
                    'table' => 'commodities',
                    'column' => 'NAMA',
                ],
                'size' => [
                    'table' => 'sizes',
                    'column' => 'NAMA',
                ],
            ];

            foreach ($relatedColumns as $relation => $relatedColumn) {
                $query->orWhereHas($relation, function ($q) use ($searchValue, $relatedColumn) {
                    $q->where($relatedColumn['column'], 'LIKE', "%$searchValue%");
                });
            }
        }

        $query->orderByRaw('CAST(SUBSTRING("KODE" FROM \'\\.([0-9]+)$\') AS INTEGER) ASC, "KODE" ASC');

        $allData = $query->get();



        if (!empty($columnToSort)) {
            foreach ($allData as $costrate) {
                $biaya = Cost::where('KODE', $costrate->KODE_BIAYA)->first();
                $costrate->NAMA_BIAYA = $biaya->NAMA_BIAYA;
                $UK_KONTAINERs = Size::where('KODE', $costrate->UK_KONTAINER)->first();
                $costrate->NAMA_UK_KONTAINER = $UK_KONTAINERs ? $UK_KONTAINERs->NAMA : '';

                $COMMODITY = Commodity::where('KODE', $costrate->KODE_COMMODITY)->first();
                $costrate->NAMA_COMMODITY = $COMMODITY ? $COMMODITY->NAMA : '';

                $kode_pelabuhan_asal = Harbor::where('KODE', $costrate->KODE_PELABUHAN_ASAL)->first();
                $costrate->NAMA_PELABUHAN_ASAL = $kode_pelabuhan_asal ? $kode_pelabuhan_asal->NAMA_PELABUHAN : '';

                $kode_pelabuhan_tujuan = Harbor::where('KODE', $costrate->KODE_PELABUHAN_TUJUAN)->first();
                $costrate->NAMA_PELABUHAN_TUJUAN = $kode_pelabuhan_tujuan ? $kode_pelabuhan_tujuan->NAMA_PELABUHAN : '';


                $kode_vendor = Vendor::where('KODE', $costrate->KODE_VENDOR)->first();
                $costrate->NAMA_VENDOR = $kode_vendor->NAMA;

                $kode_customer = Customer::where('KODE', $costrate->KODE_CUSTOMER)->first();
                $costrate->NAMA_CUSTOMER = $kode_customer->NAMA;
            }
            $sortBoolean = $sortDirection === 'asc' ? false : true;
            //check if columnToSort is KODE
            if ($columnIndex === 1) {


                //include the sort boolean
                $allData = $allData->sortBy(function ($data) {
                    return (int) substr($data->KODE, strpos($data->KODE, '.') + 1);
                }, SORT_REGULAR, $sortBoolean);
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
        $costrates = $allData->slice($skip)->take($limit);



        // ... rest of your code to enrich the data ...
        if (empty($columnToSort)) {
            foreach ($costrates as $costrate) {
                $biaya = Cost::where('KODE', $costrate->KODE_BIAYA)->first();
                $costrate->NAMA_BIAYA = $biaya->NAMA_BIAYA;
                $UK_KONTAINERs = Size::where('KODE', $costrate->UK_KONTAINER)->first();
                $costrate->NAMA_UK_KONTAINER = $UK_KONTAINERs ? $UK_KONTAINERs->NAMA : '';

                $COMMODITY = Commodity::where('KODE', $costrate->KODE_COMMODITY)->first();
                $costrate->NAMA_COMMODITY = $COMMODITY ? $COMMODITY->NAMA : '';

                $kode_pelabuhan_asal = Harbor::where('KODE', $costrate->KODE_PELABUHAN_ASAL)->first();
                $costrate->NAMA_PELABUHAN_ASAL = $kode_pelabuhan_asal ? $kode_pelabuhan_asal->NAMA_PELABUHAN : '';

                $kode_pelabuhan_tujuan = Harbor::where('KODE', $costrate->KODE_PELABUHAN_TUJUAN)->first();
                $costrate->NAMA_PELABUHAN_TUJUAN = $kode_pelabuhan_tujuan ? $kode_pelabuhan_tujuan->NAMA_PELABUHAN : '';


                $kode_vendor = Vendor::where('KODE', $costrate->KODE_VENDOR)->first();
                $costrate->NAMA_VENDOR = $kode_vendor->NAMA;

                $kode_customer = Customer::where('KODE', $costrate->KODE_CUSTOMER)->first();
                $costrate->NAMA_CUSTOMER = $kode_customer->NAMA;
            }
        }



        // Prepare the response data structure expected by the frontend

        $response = [
            'draw' => $request->input('draw'),
            'recordsTotal' => Cost::count(),
            'recordsFiltered' => $total_data_after_search,
            'data' => $costrates->values()->toArray(),
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
            2 => ['cost', 'NAMA_BIAYA'],
            3 => ['vendor', 'NAMA'],
            4 => ['harborOrigin', 'NAMA_PELABUHAN'],
            5 => ['harborDestination', 'NAMA_PELABUHAN'],
            6 => ['commodity', 'NAMA'],
            7 => ['size', 'NAMA'],
        ];
        try {
            $order = $request->input('order')[0];
            $columnIndex = (int) $order['column'];
            $sortDirection = $order['dir'];

            $columns = $request->input('columns');
            $columnToSort = $columns[$columnIndex]['data'];

            $query = CostRate::query();
            $dateColumns = ["TGL_BERLAKU"]; // Add your date column names here

            //check for each column hasSearch columns[x][search][value]
            $columns = $request->input('columns');
            $hasSearch = false;
            foreach ($columns as $index => $column) {
                if (isset($column['search']['value']) && !empty($column['search']['value'])) {
                    $hasSearch = true;
                    break;
                }
            }


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

            $query->orderByRaw('CAST(SUBSTRING("KODE" FROM \'\\.([0-9]+)$\') AS INTEGER) ASC, "KODE" ASC');

            $allData = $query->get();



            if (!empty($columnToSort)) {
                foreach ($allData as $costrate) {
                    $biaya = Cost::where('KODE', $costrate->KODE_BIAYA)->first();
                    $costrate->NAMA_BIAYA = $biaya->NAMA_BIAYA;
                    $UK_KONTAINERs = Size::where('KODE', $costrate->UK_KONTAINER)->first();
                    $costrate->NAMA_UK_KONTAINER = $UK_KONTAINERs ? $UK_KONTAINERs->NAMA : '';

                    $COMMODITY = Commodity::where('KODE', $costrate->KODE_COMMODITY)->first();
                    $costrate->NAMA_COMMODITY = $COMMODITY ? $COMMODITY->NAMA : '';

                    $kode_pelabuhan_asal = Harbor::where('KODE', $costrate->KODE_PELABUHAN_ASAL)->first();
                    $costrate->NAMA_PELABUHAN_ASAL = $kode_pelabuhan_asal ? $kode_pelabuhan_asal->NAMA_PELABUHAN : '';

                    $kode_pelabuhan_tujuan = Harbor::where('KODE', $costrate->KODE_PELABUHAN_TUJUAN)->first();
                    $costrate->NAMA_PELABUHAN_TUJUAN = $kode_pelabuhan_tujuan ? $kode_pelabuhan_tujuan->NAMA_PELABUHAN : '';


                    $kode_vendor = Vendor::where('KODE', $costrate->KODE_VENDOR)->first();
                    $costrate->NAMA_VENDOR = $kode_vendor->NAMA;

                    $kode_customer = Customer::where('KODE', $costrate->KODE_CUSTOMER)->first();
                    $costrate->NAMA_CUSTOMER = $kode_customer->NAMA;
                }
                $sortBoolean = $sortDirection === 'asc' ? false : true;
                //check if columnToSort is KODE
                if ($columnIndex === 1) {


                    //include the sort boolean
                    $allData = $allData->sortBy(function ($data) {
                        return (int) substr($data->KODE, strpos($data->KODE, '.') + 1);
                    }, SORT_REGULAR, $sortBoolean);
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

            $costrates = $allData->slice($skip)->take($limit);

            if (empty($columnToSort)) {
                foreach ($costrates as $costrate) {
                    $biaya = Cost::where('KODE', $costrate->KODE_BIAYA)->first();
                    $costrate->NAMA_BIAYA = $biaya->NAMA_BIAYA;
                    $UK_KONTAINERs = Size::where('KODE', $costrate->UK_KONTAINER)->first();
                    $costrate->NAMA_UK_KONTAINER = $UK_KONTAINERs ? $UK_KONTAINERs->NAMA : '';

                    $COMMODITY = Commodity::where('KODE', $costrate->KODE_COMMODITY)->first();
                    $costrate->NAMA_COMMODITY = $COMMODITY ? $COMMODITY->NAMA : '';

                    $kode_pelabuhan_asal = Harbor::where('KODE', $costrate->KODE_PELABUHAN_ASAL)->first();
                    $costrate->NAMA_PELABUHAN_ASAL = $kode_pelabuhan_asal ? $kode_pelabuhan_asal->NAMA_PELABUHAN : '';

                    $kode_pelabuhan_tujuan = Harbor::where('KODE', $costrate->KODE_PELABUHAN_TUJUAN)->first();
                    $costrate->NAMA_PELABUHAN_TUJUAN = $kode_pelabuhan_tujuan ? $kode_pelabuhan_tujuan->NAMA_PELABUHAN : '';


                    $kode_vendor = Vendor::where('KODE', $costrate->KODE_VENDOR)->first();
                    $costrate->NAMA_VENDOR = $kode_vendor->NAMA;

                    $kode_customer = Customer::where('KODE', $costrate->KODE_CUSTOMER)->first();
                    $costrate->NAMA_CUSTOMER = $kode_customer->NAMA;
                }
            }


            // ... rest of your code to enrich the data ...




            $response = [
                'draw' => $request->input('draw'),
                'recordsTotal' => Cost::count(),
                'recordsFiltered' => $total_data_after_search,
                'data' => $costrates->values()->toArray(),
            ];

            return ApiResponse::json(true, 'Data retrieved successfully', $response);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }
    public function findByKode($KODE)
    {
        try {
            $User = CostRate::where('KODE', $KODE)->first();
            if (!$User) {
                return ApiResponse::json(false, 'Data not found', null, 404);
            }
            //convert to array
            $User = $User->toArray();
            return ApiResponse::json(true, 'Data retrieved successfully',  $User);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to retrieve data', null, 500);
        }
    }

    public function getKota()
    {
        $kota = City::all();
        return response()->json([
            'kota' => $kota
        ]);
    }

    public function indexWeb()
    {
        try {
            $costrates = CostRate::orderBy('KODE', 'asc')->get();

            foreach ($costrates as $costrate) {
                $biaya = Cost::where('KODE', $costrate->KODE_BIAYA)->first();
                $costrate->NAMA_BIAYA = $biaya->NAMA_BIAYA;
                $UK_KONTAINERs = Size::where('KODE', $costrate->UK_KONTAINER)->first();
                $costrate->NAMA_UK_KONTAINER = $UK_KONTAINERs ? $UK_KONTAINERs->NAMA : '';

                $COMMODITY = Commodity::where('KODE', $costrate->KODE_COMMODITY)->first();
                $costrate->NAMA_COMMODITY = $COMMODITY ? $COMMODITY->NAMA : '';

                $kode_pelabuhan_asal = Harbor::where('KODE', $costrate->KODE_PELABUHAN_ASAL)->first();
                $costrate->NAMA_PELABUHAN_ASAL = $kode_pelabuhan_asal ? $kode_pelabuhan_asal->NAMA_PELABUHAN : '';

                $kode_pelabuhan_tujuan = Harbor::where('KODE', $costrate->KODE_PELABUHAN_TUJUAN)->first();
                $costrate->NAMA_PELABUHAN_TUJUAN = $kode_pelabuhan_tujuan ? $kode_pelabuhan_tujuan->NAMA_PELABUHAN : '';


                $kode_vendor = Vendor::where('KODE', $costrate->KODE_VENDOR)->first();
                $costrate->NAMA_VENDOR = $kode_vendor->NAMA;

                $kode_customer = Customer::where('KODE', $costrate->KODE_CUSTOMER)->first();
                $costrate->NAMA_CUSTOMER = $kode_customer->NAMA;
            }
            return ApiResponse::json(true, 'Data retrieved successfully',  $costrates);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to retrieve data', null, 500);
        }
    }

    public function storeWeb(Request $request)
    {
        //use validator here
        $validator = Validator::make($request->all(), [
            'KODE_BIAYA' => 'required',
            'KODE_VENDOR' => 'required',
            'KODE_PELABUHAN_ASAL' => '',
            'KODE_PELABUHAN_TUJUAN' => '',
            'KODE_COMMODITY' => '',
            'UK_KONTAINER' => '',
            'TARIF' => 'required',
            'TGL_BERLAKU' => 'required|date',
            'KETERANGAN' => 'required',
            'KODE_CUSTOMER' => 'required',
        ], [
            'KODE_BIAYA.required' => 'The KODE_BIAYA field is required.',
            'KODE_BIAYA.numeric' => 'The KODE_BIAYA must be numeric.',
            'KODE_VENDOR.required' => 'The KODE_VENDOR field is required.',
            'KODE_VENDOR.numeric' => 'The KODE_VENDOR must be numeric.',
            'KODE_PELABUHAN_ASAL.required' => 'The KODE_PELABUHAN_ASAL field is required.',
            'KODE_PELABUHAN_ASAL.numeric' => 'The KODE_PELABUHAN_ASAL must be numeric.',
            'KODE_PELABUHAN_TUJUAN.required' => 'The KODE_PELABUHAN_TUJUAN field is required.',
            'KODE_PELABUHAN_TUJUAN.numeric' => 'The KODE_PELABUHAN_TUJUAN must be numeric.',
            'KODE_COMMODITY.required' => 'The KODE_COMMODITY field is required.',
            'KODE_COMMODITY.numeric' => 'The KODE_COMMODITY must be numeric.',
            'UK_KONTAINER.required' => 'The UK_KONTAINER field is required.',
            'UK_KONTAINER.numeric' => 'The UK_KONTAINER must be numeric.',
            'TGL_BERLAKU.required' => 'The TGL_BERLAKU field is required.',
            'TGL_BERLAKU.date' => 'The TGL_BERLAKU must be date.',
            'TARIF.required' => 'The TARIF field is required.',
            'KETERANGAN.required' => 'The KETERANGAN field is required.',
        ]);

        //if validator fails
        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }


        // try {
        $validatedData = $validator->validated();


        $new_truck_price = new CostRate();
        $new_truck_price->KODE = $this->getLocalNextId();
        $new_truck_price->KODE_BIAYA = $validatedData['KODE_BIAYA'];
        $new_truck_price->KODE_VENDOR = $validatedData['KODE_VENDOR'];
        $new_truck_price->KODE_PELABUHAN_ASAL = $validatedData['KODE_PELABUHAN_ASAL'];
        $new_truck_price->KODE_PELABUHAN_TUJUAN = $validatedData['KODE_PELABUHAN_TUJUAN'];
        $new_truck_price->KODE_COMMODITY = $validatedData['KODE_COMMODITY'];
        $new_truck_price->UK_KONTAINER = $validatedData['UK_KONTAINER'];
        $new_truck_price->TARIF = $validatedData['TARIF'];
        $new_truck_price->TGL_BERLAKU = $validatedData['TGL_BERLAKU'];
        $new_truck_price->KETERANGAN = $validatedData['KETERANGAN'];
        $new_truck_price->KODE_CUSTOMER = $validatedData['KODE_CUSTOMER'];
        $new_truck_price->save();
        if (!$new_truck_price) {
            return ApiResponse::json(false, 'Failed to insert data', null, 500);
        }
        $id = $new_truck_price->KODE;

        $costratesr = CostRate::where('KODE', $id)->first();

        $namaPelabuhanAsal = $costratesr->KODE_PELABUHAN_ASAL !== null
            ? Harbor::where('KODE', $costratesr->KODE_PELABUHAN_ASAL)->first()->NAMA_PELABUHAN
            : '';

        $namaPelabuhanTujuan = $costratesr->KODE_PELABUHAN_TUJUAN !== null
            ? Harbor::where('KODE', $costratesr->KODE_PELABUHAN_TUJUAN)->first()->NAMA_PELABUHAN
            : '';

        $namaCommodity = $costratesr->KODE_COMMODITY !== null
            ? Commodity::where('KODE', $costratesr->KODE_COMMODITY)->first()->NAMA
            : '';

        $namaUkKontainer = $costratesr->UK_KONTAINER !== null
            ? Size::where('KODE', $costratesr->UK_KONTAINER)->first()->NAMA
            : '';

        $resp_tarifbiayar = array(
            'KODE' => $costratesr->KODE,
            'NAMA_BIAYA' => Cost::where('KODE', $costratesr->KODE_BIAYA)->first()->NAMA_BIAYA,
            'NAMA_VENDOR' => Vendor::where('KODE', $costratesr->KODE_VENDOR)->first()->NAMA,
            'NAMA_PELABUHAN_ASAL' => $namaPelabuhanAsal,
            'NAMA_PELABUHAN_TUJUAN' => $namaPelabuhanTujuan,
            'NAMA_COMMODITY' => $namaCommodity,
            'NAMA_UK_KONTAINER' => $namaUkKontainer,
            'TARIF' => $costratesr->TARIF,
            'TGL_BERLAKU' => $costratesr->TGL_BERLAKU,
            'KETERANGAN' => $costratesr->KETERANGAN,
            'NAMA_CUSTOMER' => Customer::where('KODE', $costratesr->KODE_CUSTOMER)->first()->NAMA,
        );



        return ApiResponse::json(true, "Data inserted successfully with KODE $costratesr->KODE", $resp_tarifbiayar, 201);
        // } catch (\Exception $e) {
        //     return ApiResponse::json(false, $e, null, 500);
        // }
    }

    public function update(Request $request, $KODE)
    {
        //use validator here
        $validator = Validator::make($request->all(), [

            'KODE_BIAYA' => 'required',
            'KODE_VENDOR' => 'required',
            'KODE_PELABUHAN_ASAL' => '',
            'KODE_PELABUHAN_TUJUAN' => '',
            'KODE_COMMODITY' => '',
            'UK_KONTAINER' => '',
            'TARIF' => 'required',
            'TGL_BERLAKU' => 'required|date',
            'KETERANGAN' => 'required',
            'KODE_CUSTOMER' => 'required',
        ], [
            'KODE_BIAYA.required' => 'The KODE_BIAYA field is required.',
            'KODE_BIAYA.numeric' => 'The KODE_BIAYA must be numeric.',
            'KODE_VENDOR.required' => 'The KODE_VENDOR field is required.',
            'KODE_VENDOR.numeric' => 'The KODE_VENDOR must be numeric.',
            'KODE_PELABUHAN_ASAL.required' => 'The KODE_PELABUHAN_ASAL field is required.',
            'KODE_PELABUHAN_ASAL.numeric' => 'The KODE_PELABUHAN_ASAL must be numeric.',
            'KODE_PELABUHAN_TUJUAN.required' => 'The KODE_PELABUHAN_TUJUAN field is required.',
            'KODE_PELABUHAN_TUJUAN.numeric' => 'The KODE_PELABUHAN_TUJUAN must be numeric.',
            'KODE_COMMODITY.required' => 'The KODE_COMMODITY field is required.',
            'KODE_COMMODITY.numeric' => 'The KODE_COMMODITY must be numeric.',
            'UK_KONTAINER.required' => 'The UK_KONTAINER field is required.',
            'UK_KONTAINER.numeric' => 'The UK_KONTAINER must be numeric.',
            'TGL_BERLAKU.required' => 'The TGL_BERLAKU field is required.',
            'TGL_BERLAKU.date' => 'The TGL_BERLAKU must be date.',
            'TARIF.required' => 'The TARIF field is required.',
            'KETERANGAN.required' => 'The KETERANGAN field is required.',
        ]);

        //if validator fails
        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }

        // Check uniqueness for NAMA case insensitive except for current KODE
        // $costrates = CostRate::where(DB::raw('LOWER("NAMA_BIAYA")'), strtolower($request->NAMA_BIAYA))->where('KODE', '!=', $KODE)->first();
        // if ($costrates) {
        //     return ApiResponse::json(false, ['NAMA_BIAYA' => ['The NAMA_BIAYA has already been taken.']], null, 422);
        // }
        try {
            $validatedData = $validator->validated();



            $costrates = CostRate::findOrFail($KODE);
            $costrates->KODE_BIAYA = $request->get('KODE_BIAYA');
            $costrates->KODE_VENDOR = $request->get('KODE_VENDOR');
            $costrates->KODE_PELABUHAN_ASAL = $request->get('KODE_PELABUHAN_ASAL');
            $costrates->KODE_PELABUHAN_TUJUAN = $request->get('KODE_PELABUHAN_TUJUAN');
            $costrates->KODE_COMMODITY = $request->get('KODE_COMMODITY');
            $costrates->UK_KONTAINER = $request->get('UK_KONTAINER');
            $costrates->TARIF = $request->get('TARIF');
            $costrates->TGL_BERLAKU = $request->get('TGL_BERLAKU');
            $costrates->KETERANGAN = $request->get('KETERANGAN');
            $costrates->KODE_CUSTOMER = $request->get('KODE_CUSTOMER');
            $costrates->save();
            if (!$costrates) {
                return ApiResponse::json(false, 'Failed to update data', null, 500);
            }

            $costrates = CostRate::findOrFail($KODE);

            $namaPelabuhanAsal = $costrates->KODE_PELABUHAN_ASAL !== null
                ? Harbor::where('KODE', $costrates->KODE_PELABUHAN_ASAL)->first()->NAMA_PELABUHAN
                : '';

            $namaPelabuhanTujuan = $costrates->KODE_PELABUHAN_TUJUAN !== null
                ? Harbor::where('KODE', $costrates->KODE_PELABUHAN_TUJUAN)->first()->NAMA_PELABUHAN
                : '';

            $namaCommodity = $costrates->KODE_COMMODITY !== null
                ? Commodity::where('KODE', $costrates->KODE_COMMODITY)->first()->NAMA
                : '';

            $namaUkKontainer = $costrates->UK_KONTAINER !== null
                ? Size::where('KODE', $costrates->UK_KONTAINER)->first()->NAMA
                : '';

            $resp_tarifbiaya = array(
                'KODE' => $costrates->KODE,
                'NAMA_BIAYA' => Cost::where('KODE', $costrates->KODE_BIAYA)->first()->NAMA_BIAYA,
                'NAMA_VENDOR' => Vendor::where('KODE', $costrates->KODE_VENDOR)->first()->NAMA,
                'NAMA_PELABUHAN_ASAL' => $namaPelabuhanAsal,
                'NAMA_PELABUHAN_TUJUAN' => $namaPelabuhanTujuan,
                'NAMA_COMMODITY' => $namaCommodity,
                'NAMA_UK_KONTAINER' => $namaUkKontainer,
                'TARIF' => $costrates->TARIF,
                'TGL_BERLAKU' => $costrates->TGL_BERLAKU,
                'KETERANGAN' => $costrates->KETERANGAN,
                'NAMA_CUSTOMER' => Customer::where('KODE', $costrates->KODE_CUSTOMER)->first()->NAMA,
            );

            return ApiResponse::json(true, 'tarifbiaya successfully updated', $resp_tarifbiaya);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }

    public function destroy($KODE)
    {
        try {
            $city = CostRate::findOrFail($KODE);
            $city->delete();
            return ApiResponse::json(true, 'tarifbiaya successfully deleted', ['KODE' => $KODE]);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to delete tarifbiaya', null, 500);
        }
    }

    public function trash()
    {
        $provinces = CostRate::onlyTrashed()->get();

        $resp_provinces = [];

        foreach ($provinces as $costrates) {

            $resp_province = [
                'KODE' => $costrates->KODE,
                // 'KODE_RUTE_TRUCK' => $costrates->KODE_RUTE_TRUCK,
                'NAMA_BIAYA' => Cost::where('KODE', $costrates->KODE_BIAYA)->first()->NAMA_BIAYA,
                'NAMA_VENDOR' => Vendor::where('KODE', $costrates->KODE_VENDOR)->first()->NAMA,
                'NAMA_PELABUHAN_ASAL' => Harbor::where('KODE', $costrates->KODE_PELABUHAN_ASAL)->first()->NAMA_PELABUHAN,
                'NAMA_PELABUHAN_TUJUAN' => Harbor::where('KODE', $costrates->KODE_PELABUHAN_TUJUAN)->first()->NAMA_PEALABUHAN,
                'NAMA_COMMODITY' => Commodity::where('KODE', $costrates->KODE_COMMODITY)->first()->NAMA,
                'NAMA_UK_KONTAINER' => Size::where('KODE', $costrates->UK_KONTAINER)->first()->NAMA,
                'TARIF' => $costrates->TARIF,
                'TGL_BERLAKU' => $costrates->TGL_BERLAKU,
                'KETERANGAN' => $costrates->KETERANGAN,
                'NAMA_CUSTOMER' => Customer::where('KODE', $costrates->KODE_CUSTOMER)->first()->NAMA,
            ];

            $resp_provinces[] = $resp_province;
        }

        return ApiResponse::json(true, 'Trash bin fetched', $resp_provinces);
    }

    public function restore($id)
    {
        $restored = CostRate::onlyTrashed()->findOrFail($id);
        $restored->restore();

        return ApiResponse::json(true, 'Restored', ['KODE' => $id]);
    }

    public function getNextId()
    {
        $nextId = $this->getLocalNextId();

        return ApiResponse::json(true, 'Next KODE retrieved successfully', $nextId);
    }

    public function getLocalNextId()
    {
        // Get the maximum COM.X ID from the database
        $maxId = CostRate::where('KODE', 'LIKE', 'HPB.%')
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
        $nextId = 'HPB.' . $nextNumber;

        return $nextId;
    }
}
