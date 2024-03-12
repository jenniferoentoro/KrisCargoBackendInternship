<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Truck;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class TruckController extends Controller
{

    public function dataTableJson(Request $request)
    {
        // try {
        $order = $request->input('order')[0];
        $columnIndex = (int) $order['column'];
        $sortDirection = $order['dir'];

        $columns = $request->input('columns');
        $columnToSort = $columns[$columnIndex]['data'];

        $query = Truck::query();

        $hasSearch = $request->has('search') && !empty($request->input('search')['value']);
        $dateColumns = []; // Add your date column names here
        // Apply search filter if applicable
        if ($hasSearch) {
            $searchValue = strtoupper($request->input('search')['value']);
            $attributes = Truck::first()->getAttributes(); // Get all attribute names
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
        $trucks = $allData->slice($skip)->take($limit);



        // ... rest of your code to enrich the data ...
        if (empty($columnToSort)) {
        }



        // Prepare the response data structure expected by the frontend

        $response = [
            'draw' => $request->input('draw'),
            'recordsTotal' => Truck::count(),
            'recordsFiltered' => $total_data_after_search,
            'data' => $trucks->values()->toArray(),
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

            $query = Truck::query();

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

            $trucks = $allData->slice($skip)->take($limit);

            if (empty($columnToSort)) {
            }


            // ... rest of your code to enrich the data ...




            $response = [
                'draw' => $request->input('draw'),
                'recordsTotal' => Truck::count(),
                'recordsFiltered' => $total_data_after_search,
                'data' => $trucks->values()->toArray(),
            ];

            return ApiResponse::json(true, 'Data retrieved successfully', $response);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }

    public function json(Request $request)
    {
        // try {
        $query = Truck::query();
        $customer = new Truck(); // Create an instance of the Customer model

        $hasSearch = $request->has('search') && !empty($request->input('search')['value']);
        // Apply search filter if applicable
        if ($hasSearch) {
            $searchValue = strtoupper($request->input('search')['value']);
            $attributes = Truck::first()->getAttributes(); // Get all attribute names
            $query->where(function ($q) use ($searchValue, $attributes) {
                foreach ($attributes as $attribute => $value) {
                    $q->orWhere($attribute, 'LIKE', "%$searchValue%");
                }
            });

            $relatedColumns = [];

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


        // Prepare the response data structure expected by the frontend

        $response = [
            'draw' => $request->input('draw'),
            'recordsTotal' => Truck::count(),
            'recordsFiltered' => $total_data_after_search,
            'data' => $customers->toArray(),
        ];



        return ApiResponse::json(true, 'Data retrieved successfully', $response);
    }
    public function findByKode($KODE)
    {
        try {
            $truckT = Truck::where('KODE', $KODE)->first();
            //convert to array
            $truckT = $truckT->toArray();
            return ApiResponse::json(true, 'Truck retrieved successfully',  $truckT);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to retrieve Truck', null, 500);
        }
    }

    public function index()
    {
        try {
            //get all Truck order by KODE
            $truckT = Truck::orderBy('KODE')->get();
            return ApiResponse::json(true, 'Data retrieved successfully',  $truckT);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to retrieve data', null, 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // unique case insensitive
            'NAMA' => 'required|unique:trucks,NAMA',
        ], [
            'NAMA.required' => 'The NAMA field is required.',
            'NAMA.unique' => 'The NAMA has already been taken.',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }

        // Check uniqueness for NAMA case insensitive
        $truckT = Truck::where(DB::raw('LOWER("NAMA")'), strtolower($request->NAMA))->first();
        if ($truckT) {
            return ApiResponse::json(false, ['NAMA' => ['The NAMA has already been taken.']], null, 422);
        }



        try {
            $validatedData = $validator->validated();

            $new_truck = new Truck();
            $new_truck->KODE = $this->getLocalNextId();
            $new_truck->NAMA = $validatedData['NAMA'];
            $new_truck->save();



            if (!$new_truck) {
                return ApiResponse::json(false, 'Failed to insert data', null, 500);
            }
            $resp_containertype = array(
                'KODE' => $new_truck->KODE,
                'NAMA' => $new_truck->NAMA,
            );


            return ApiResponse::json(true, "Data inserted successfully with KODE $new_truck->KODE", $resp_containertype, 201);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e->getMessage(), null, 500);
        }
    }
    public function update(Request $request, $KODE)
    {
        $validator = Validator::make($request->all(), [
            'NAMA' => 'required|unique:trucks,NAMA,' . $KODE . ',KODE',
        ], [
            'NAMA.required' => 'The NAMA field is required.',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }

        // Check uniqueness for NAMA case insensitive except for current KODE
        $truckT = Truck::where(DB::raw('LOWER("NAMA")'), strtolower($request->NAMA))->where('KODE', '!=', $KODE)->first();
        if ($truckT) {
            return ApiResponse::json(false, ['NAMA' => ['The NAMA has already been taken.']], null, 422);
        }



        try {
            $validatedData = $validator->validated();
            $truckT = Truck::findOrFail($KODE);
            if ($truckT->hasRelatedRecords()) {
                return ApiResponse::json(false, 'Cannot update Truck because it has related records', null, 400);
            }
            $truckT->NAMA = $validatedData['NAMA'];
            $truckT->save();
            if (!$truckT) {
                return ApiResponse::json(false, 'Failed to update Truck', null, 500);
            }
            $resp_containertype = array(
                'KODE' => $truckT->KODE,
                'NAMA' => $truckT->NAMA,
            );
            return ApiResponse::json(true, 'Truck successfully updated', $resp_containertype);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to update Truck', null, 500);
        }
    }

    public function destroy($KODE)
    {
        try {
            $truckT = Truck::findOrFail($KODE);
            $truckT->delete();
            return ApiResponse::json(true, 'Truck successfully deleted', ['KODE' => $KODE]);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e->getMessage(), null, 500);
        }
    }

    public function trash()
    {

        $truckTs = Truck::onlyTrashed()->get();

        return ApiResponse::json(true, 'Trash bin fetched',  $truckTs);
    }

    public function restore($id)
    {
        $restored = Truck::onlyTrashed()->findOrFail($id);
        $restored->restore();

        return ApiResponse::json(true, 'Restored', ['KODE' => $id]);
    }

    public function delete($id)
    {
        $post = Truck::onlyTrashed()->findOrFail($id);

        if ($post->cover) {
            Storage::delete('public/' . $post->cover);
        }

        $post->forceDelete();

        return to_route('posts.trash')->with('success', 'Post deleted permanently');
    }



    public function getNextId()
    {
        $nextId = $this->getLocalNextId();

        return ApiResponse::json(true, 'Next KODE retrieved successfully', $nextId);
    }

    public function getLocalNextId()
    {
        // Get the maximum COM.X ID from the database
        $maxId = Truck::where('KODE', 'LIKE', 'JT.%')
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
        $nextId = 'JT.' . $nextNumber;

        return $nextId;
    }
}
