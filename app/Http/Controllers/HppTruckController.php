<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\City;
use App\Models\Commodity;
use App\Models\Size;
use App\Models\Truck;
use App\Models\HppTruck;
use App\Models\TruckRoute;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class HppTruckController extends Controller
{

    //search based on a few KODE
    public function findOne(Request $request)
    {
        try {
            $currentDate = now(); // Get the current date and time

            $hpptruck = HppTruck::where('KODE_RUTE_TRUCK', $request->KODE_RUTE_TRUCK)
                ->where('KODE_VENDOR', $request->KODE_VENDOR)
                ->where('KODE_COMMODITY', $request->KODE_COMMODITY)
                ->whereDate('BERLAKU', '<=', $currentDate) // Filter by TGL_BERLAKU >= current date
                ->orderBy('BERLAKU', 'desc') // Order by BERLAKU in descending order
                ->first();

            if (!$hpptruck) {
                return ApiResponse::json(false, 'Data not found', null, 404);
            }

            return ApiResponse::json(true, 'Data retrieved successfully', $hpptruck);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to retrieve data', null, 500);
        }
    }

    public function dataTableJson(Request $request)
    {
        // try {
        $order = $request->input('order')[0];
        $columnIndex = (int) $order['column'];
        $sortDirection = $order['dir'];

        $columns = $request->input('columns');
        $columnToSort = $columns[$columnIndex]['data'];

        $query = HppTruck::query();

        $hasSearch = $request->has('search') && !empty($request->input('search')['value']);
        $dateColumns = ["BERLAKU"]; // Add your date column names here
        // Apply search filter if applicable
        if ($hasSearch) {
            $searchValue = strtoupper($request->input('search')['value']);
            $attributes = HppTruck::first()->getAttributes(); // Get all attribute names
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
                'truck' => [
                    'table' => 'trucks',
                    'column' => 'NAMA',
                ],
                'commodity' => [
                    'table' => 'commodities',
                    'column' => 'NAMA',
                ],
                'truck_route' => [
                    'table' => 'truck_routes',
                    'column' => 'KODE',
                ],

                'vendor' => [
                    'table' => 'vendors',
                    'column' => 'NAMA',
                ],


            ];

            foreach ($relatedColumns as $relation => $relatedColumn) {
                if ($relation === 'truck_route') {
                    $query->orWhereHas($relation, function ($q) use ($searchValue, $relatedColumn) {
                        $q->where(DB::raw('CONCAT("RUTE_ASAL", \' - \', "RUTE_TUJUAN")'), 'LIKE', "%$searchValue%");
                    });
                    continue;
                } else {
                    $query->orWhereHas($relation, function ($q) use ($searchValue, $relatedColumn) {
                        $q->where($relatedColumn['column'], 'LIKE', "%$searchValue%");
                    });
                }
            }
        }

        $query->orderByRaw('CAST(SUBSTRING("KODE" FROM \'\\.([0-9]+)$\') AS INTEGER) ASC, "KODE" ASC');

        $allData = $query->get();



        if (!empty($columnToSort)) {
            foreach ($allData as $truckPrice) {
                // $rute_truck = TruckRoute::where('KODE', $truckPrice->KODE_RUTE_TRUCK)->first();
                // $truckPrice->KODE_RUTE_TRUCK = $rute_truck->KODE;
                // $UK_KONTAINERs = Size::where('KODE', $truckPrice->UK_KONTAINER)->first();
                // $truckPrice->NAMA_UK_KONTAINER = $UK_KONTAINERs->KETERANGAN;

                $COMMODITY = Commodity::where('KODE', $truckPrice->KODE_COMMODITY)->first();
                $truckPrice->NAMA_COMMODITY = $COMMODITY->NAMA;

                $truck = Truck::where('KODE', $truckPrice->KODE_TRUCK)->first();
                $truckPrice->NAMA_TRUCK = $truck->NAMA;


                $kode_vendor = Vendor::where('KODE', $truckPrice->KODE_VENDOR)->first();
                $truckPrice->NAMA_VENDOR = $kode_vendor->NAMA;

                $rute = TruckRoute::where('KODE', $truckPrice->KODE_RUTE_TRUCK)->first();
                $truckPrice->NAMA_RUTE_TRUCK = $rute->RUTE_ASAL . ' - ' . $rute->RUTE_TUJUAN;
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
        $truckprices = $allData->slice($skip)->take($limit);



        // ... rest of your code to enrich the data ...
        if (empty($columnToSort)) {
            foreach ($truckprices as $truckPrice) {
                // $rute_truck = TruckRoute::where('KODE', $truckPrice->KODE_RUTE_TRUCK)->first();
                // $truckPrice->KODE_RUTE_TRUCK = $rute_truck->KODE;
                // $UK_KONTAINERs = Size::where('KODE', $truckPrice->UK_KONTAINER)->first();
                // $truckPrice->NAMA_UK_KONTAINER = $UK_KONTAINERs->KETERANGAN;

                $COMMODITY = Commodity::where('KODE', $truckPrice->KODE_COMMODITY)->first();
                $truckPrice->NAMA_COMMODITY = $COMMODITY->NAMA;

                $truck = Truck::where('KODE', $truckPrice->KODE_TRUCK)->first();
                $truckPrice->NAMA_TRUCK = $truck->NAMA;


                $kode_vendor = Vendor::where('KODE', $truckPrice->KODE_VENDOR)->first();
                $truckPrice->NAMA_VENDOR = $kode_vendor->NAMA;

                $rute = TruckRoute::where('KODE', $truckPrice->KODE_RUTE_TRUCK)->first();
                $truckPrice->NAMA_RUTE_TRUCK = $rute->RUTE_ASAL . ' - ' . $rute->RUTE_TUJUAN;
            }
        }



        // Prepare the response data structure expected by the frontend

        $response = [
            'draw' => $request->input('draw'),
            'recordsTotal' => HppTruck::count(),
            'recordsFiltered' => $total_data_after_search,
            'data' => $truckprices->values()->toArray(),
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
            2 => ['truck_route', 'KODE'],
            3 => ['vendor', 'NAMA'],
            4 => ['commodity', 'NAMA'],
            5 => ['truck', 'NAMA'],

        ];
        try {
            $order = $request->input('order')[0];
            $columnIndex = (int) $order['column'];
            $sortDirection = $order['dir'];

            $columns = $request->input('columns');
            $columnToSort = $columns[$columnIndex]['data'];

            $query = HppTruck::query();

            //check for each column hasSearch columns[x][search][value]
            $columns = $request->input('columns');
            $hasSearch = false;
            foreach ($columns as $index => $column) {
                if (isset($column['search']['value']) && !empty($column['search']['value'])) {
                    $hasSearch = true;
                    break;
                }
            }

            // if ($hasSearch) {
            //     $columns = $request->input('columns');
            //     foreach ($columns as $index => $column) {
            //         if (isset($column['search']['value']) && !empty($column['search']['value'])) {
            //             if ($column['searchable'] === 'true') {
            //                 $columnName = $column['data'];
            //                 $searchValue = strtoupper($column['search']['value']);
            //                 //remove parantheses from search value
            //                 $searchValue = str_replace('(', '', $searchValue);
            //                 $searchValue = str_replace(')', '', $searchValue);
            //                 //if index is not related column
            //                 // if (!in_array($index, $relationColumns)) {

            //                 //     $query->where($columnName, 'LIKE', "%$searchValue%");
            //                 // }



            //                 //if index is related column
            //                 if (in_array($index, $relationColumns)) {
            //                     $query->whereHas($relatedTo[$index][0], function ($q) use ($searchValue, $relatedTo, $index) {
            //                         $q->where($relatedTo[$index][1], 'LIKE', "%$searchValue%");
            //                     });
            //                 } else {
            //                     $query->where($columnName, 'LIKE', "%$searchValue%");
            //                 }



            //                 // foreach ($relatedColumns as $relation => $relatedColumn) {
            //                 //     $query->orWhereHas($relation, function ($q) use ($searchValue, $relatedColumn) {
            //                 //         $q->where($relatedColumn['column'], 'LIKE', "%$searchValue%");
            //                 //     });
            //                 // }

            //                 // or if column is related column

            //             }
            //         }
            //     }
            // }
            $dateColumns = ["BERLAKU"]; // Add your date column names here

            if ($hasSearch) {
                foreach ($columns as $index => $column) {
                    if (isset($column['search']['value']) && !empty($column['search']['value'])) {
                        if ($column['searchable'] === 'true') {
                            $searchValue = strtoupper($column['search']['value']);
                            // Remove parentheses from search value
                            $searchValue = str_replace(['(', ')'], '', $searchValue);

                            if (array_key_exists($index, $relationColumnsAndTo)) {
                                [$relation, $relatedColumn] = $relationColumnsAndTo[$index];
                                if ($relation === 'truck_route') {
                                    $query->orWhereHas($relation, function ($q) use ($searchValue, $relatedColumn) {
                                        $q->where(DB::raw('CONCAT("RUTE_ASAL", \' - \', "RUTE_TUJUAN")'), 'LIKE', "%$searchValue%");
                                    });
                                    continue;
                                } else {
                                    $query->whereHas($relation, function ($q) use ($searchValue, $relatedColumn) {
                                        $q->where($relatedColumn, 'LIKE', "%$searchValue%");
                                    });
                                }
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
                foreach ($allData as $truckPrice) {
                    // $rute_truck = TruckRoute::where('KODE', $truckPrice->KODE_RUTE_TRUCK)->first();
                    // $truckPrice->KODE_RUTE_TRUCK = $rute_truck->KODE;
                    // $UK_KONTAINERs = Size::where('KODE', $truckPrice->UK_KONTAINER)->first();
                    // $truckPrice->NAMA_UK_KONTAINER = $UK_KONTAINERs->KETERANGAN;

                    $COMMODITY = Commodity::where('KODE', $truckPrice->KODE_COMMODITY)->first();
                    $truckPrice->NAMA_COMMODITY = $COMMODITY->NAMA;

                    $truck = Truck::where('KODE', $truckPrice->KODE_TRUCK)->first();
                    $truckPrice->NAMA_TRUCK = $truck->NAMA;


                    $kode_vendor = Vendor::where('KODE', $truckPrice->KODE_VENDOR)->first();
                    $truckPrice->NAMA_VENDOR = $kode_vendor->NAMA;

                    $rute = TruckRoute::where('KODE', $truckPrice->KODE_RUTE_TRUCK)->first();
                    $truckPrice->NAMA_RUTE_TRUCK = $rute->RUTE_ASAL . ' - ' . $rute->RUTE_TUJUAN;
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

            $truckprices = $allData->slice($skip)->take($limit);

            if (empty($columnToSort)) {
                foreach ($truckprices as $truckPrice) {
                    // $rute_truck = TruckRoute::where('KODE', $truckPrice->KODE_RUTE_TRUCK)->first();
                    // $truckPrice->KODE_RUTE_TRUCK = $rute_truck->KODE;
                    // $UK_KONTAINERs = Size::where('KODE', $truckPrice->UK_KONTAINER)->first();
                    // $truckPrice->NAMA_UK_KONTAINER = $UK_KONTAINERs->KETERANGAN;

                    $COMMODITY = Commodity::where('KODE', $truckPrice->KODE_COMMODITY)->first();
                    $truckPrice->NAMA_COMMODITY = $COMMODITY->NAMA;

                    $truck = Truck::where('KODE', $truckPrice->KODE_TRUCK)->first();
                    $truckPrice->NAMA_TRUCK = $truck->NAMA;


                    $kode_vendor = Vendor::where('KODE', $truckPrice->KODE_VENDOR)->first();
                    $truckPrice->NAMA_VENDOR = $kode_vendor->NAMA;

                    $rute = TruckRoute::where('KODE', $truckPrice->KODE_RUTE_TRUCK)->first();
                    $truckPrice->NAMA_RUTE_TRUCK = $rute->RUTE_ASAL . ' - ' . $rute->RUTE_TUJUAN;
                }
            }


            // ... rest of your code to enrich the data ...




            $response = [
                'draw' => $request->input('draw'),
                'recordsTotal' => HppTruck::count(),
                'recordsFiltered' => $total_data_after_search,
                'data' => $truckprices->values()->toArray(),
            ];

            return ApiResponse::json(true, 'Data retrieved successfully', $response);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }

    public function findByKode($KODE)
    {
        try {
            $User = HppTruck::where('KODE', $KODE)->first();
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
            $truckprices = HppTruck::orderBy('KODE', 'asc')->get();

            foreach ($truckprices as $truckPrice) {
                // $rute_truck = TruckRoute::where('KODE', $truckPrice->KODE_RUTE_TRUCK)->first();
                // $truckPrice->KODE_RUTE_TRUCK = $rute_truck->KODE;
                // $UK_KONTAINERs = Size::where('KODE', $truckPrice->UK_KONTAINER)->first();
                // $truckPrice->NAMA_UK_KONTAINER = $UK_KONTAINERs->KETERANGAN;

                $COMMODITY = Commodity::where('KODE', $truckPrice->KODE_COMMODITY)->first();
                $truckPrice->NAMA_COMMODITY = $COMMODITY->NAMA;

                $truck = Truck::where('KODE', $truckPrice->KODE_TRUCK)->first();
                $truckPrice->NAMA_TRUCK = $truck->NAMA;


                $kode_vendor = Vendor::where('KODE', $truckPrice->KODE_VENDOR)->first();
                $truckPrice->NAMA_VENDOR = $kode_vendor->NAMA;

                $rute = TruckRoute::where('KODE', $truckPrice->KODE_RUTE_TRUCK)->first();
                $truckPrice->NAMA_RUTE_TRUCK = $rute->RUTE_ASAL . ' - ' . $rute->RUTE_TUJUAN;
            }
            return ApiResponse::json(true, 'Data retrieved successfully',  $truckprices);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to retrieve data', null, 500);
        }
    }

    public function storeWeb(Request $request)
    {
        //use validator here
        $validator = Validator::make($request->all(), [
            'KODE_RUTE_TRUCK' => 'required',
            'KODE_COMMODITY' => 'required',
            'KODE_TRUCK' => 'required',
            'BERLAKU' => 'required|date',
            'KODE_VENDOR' => 'required',
            'HARGA_JUAL' => 'required|numeric',
            'KETERANGAN' => 'required',
        ], [
            'KODE_RUTE_TRUCK.required' => 'The KODE_RUTE_TRUCK field is required.',
            'KODE_VENDOR.required' => 'The KODE_VENDOR field is required.',
            'KODE_VENDOR.numeric' => 'The KODE_VENDOR must be numeric.',
            'KODE_VENDOR.required' => 'The KODE_VENDOR field is required.',
            'KODE_COMMODITY.required' => 'The KODE_COMMODITY field is required.',
            'KODE_COMMODITY.numeric' => 'The KODE_COMMODITY must be numeric.',
            'UK_KONTAINER.required' => 'The UK_KONTAINER field is required.',
            'UK_KONTAINER.numeric' => 'The UK_KONTAINER must be numeric.',
            'BERLAKU.required' => 'The BERLAKU field is required.',
            'BERLAKU.date' => 'The BERLAKU must be date.',
            'HARGA_JUAL.required' => 'The HARGA_JUAL field is required.',
            'KETERANGAN.required' => 'The KETERANGAN field is required.',
        ]);

        //if validator fails
        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }


        try {
            $validatedData = $validator->validated();


            $new_truck_price = new HppTruck();
            $new_truck_price->KODE = $this->getLocalNextId();
            $new_truck_price->KODE_RUTE_TRUCK = $validatedData['KODE_RUTE_TRUCK'];
            // $new_truck_price->KODE_VENDOR = $validatedData['KODE_VENDOR'];
            $new_truck_price->KODE_COMMODITY = $validatedData['KODE_COMMODITY'];
            // $new_truck_price->UK_KONTAINER = $validatedData['UK_KONTAINER'];
            $new_truck_price->KODE_TRUCK = $validatedData['KODE_TRUCK'];
            $new_truck_price->KODE_VENDOR = $validatedData['KODE_VENDOR'];
            $new_truck_price->BERLAKU = $validatedData['BERLAKU'];
            $new_truck_price->HARGA_JUAL = $validatedData['HARGA_JUAL'];
            $new_truck_price->KETERANGAN = $validatedData['KETERANGAN'];
            $new_truck_price->save();
            if (!$new_truck_price) {
                return ApiResponse::json(false, 'Failed to insert data', null, 500);
            }
            $id = $new_truck_price->KODE;

            $truckpricesr = HppTruck::where('KODE', $id)->first();

            $resp_truckpricer = array(
                'KODE' => $truckpricesr->KODE,
                'KODE_RUTE_TRUCK' => $truckpricesr->KODE_RUTE_TRUCK,
                'NAMA_VENDOR' => Vendor::where('KODE', $truckpricesr->KODE_VENDOR)->first()->NAMA,
                // 'NAMA_VENDOR' => Vendor::where('KODE', $truckpricesr->KODE_VENDOR)->first()->NAMA,
                'NAMA_COMMODITY' => Commodity::where('KODE', $truckpricesr->KODE_COMMODITY)->first()->NAMA,
                // 'NAMA_UK_KONTAINER' => Size::where('KODE', $truckpricesr->UK_KONTAINER)->first()->KETERANGAN,
                'NAMA_TRUCK' => Truck::where('KODE', $truckpricesr->KODE_TRUCK)->first()->NAMA,
                'BERLAKU' => $truckpricesr->BERLAKU,
                'HARGA_JUAL' => $truckpricesr->HARGA_JUAL,
                'KETERANGAN' => $truckpricesr->KETERANGAN,
            );


            return ApiResponse::json(true, "Data inserted successfully with KODE $truckpricesr->KODE", $resp_truckpricer, 201);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }

    public function update(Request $request, $KODE)
    {
        //use validator here
        $validator = Validator::make($request->all(), [

            'KODE_RUTE_TRUCK' => 'required',
            'KODE_COMMODITY' => 'required',
            'KODE_TRUCK' => 'required',
            'BERLAKU' => 'required|date',
            'KODE_VENDOR' => 'required',
            'HARGA_JUAL' => 'required|numeric',
            'KETERANGAN' => 'required',
        ], [
            'KODE_RUTE_TRUCK.required' => 'The KODE_RUTE_TRUCK field is required.',
            'KODE_VENDOR.required' => 'The KODE_VENDOR field is required.',
            'KODE_VENDOR.numeric' => 'The KODE_VENDOR must be numeric.',
            'KODE_VENDOR.required' => 'The KODE_VENDOR field is required.',
            'KODE_COMMODITY.required' => 'The KODE_COMMODITY field is required.',
            'KODE_COMMODITY.numeric' => 'The KODE_COMMODITY must be numeric.',
            'UK_KONTAINER.required' => 'The UK_KONTAINER field is required.',
            'UK_KONTAINER.numeric' => 'The UK_KONTAINER must be numeric.',
            'BERLAKU.required' => 'The BERLAKU field is required.',
            'BERLAKU.date' => 'The BERLAKU must be date.',
            'HARGA_JUAL.required' => 'The HARGA_JUAL field is required.',
            'KETERANGAN.required' => 'The KETERANGAN field is required.',
        ]);

        //if validator fails
        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }

        // Check uniqueness for NAMA case insensitive except for current KODE
        // $truckprices = HppTruck::where(DB::raw('LOWER("NAMA_BIAYA")'), strtolower($request->NAMA_BIAYA))->where('KODE', '!=', $KODE)->first();
        // if ($truckprices) {
        //     return ApiResponse::json(false, ['NAMA_BIAYA' => ['The NAMA_BIAYA has already been taken.']], null, 422);
        // }
        try {
            $validatedData = $validator->validated();



            $truckprices = HppTruck::findOrFail($KODE);
            $truckprices->KODE_RUTE_TRUCK = $request->get('KODE_RUTE_TRUCK');
            // $truckprices->KODE_VENDOR = $request->get('KODE_VENDOR');
            $truckprices->KODE_COMMODITY = $request->get('KODE_COMMODITY');
            // $truckprices->UK_KONTAINER = $request->get('UK_KONTAINER');
            $truckprices->KODE_TRUCK = $request->get('KODE_TRUCK');
            $truckprices->BERLAKU = $request->get('BERLAKU');
            $truckprices->KODE_VENDOR = $request->get('KODE_VENDOR');
            $truckprices->HARGA_JUAL = $request->get('HARGA_JUAL');
            $truckprices->KETERANGAN = $request->get('KETERANGAN');
            $truckprices->save();
            if (!$truckprices) {
                return ApiResponse::json(false, 'Failed to update data', null, 500);
            }

            $truckprices = HppTruck::where('KODE', $KODE)->first();

            $resp_truckprice = array(
                'KODE' => $truckprices->KODE,
                'KODE_RUTE_TRUCK' => $truckprices->KODE_RUTE_TRUCK,
                'NAMA_VENDOR' => Vendor::where('KODE', $truckprices->KODE_VENDOR)->first()->NAMA,
                // 'KODE_RUTE_TRUCK' => TruckRoute::where('KODE', $truckprices->KODE_RUTE_TRUCK)->first()->KODE,
                // 'NAMA_VENDOR' => Vendor::where('KODE', $truckprices->KODE_VENDOR)->first()->NAMA,
                'NAMA_COMMODITY' => Commodity::where('KODE', $truckprices->KODE_COMMODITY)->first()->NAMA,
                // 'NAMA_UK_KONTAINER' => Size::where('KODE', $truckprices->UK_KONTAINER)->first()->KETERANGAN,
                'NAMA_TRUCK' => Truck::where('KODE', $truckprices->KODE_TRUCK)->first()->NAMA,
                'BERLAKU' => $truckprices->BERLAKU,

                'HARGA_JUAL' => $truckprices->HARGA_JUAL,
                'KETERANGAN' => $truckprices->KETERANGAN,
            );
            return ApiResponse::json(true, 'truckprice successfully updated', $resp_truckprice);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }

    public function destroy($KODE)
    {
        try {
            $city = HppTruck::findOrFail($KODE);
            $city->delete();
            return ApiResponse::json(true, 'truckprice successfully deleted', ['KODE' => $KODE]);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to delete truckprice', null, 500);
        }
    }

    public function trash()
    {
        $provinces = HppTruck::onlyTrashed()->get();

        $resp_provinces = [];

        foreach ($provinces as $truckprices) {

            $resp_province = [
                'KODE' => $truckprices->KODE,
                'KODE_RUTE_TRUCK' => $truckprices->KODE_RUTE_TRUCK,
                'NAMA_VENDOR' => Vendor::where('KODE', $truckprices->KODE_VENDOR)->first()->NAMA,
                // 'KODE_RUTE_TRUCK' => TruckRoute::where('KODE', $truckprices->KODE_RUTE_TRUCK)->first()->RUTE,
                // 'NAMA_VENDOR' => Vendor::where('KODE', $truckprices->KODE_VENDOR)->first()->NAMA,
                'NAMA_COMMODITY' => Commodity::where('KODE', $truckprices->KODE_COMMODITY)->first()->NAMA,
                'NAMA_TRUCK' => Truck::where('KODE', $truckprices->KODE_TRUCK)->first()->NAMA,
                // 'NAMA_UK_KONTAINER' => Size::where('KODE', $truckprices->UK_KONTAINER)->first()->KETERANGAN,
                'BERLAKU' => $truckprices->BERLAKU,
                'HARGA_JUAL' => $truckprices->HARGA_JUAL,
                'KETERANGAN' => $truckprices->KETERANGAN,
            ];

            $resp_provinces[] = $resp_province;
        }

        return ApiResponse::json(true, 'Trash bin fetched', $resp_provinces);
    }

    public function restore($id)
    {
        $restored = HppTruck::onlyTrashed()->findOrFail($id);
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
        $maxId = HppTruck::where('KODE', 'LIKE', 'HPT.%')
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
        $nextId = 'HPT.' . $nextNumber;

        return $nextId;
    }
}
