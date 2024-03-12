<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Helpers\IntAndDateFormatter;
use App\Models\City;
use App\Models\Commodity;
use App\Models\Customer;
use App\Models\Size;
use App\Models\Truck;
use App\Models\TruckPrice;
use App\Models\TruckRoute;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TruckPriceController extends Controller
{
    public function dataTableJson(Request $request)
    {
        // try {
        $order = $request->input('order')[0];
        $columnIndex = (int) $order['column'];
        $sortDirection = $order['dir'];

        $columns = $request->input('columns');
        $columnToSort = $columns[$columnIndex]['data'];

        $query = TruckPrice::query();

        $hasSearch = $request->has('search') && !empty($request->input('search')['value']);
        $dateColumns = ["BERLAKU"]; // Add your date column names here
        // Apply search filter if applicable
        if ($hasSearch) {
            $searchValue = strtoupper($request->input('search')['value']);
            $attributes = TruckPrice::first()->getAttributes(); // Get all attribute names
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
                'commodity' => [
                    'table' => 'commodities',
                    'column' => 'NAMA',
                ],
                'truck' => [
                    'table' => 'trucks',
                    'column' => 'NAMA',
                ],
                'customer' => [
                    'table' => 'customers',
                    'column' => 'NAMA',
                ],
                'truck_route' => [
                    'table' => 'truck_routes',
                    'column' => 'KODE',
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

                $customer = Customer::where('KODE', $truckPrice->KODE_CUSTOMER)->first();
                $truckPrice->NAMA_CUSTOMER = $customer->NAMA;

                // rute
                $rute = TruckRoute::where('KODE', $truckPrice->KODE_RUTE_TRUCK)->first();
                $truckPrice->NAMA_RUTE_TRUCK = $rute->RUTE_ASAL . ' - ' . $rute->RUTE_TUJUAN;



                // $kode_vendor = Vendor::where('KODE', $truckPrice->KODE_VENDOR)->first();
                // $truckPrice->NAMA_VENDOR = $kode_vendor->NAMA;
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

                $customer = Customer::where('KODE', $truckPrice->KODE_CUSTOMER)->first();
                $truckPrice->NAMA_CUSTOMER = $customer->NAMA;

                // rute
                $rute = TruckRoute::where('KODE', $truckPrice->KODE_RUTE_TRUCK)->first();
                $truckPrice->NAMA_RUTE_TRUCK = $rute->RUTE_ASAL . ' - ' . $rute->RUTE_TUJUAN;



                // $kode_vendor = Vendor::where('KODE', $truckPrice->KODE_VENDOR)->first();
                // $truckPrice->NAMA_VENDOR = $kode_vendor->NAMA;
            }
        }



        // Prepare the response data structure expected by the frontend

        $response = [
            'draw' => $request->input('draw'),
            'recordsTotal' => TruckPrice::count(),
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
            3 => ['customer', 'NAMA'],
            4 => ['commodity', 'NAMA'],
            5 => ['truck', 'NAMA'],

        ];
        try {
            $order = $request->input('order')[0];
            $columnIndex = (int) $order['column'];
            $sortDirection = $order['dir'];

            $columns = $request->input('columns');
            $columnToSort = $columns[$columnIndex]['data'];

            $query = TruckPrice::query();

            //check for each column hasSearch columns[x][search][value]
            $columns = $request->input('columns');
            $hasSearch = false;
            foreach ($columns as $index => $column) {
                if (isset($column['search']['value']) && !empty($column['search']['value'])) {
                    $hasSearch = true;
                    break;
                }
            }


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
                                //custom if relation is truck_route
                                if ($relation === 'truck_route') {
                                    $query->whereHas($relation, function ($q) use ($searchValue, $relatedColumn) {
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

                    $customer = Customer::where('KODE', $truckPrice->KODE_CUSTOMER)->first();
                    $truckPrice->NAMA_CUSTOMER = $customer->NAMA;

                    // rute
                    $rute = TruckRoute::where('KODE', $truckPrice->KODE_RUTE_TRUCK)->first();
                    $truckPrice->NAMA_RUTE_TRUCK = $rute->RUTE_ASAL . ' - ' . $rute->RUTE_TUJUAN;



                    // $kode_vendor = Vendor::where('KODE', $truckPrice->KODE_VENDOR)->first();
                    // $truckPrice->NAMA_VENDOR = $kode_vendor->NAMA;
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

                    $customer = Customer::where('KODE', $truckPrice->KODE_CUSTOMER)->first();
                    $truckPrice->NAMA_CUSTOMER = $customer->NAMA;

                    // rute
                    $rute = TruckRoute::where('KODE', $truckPrice->KODE_RUTE_TRUCK)->first();
                    $truckPrice->NAMA_RUTE_TRUCK = $rute->RUTE_ASAL . ' - ' . $rute->RUTE_TUJUAN;



                    // $kode_vendor = Vendor::where('KODE', $truckPrice->KODE_VENDOR)->first();
                    // $truckPrice->NAMA_VENDOR = $kode_vendor->NAMA;
                }
            }


            // ... rest of your code to enrich the data ...




            $response = [
                'draw' => $request->input('draw'),
                'recordsTotal' => TruckPrice::count(),
                'recordsFiltered' => $total_data_after_search,
                'data' => $truckprices->values()->toArray(),
            ];

            return ApiResponse::json(true, 'Data retrieved successfully', $response);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }
    public function json(Request $request)
    {
        // try {
        $query = TruckPrice::query();
        $customer = new TruckPrice(); // Create an instance of the Customer model

        $hasSearch = $request->has('search') && !empty($request->input('search')['value']);
        // Apply search filter if applicable
        if ($hasSearch) {
            $searchValue = strtoupper($request->input('search')['value']);
            $attributes = TruckPrice::first()->getAttributes(); // Get all attribute names
            $query->where(function ($q) use ($searchValue, $attributes) {
                foreach ($attributes as $attribute => $value) {
                    $q->orWhere($attribute, 'LIKE', "%$searchValue%");
                }
            });

            $relatedColumns = [
                'commodity' => [
                    'table' => 'commodities',
                    'column' => 'NAMA',
                ],
                'truck' => [
                    'table' => 'trucks',
                    'column' => 'NAMA',
                ],
                'customer' => [
                    'table' => 'customers',
                    'column' => 'NAMA',
                ],
                'truck_route' => [
                    'table' => 'truck_routes',
                    'column' => 'NAMA',
                ],
            ];

            foreach ($relatedColumns as $relation => $relatedColumn) {
                $query->orWhereHas($relation, function ($q) use ($searchValue, $relatedColumn) {
                    $q->where($relatedColumn['column'], 'LIKE', "%$searchValue%");
                });
            }
        }

        // Apply pagination and limit
        $limit = $request->get('length', 10);
        $page = $request->get('start', 0) / $limit + 1;
        $skip = ($page - 1) * $limit;
        $total_data_after_search = $query->count();
        $customers = $query->take($limit)->skip($skip)->get();


        // ... rest of your code to enrich the data ...

        foreach ($customers as $customer) {
            $COMMODITY = Commodity::where('KODE', $customer->KODE_COMMODITY)->first();
            $customer->NAMA_COMMODITY = $COMMODITY->NAMA;

            $truck = Truck::where('KODE', $customer->KODE_TRUCK)->first();
            $customer->NAMA_TRUCK = $truck->NAMA;

            $customer = Customer::where('KODE', $customer->KODE_CUSTOMER)->first();
            $customer->NAMA_CUSTOMER = $customer->NAMA;

            // rute
            $rute = TruckRoute::where('KODE', $customer->KODE_RUTE_TRUCK)->first();
            $customer->NAMA_RUTE_TRUCK = $rute->RUTE_ASAL . ' - ' . $rute->RUTE_TUJUAN;
        }

        // Prepare the response data structure expected by the frontend

        $response = [
            'draw' => $request->input('draw'),
            'recordsTotal' => TruckPrice::count(),
            'recordsFiltered' => $total_data_after_search,
            'data' => $customers->toArray(),
        ];



        return ApiResponse::json(true, 'Data retrieved successfully', $response);
    }

    public function dropdown()
    {
        //get all customers
        $customers = Customer::all();
        $customers = $customers->toArray();
        //get all truck routes
        $truckroutes = TruckRoute::all();
        $truckroutes = $truckroutes->toArray();
        //get all commodity
        $commodities = Commodity::all();
        $commodities = $commodities->toArray();
        //get all trucks
        $trucks = Truck::all();
        $trucks = $trucks->toArray();


        //return all data
        return ApiResponse::json(true, null, [
            'customers' => $customers,
            'truckRoutes' => $truckroutes,
            'commodities' => $commodities,
            'trucks' => $trucks,
        ]);
    }
    public function findByKode($KODE)
    {
        try {
            $truckprices = TruckPrice::where('KODE', $KODE)->first();
            //convert to array
            $truckprices = $truckprices->toArray();
            return ApiResponse::json(true, 'Truck price retrieved successfully',  $truckprices);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to retrieve truckprice', null, 500);
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
            $truckprices = TruckPrice::orderBy('KODE', 'asc')->get();

            foreach ($truckprices as $truckPrice) {
                // $rute_truck = TruckRoute::where('KODE', $truckPrice->KODE_RUTE_TRUCK)->first();
                // $truckPrice->KODE_RUTE_TRUCK = $rute_truck->KODE;
                // $UK_KONTAINERs = Size::where('KODE', $truckPrice->UK_KONTAINER)->first();
                // $truckPrice->NAMA_UK_KONTAINER = $UK_KONTAINERs->KETERANGAN;

                $COMMODITY = Commodity::where('KODE', $truckPrice->KODE_COMMODITY)->first();
                $truckPrice->NAMA_COMMODITY = $COMMODITY->NAMA;

                $truck = Truck::where('KODE', $truckPrice->KODE_TRUCK)->first();
                $truckPrice->NAMA_TRUCK = $truck->NAMA;

                $customer = Customer::where('KODE', $truckPrice->KODE_CUSTOMER)->first();
                $truckPrice->NAMA_CUSTOMER = $customer->NAMA;

                // rute
                $rute = TruckRoute::where('KODE', $truckPrice->KODE_RUTE_TRUCK)->first();
                $truckPrice->NAMA_RUTE_TRUCK = $rute->RUTE_ASAL . ' - ' . $rute->RUTE_TUJUAN;



                // $kode_vendor = Vendor::where('KODE', $truckPrice->KODE_VENDOR)->first();
                // $truckPrice->NAMA_VENDOR = $kode_vendor->NAMA;
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
            'KODE_CUSTOMER' => 'required',
            'HARGA_JUAL' => 'required|numeric',
            'KETERANGAN' => 'required',
        ], [
            'KODE_RUTE_TRUCK.required' => 'The KODE_RUTE_TRUCK field is required.',
            'KODE_VENDOR.required' => 'The KODE_VENDOR field is required.',
            'KODE_VENDOR.numeric' => 'The KODE_VENDOR must be numeric.',
            'KODE_CUSTOMER.required' => 'The KODE_CUSTOMER field is required.',
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


            $new_truck_price = new TruckPrice();
            $new_truck_price->KODE = $this->getLocalNextId();
            $new_truck_price->KODE_RUTE_TRUCK = $validatedData['KODE_RUTE_TRUCK'];
            // $new_truck_price->KODE_VENDOR = $validatedData['KODE_VENDOR'];
            $new_truck_price->KODE_COMMODITY = $validatedData['KODE_COMMODITY'];
            // $new_truck_price->UK_KONTAINER = $validatedData['UK_KONTAINER'];
            $new_truck_price->KODE_TRUCK = $validatedData['KODE_TRUCK'];
            $new_truck_price->KODE_CUSTOMER = $validatedData['KODE_CUSTOMER'];
            $new_truck_price->BERLAKU = $validatedData['BERLAKU'];
            $new_truck_price->HARGA_JUAL = $validatedData['HARGA_JUAL'];
            $new_truck_price->KETERANGAN = $validatedData['KETERANGAN'];
            $new_truck_price->save();
            if (!$new_truck_price) {
                return ApiResponse::json(false, 'Failed to insert data', null, 500);
            }
            $id = $new_truck_price->KODE;

            $truckpricesr = TruckPrice::where('KODE', $id)->first();

            $resp_truckpricer = array(
                'KODE' => $truckpricesr->KODE,
                'KODE_RUTE_TRUCK' => $truckpricesr->KODE_RUTE_TRUCK,
                // RUTE_ASAL + RUTE TUJUAN
                'NAMA_RUTE_TRUCK' => TruckRoute::where('KODE', $truckpricesr->KODE_RUTE_TRUCK)->first()->RUTE_ASAL . ' - ' . TruckRoute::where('KODE', $truckpricesr->KODE_RUTE_TRUCK)->first()->RUTE_TUJUAN,
                'NAMA_CUSTOMER' => Customer::where('KODE', $truckpricesr->KODE_CUSTOMER)->first()->NAMA,
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
            'KODE_CUSTOMER' => 'required',
            'HARGA_JUAL' => 'required|numeric',
            'KETERANGAN' => 'required',
        ], [
            'KODE_RUTE_TRUCK.required' => 'The KODE_RUTE_TRUCK field is required.',
            'KODE_VENDOR.required' => 'The KODE_VENDOR field is required.',
            'KODE_VENDOR.numeric' => 'The KODE_VENDOR must be numeric.',
            'KODE_CUSTOMER.required' => 'The KODE_CUSTOMER field is required.',
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
        // $truckprices = TruckPrice::where(DB::raw('LOWER("NAMA_BIAYA")'), strtolower($request->NAMA_BIAYA))->where('KODE', '!=', $KODE)->first();
        // if ($truckprices) {
        //     return ApiResponse::json(false, ['NAMA_BIAYA' => ['The NAMA_BIAYA has already been taken.']], null, 422);
        // }
        try {
            $validatedData = $validator->validated();



            $truckprices = TruckPrice::findOrFail($KODE);
            $truckprices->KODE_RUTE_TRUCK = $request->get('KODE_RUTE_TRUCK');
            // $truckprices->KODE_VENDOR = $request->get('KODE_VENDOR');
            $truckprices->KODE_COMMODITY = $request->get('KODE_COMMODITY');

            // $truckprices->UK_KONTAINER = $request->get('UK_KONTAINER');
            $truckprices->KODE_TRUCK = $request->get('KODE_TRUCK');
            $truckprices->BERLAKU = $request->get('BERLAKU');
            $truckprices->KODE_CUSTOMER = $request->get('KODE_CUSTOMER');
            $truckprices->HARGA_JUAL = $request->get('HARGA_JUAL');
            $truckprices->KETERANGAN = $request->get('KETERANGAN');
            $truckprices->save();
            if (!$truckprices) {
                return ApiResponse::json(false, 'Failed to update data', null, 500);
            }

            //finq updated data
            $truckprices = TruckPrice::where('KODE', $KODE)->first();


            $resp_truckprice = array(
                'KODE' => $truckprices->KODE,
                'KODE_RUTE_TRUCK' => $truckprices->KODE_RUTE_TRUCK,
                'NAMA_CUSTOMER' => Customer::where('KODE', $truckprices->KODE_CUSTOMER)->first()->NAMA,
                'NAMA_RUTE_TRUCK' => TruckRoute::where('KODE', $truckprices->KODE_RUTE_TRUCK)->first()->RUTE_ASAL . ' - ' . TruckRoute::where('KODE', $truckprices->KODE_RUTE_TRUCK)->first()->RUTE_TUJUAN,

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
            $city = TruckPrice::findOrFail($KODE);
            $city->delete();
            return ApiResponse::json(true, 'truckprice successfully deleted', ['KODE' => $KODE]);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to delete truckprice', null, 500);
        }
    }

    public function trash()
    {
        $provinces = TruckPrice::onlyTrashed()->get();

        $resp_provinces = [];

        foreach ($provinces as $truckprices) {

            $resp_province = [
                'KODE' => $truckprices->KODE,
                'KODE_RUTE_TRUCK' => $truckprices->KODE_RUTE_TRUCK,
                'NAMA_CUSTOMER' => Customer::where('KODE', $truckprices->KODE_CUSTOMER)->first()->NAMA,
                'NAMA_RUTE_TRUCK' => TruckRoute::where('KODE', $truckprices->KODE_RUTE_TRUCK)->first()->RUTE_ASAL . ' - ' . TruckRoute::where('KODE', $truckprices->KODE_RUTE_TRUCK)->first()->RUTE_TUJUAN,

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
        $restored = TruckPrice::onlyTrashed()->findOrFail($id);
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
        $maxId = TruckPrice::where('KODE', 'LIKE', 'TT.%')
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
        $nextId = 'TT.' . $nextNumber;

        return $nextId;
    }
}
