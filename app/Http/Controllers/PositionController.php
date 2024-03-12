<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PositionController extends Controller
{

    public function dataTableJson(Request $request)
    {
        // try {
        $order = $request->input('order')[0];
        $columnIndex = (int) $order['column'];
        $sortDirection = $order['dir'];

        $columns = $request->input('columns');
        $columnToSort = $columns[$columnIndex]['data'];

        $query = Position::query();

        $hasSearch = $request->has('search') && !empty($request->input('search')['value']);
        $dateColumns = []; // Add your date column names here
        // Apply search filter if applicable
        if ($hasSearch) {
            $searchValue = strtoupper($request->input('search')['value']);
            $attributes = Position::first()->getAttributes(); // Get all attribute names
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

            $relatedColumns = [];

            foreach ($relatedColumns as $relation => $relatedColumn) {
                $query->orWhereHas($relation, function ($q) use ($searchValue, $relatedColumn) {
                    $q->where($relatedColumn['column'], 'LIKE', "%$searchValue%");
                });
            }
        }

        $query->orderByRaw('CAST(SUBSTRING("KODE" FROM \'\\.([0-9]+)$\') AS INTEGER) ASC, "KODE" ASC');

        $allData = $query->get();



        if (!empty($columnToSort)) {

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
        $positions = $allData->slice($skip)->take($limit);



        // ... rest of your code to enrich the data ...
        if (empty($columnToSort)) {
        }



        // Prepare the response data structure expected by the frontend

        $response = [
            'draw' => $request->input('draw'),
            'recordsTotal' => Position::count(),
            'recordsFiltered' => $total_data_after_search,
            'data' => $positions->values()->toArray(),
        ];



        return ApiResponse::json(true, 'Data retrieved successfully', $response);
        // } catch (\Exception $e) {
        //     return ApiResponse::json(false, $e, null, 500);
        // }
    }

    public function dataTableAdvJson(Request $request)
    {
        //related to function
        $relationColumnsAndTo = [];
        try {
            $order = $request->input('order')[0];
            $columnIndex = (int) $order['column'];
            $sortDirection = $order['dir'];

            $columns = $request->input('columns');
            $columnToSort = $columns[$columnIndex]['data'];

            $query = Position::query();

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

            $positions = $allData->slice($skip)->take($limit);

            if (empty($columnToSort)) {
            }


            // ... rest of your code to enrich the data ...




            $response = [
                'draw' => $request->input('draw'),
                'recordsTotal' => Position::count(),
                'recordsFiltered' => $total_data_after_search,
                'data' => $positions->values()->toArray(),
            ];

            return ApiResponse::json(true, 'Data retrieved successfully', $response);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }
    public function findByKode($KODE)
    {
        try {
            $position = Position::where('KODE', $KODE)->first();
            //convert to array
            $position = $position->toArray();
            return ApiResponse::json(true, 'Position retrieved successfully',  $position);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to retrieve Position', null, 500);
        }
    }


    public function index()
    {
        try {
            //order by KODE
            $positions = Position::orderBy('KODE')->get();

            return ApiResponse::json(true, 'Data retrieved successfully',  $positions);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to retrieve data', null, 500);
        }
    }

    public function storeWeb(Request $request)
    {
        //use validator here
        $validator = Validator::make($request->all(), [
            'NAMA' => 'required|unique:positions,NAMA',
            'KETERANGAN' => 'required',
        ], []);


        //if validator fails
        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }

        // Check uniqueness for NAMA case insensitive
        $existingData = Position::where(DB::raw('LOWER("NAMA")'), strtolower($request->NAMA))->first();
        if ($existingData) {
            return ApiResponse::json(false, ['NAMA' => ['The NAMA has already been taken.']], null, 422);
        }


        try {

            $new_Position = new Position();
            $new_Position->KODE = $this->getLocalNextId();
            $new_Position->NAMA = $request->NAMA;
            $new_Position->KETERANGAN = $request->KETERANGAN;


            $new_Position->save();
            if (!$new_Position) {
                return ApiResponse::json(false, 'Failed to insert data', null, 500);
            }
            $id = $new_Position->KODE;

            $position = Position::where('KODE', $id)->first();;


            return ApiResponse::json(true, "Data inserted successfully with KODE $position->KODE", $position, 201);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }

    public function update(Request $request, $KODE)
    {
        //use validator here
        $validator = Validator::make($request->all(), [
            //uunique except current KODE
            'NAMA' => 'required|unique:positions,NAMA,' . $KODE . ',KODE',
            'KETERANGAN' => 'required',
        ], []);

        //if validator fails
        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }

        // Check uniqueness for NAMA case insensitive except for current KODE
        $existingData = Position::where(DB::raw('LOWER("NAMA")'), strtolower($request->NAMA))->where('KODE', '!=', $KODE)->first();
        if ($existingData) {
            return ApiResponse::json(false, ['NAMA' => ['The NAMA has already been taken.']], null, 422);
        }
        try {
            $position = Position::findOrFail($KODE);
            if ($position->hasRelatedRecords()) {
                return ApiResponse::json(false, 'Cannot update position because it has related records', null, 400);
            }
            $position->NAMA = $request->NAMA;
            $position->KETERANGAN = $request->KETERANGAN;
            $position->save();
            if (!$position) {
                return ApiResponse::json(false, 'Failed to update data', null, 500);
            };
            return ApiResponse::json(true, 'Position successfully updated', $position);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }

    public function destroy($KODE)
    {
        try {
            $position = Position::findOrFail($KODE);
            $position->delete();
            return ApiResponse::json(true, 'Position successfully deleted', ['KODE' => $KODE]);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e->getMessage(), null, 500);
        }
    }

    public function trash()
    {
        $positions = Position::onlyTrashed()->get();



        return ApiResponse::json(true, 'Trash bin fetched', $positions);
    }





    public function restore($id)
    {
        $restored = Position::onlyTrashed()->findOrFail($id);
        $restored->restore();

        return ApiResponse::json(true, 'Restored', ['KODE' => $id]);
    }


    // public function getNextId()
    // {

    //     $maxKODE = Position::withTrashed()->max('KODE');
    //     //get next KODE
    //     $nextKODE = $maxKODE + 1;
    //     return ApiResponse::json(true, 'Data retrieved successfully',  $nextKODE);
    // }

    public function getNextId()
    {
        $nextId = $this->getLocalNextId();

        return ApiResponse::json(true, 'Next KODE retrieved successfully', $nextId);
    }

    public function getLocalNextId()
    {
        // Get the maximum COM.X ID from the database
        $maxId = Position::where('KODE', 'LIKE', 'JBT.%')
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
        $nextId = 'JBT.' . $nextNumber;

        return $nextId;
    }
}
