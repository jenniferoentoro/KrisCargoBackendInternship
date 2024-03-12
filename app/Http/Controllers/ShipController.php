<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Ship;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ShipController extends Controller
{
    public function dataTableJson(Request $request)
    {
        // try {
        $order = $request->input('order')[0];
        $columnIndex = (int) $order['column'];
        $sortDirection = $order['dir'];

        $columns = $request->input('columns');
        $columnToSort = $columns[$columnIndex]['data'];

        $query = Ship::query();

        $hasSearch = $request->has('search') && !empty($request->input('search')['value']);
        $dateColumns = ["TGL_REG"]; // Add your date column names here
        // Apply search filter if applicable
        if ($hasSearch) {
            $searchValue = strtoupper($request->input('search')['value']);
            $attributes = Ship::first()->getAttributes(); // Get all attribute names
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
                'vendor' => [
                    'table' => 'vendors',
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
            foreach ($allData as $ship) {
                $ship->NAMA_VENDOR = Vendor::where('KODE', $ship->KODE_VENDOR)->first()->NAMA;
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
        $ships = $allData->slice($skip)->take($limit);



        // ... rest of your code to enrich the data ...
        if (empty($columnToSort)) {
            foreach ($ships as $ship) {
                $ship->NAMA_VENDOR = Vendor::where('KODE', $ship->KODE_VENDOR)->first()->NAMA;
            }
        }



        // Prepare the response data structure expected by the frontend

        $response = [
            'draw' => $request->input('draw'),
            'recordsTotal' => Ship::count(),
            'recordsFiltered' => $total_data_after_search,
            'data' => $ships->values()->toArray(),
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
            3 => ['vendor', 'NAMA'],



        ];
        try {
            $order = $request->input('order')[0];
            $columnIndex = (int) $order['column'];
            $sortDirection = $order['dir'];

            $columns = $request->input('columns');
            $columnToSort = $columns[$columnIndex]['data'];

            $query = Ship::query();

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

            $query->orderByRaw('CAST(SUBSTRING("KODE" FROM \'\\.([0-9]+)$\') AS INTEGER) ASC, "KODE" ASC');

            $allData = $query->get();



            if (!empty($columnToSort)) {
                foreach ($allData as $ship) {
                    $ship->NAMA_VENDOR = Vendor::where('KODE', $ship->KODE_VENDOR)->first()->NAMA;
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

            $ships = $allData->slice($skip)->take($limit);

            if (empty($columnToSort)) {
                foreach ($ships as $ship) {
                    $ship->NAMA_VENDOR = Vendor::where('KODE', $ship->KODE_VENDOR)->first()->NAMA;
                }
            }


            // ... rest of your code to enrich the data ...




            $response = [
                'draw' => $request->input('draw'),
                'recordsTotal' => Ship::count(),
                'recordsFiltered' => $total_data_after_search,
                'data' => $ships->values()->toArray(),
            ];

            return ApiResponse::json(true, 'Data retrieved successfully', $response);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }
    public function findByKode($KODE)
    {
        try {
            $ship = Ship::where('KODE', $KODE)->first();
            //convert to array
            $ship = $ship->toArray();
            return ApiResponse::json(true, 'Ship retrieved successfully',  $ship);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to retrieve Ship', null, 500);
        }
    }




    public function index()
    {
        try {
            //order by KODE
            $ships = Ship::orderBy('KODE')->get();
            foreach ($ships as $ship) {
                $ship->NAMA_VENDOR = Vendor::where('KODE', $ship->KODE_VENDOR)->first()->NAMA;
            }
            return ApiResponse::json(true, 'Data retrieved successfully',  $ships);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to retrieve data', null, 500);
        }
    }

    public function storeWeb(Request $request)
    {
        //use validator here
        $validator = Validator::make($request->all(), [
            'KAPAL' => 'required|unique:ships,KAPAL',
            'KODE_VENDOR' => 'required',
            'KETERANGAN' => 'required',
        ], []);


        //if validator fails
        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }

        // Check uniqueness for NAMA case insensitive
        $existingData = Ship::where(DB::raw('LOWER("KAPAL")'), strtolower($request->KAPAL))->first();
        if ($existingData) {
            return ApiResponse::json(false, ['KAPAL' => ['The KAPAL has already been taken.']], null, 422);
        }


        try {

            $new_ship = new Ship();
            $new_ship->KODE = $this->getLocalNextId();
            $new_ship->KAPAL = $request->KAPAL;
            $new_ship->KODE_VENDOR = $request->KODE_VENDOR;
            $new_ship->KETERANGAN = $request->KETERANGAN;

            $new_ship->save();
            if (!$new_ship) {
                return ApiResponse::json(false, 'Failed to insert data', null, 500);
            }
            $id = $new_ship->KODE;

            $ship = Ship::where('KODE', $id)->first();
            $resp_ship = array(
                'KODE' => $ship->KODE,
                'KAPAL' => $ship->KAPAL,
                'NAMA_VENDOR' => Vendor::where('KODE', $ship->KODE_VENDOR)->first()->NAMA,
                'KETERANGAN' => $ship->KETERANGAN,


            );


            return ApiResponse::json(true, "Data inserted successfully with KODE $ship->KODE", $resp_ship, 201);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }

    public function update(Request $request, $KODE)
    {
        //use validator here
        $validator = Validator::make($request->all(), [
            'KAPAL' => 'required|unique:ships,KAPAL,' . $KODE . ',KODE',
            'KODE_VENDOR' => 'required',
            'KETERANGAN' => 'required',
        ], []);

        //if validator fails
        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }

        // Check uniqueness for NAMA case insensitive except for current KODE
        $existingData = Ship::where(DB::raw('LOWER("KAPAL")'), strtolower($request->KAPAL))->where('KODE', '!=', $KODE)->first();
        if ($existingData) {
            return ApiResponse::json(false, ['KAPAL' => ['The KAPAL has already been taken.']], null, 422);
        }
        try {

            $ship = Ship::findOrFail($KODE);
            $ship->KAPAL = $request->KAPAL;
            $ship->KODE_VENDOR = $request->KODE_VENDOR;
            $ship->KETERANGAN = $request->KETERANGAN;

            $ship->save();
            if (!$ship) {
                return ApiResponse::json(false, 'Failed to update data', null, 500);
            }
            $resp_ship = array(
                'KODE' => $ship->KODE,
                'KAPAL' => $ship->KAPAL,
                'NAMA_VENDOR' => Vendor::where('KODE', $ship->KODE_VENDOR)->first()->NAMA,
                'KETERANGAN' => $ship->KETERANGAN,

            );
            return ApiResponse::json(true, 'Ship successfully updated', $resp_ship);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }

    public function destroy($KODE)
    {
        try {
            $ship = Ship::findOrFail($KODE);
            $ship->delete();
            return ApiResponse::json(true, 'Ship successfully deleted', ['KODE' => $KODE]);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to delete ship', null, 500);
        }
    }

    public function trash()
    {
        $ships = Ship::onlyTrashed()->get();

        $resp_ships = [];

        foreach ($ships as $ship) {
            $resp_ship = [
                'KODE' => $ship->KODE,
                'KAPAL' => $ship->KAPAL,
                'NAMA_VENDOR' => Vendor::where('KODE', $ship->KODE_VENDOR)->first()->NAMA,
                'KETERANGAN' => $ship->KETERANGAN,

            ];

            $resp_ships[] = $resp_ship;
        }

        return ApiResponse::json(true, 'Trash bin fetched', $resp_ships);
    }





    public function restore($id)
    {
        $restored = Ship::onlyTrashed()->findOrFail($id);
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
        $maxId = Ship::where('KODE', 'LIKE', 'KPL.%')
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
        $nextId = 'KPL.' . $nextNumber;

        return $nextId;
    }
}
