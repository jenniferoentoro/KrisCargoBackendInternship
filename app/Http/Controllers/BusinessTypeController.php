<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\BusinessType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class BusinessTypeController extends Controller
{


    public function dataTableJson(Request $request)
    {
        try {
            $order = $request->input('order')[0];
            $columnIndex = (int) $order['column'];
            $sortDirection = $order['dir'];

            $columns = $request->input('columns');
            $columnToSort = $columns[$columnIndex]['data'];
            $query = BusinessType::query();

            $hasSearch = $request->has('search') && !empty($request->input('search')['value']);
            // Apply search filter if applicable
            if ($hasSearch) {
                $searchValue = strtoupper($request->input('search')['value']);
                $attributes = BusinessType::first()->getAttributes(); // Get all attribute names
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

            $query->orderByRaw('CAST(SUBSTRING("KODE" FROM \'\\.([0-9]+)$\') AS INTEGER) ASC, "KODE" ASC'); // Order by the numeric part of KODE

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

            $limit = $request->get('length', 10);
            $total_data_after_search = $query->count();
            if ($limit == -1) {
                $limit = $total_data_after_search; // Use the total count after search filters
            }

            // Apply pagination and limit
            $page = $request->get('start', 0) / $limit + 1;
            $skip = ($page - 1) * $limit;
            $businessTypes = $allData->slice($skip)->take($limit);

            if (empty($columnToSort)) {
            }
            // ... rest of your code to enrich the data ...


            // Prepare the response data structure expected by the frontend

            $response = [
                'draw' => $request->input('draw'),
                'recordsTotal' => BusinessType::count(),
                'recordsFiltered' => $total_data_after_search,
                'data' => $businessTypes->values()->toArray(),
            ];



            return ApiResponse::json(true, 'Data retrieved successfully', $response);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }

    public function dataTableAdvJson(Request $request)
    {
        //related to function
        $relationColumnsAndTo = [];
        // try {
        $order = $request->input('order')[0];
        $columnIndex = (int) $order['column'];
        $sortDirection = $order['dir'];

        $columns = $request->input('columns');
        $columnToSort = $columns[$columnIndex]['data'];
        $query = BusinessType::query();

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
                            $query->where($columnName, 'LIKE', "%$searchValue%");
                        }
                    }
                }
            }
        }

        $query->orderByRaw('CAST(SUBSTRING("KODE" FROM \'\\.([0-9]+)$\') AS INTEGER) ASC, "KODE" ASC'); // Order by the numeric part of KODE

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
        $businessTypes = $allData->slice($skip)->take($limit);
        if (empty($columnToSort)) {
        }


        // ... rest of your code to enrich the data ...




        $response = [
            'draw' => $request->input('draw'),
            'recordsTotal' => BusinessType::count(),
            'recordsFiltered' => $total_data_after_search,
            'data' => $businessTypes->values()->toArray(),
        ];

        return ApiResponse::json(true, 'Data retrieved successfully', $response);
        // } catch (\Exception $e) {
        //     return ApiResponse::json(false, $e, null, 500);
        // }
    }


    public function findByKode($KODE)
    {
        try {
            $User = BusinessType::where('KODE', $KODE)->first();
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

    public function index()
    {
        try {
            //get all negara order by KODE
            $businesstype = BusinessType::orderBy('KODE')->get();
            return ApiResponse::json(true, 'Data retrieved successfully',  $businesstype);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to retrieve data', null, 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // unique case insensitive
            'NAMA' => 'required|unique:business_types,NAMA',
        ], [
            'NAMA.required' => 'The NAMA field is required.',
            'NAMA.unique' => 'The NAMA has already been taken.',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }

        // Check uniqueness for NAMA case insensitive
        $businesstype = BusinessType::where(DB::raw('LOWER("NAMA")'), strtolower($request->NAMA))->first();
        if ($businesstype) {
            return ApiResponse::json(false, ['NAMA' => ['The NAMA has already been taken.']], null, 422);
        }



        try {
            $validatedData = $validator->validated();

            $new_businesstype = new BusinessType();
            $new_businesstype->KODE = $this->getLocalNextId();
            $new_businesstype->NAMA = $validatedData['NAMA'];
            $new_businesstype->save();



            if (!$new_businesstype) {
                return ApiResponse::json(false, 'Failed to insert data', null, 500);
            }
            $resp_negara = array(
                'KODE' => $new_businesstype->KODE,
                'NAMA' => $new_businesstype->NAMA,
            );


            return ApiResponse::json(true, "Data inserted successfully with KODE $new_businesstype->KODE", $resp_negara, 201);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e->getMessage(), null, 500);
        }
    }
    public function update(Request $request, $KODE)
    {
        $validator = Validator::make($request->all(), [
            'NAMA' => 'required|unique:business_types,NAMA,' . $KODE . ',KODE',
        ], [
            'NAMA.required' => 'The NAMA field is required.',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }

        // Check uniqueness for NAMA case insensitive except for current KODE
        $businesstype = BusinessType::where(DB::raw('LOWER("NAMA")'), strtolower($request->NAMA))->where('KODE', '!=', $KODE)->first();
        if ($businesstype) {
            return ApiResponse::json(false, ['NAMA' => ['The NAMA has already been taken.']], null, 422);
        }



        try {
            $validatedData = $validator->validated();
            $businesstype = BusinessType::findOrFail($KODE);
            if ($businesstype->hasRelatedRecords()) {
                return ApiResponse::json(false, 'Cannot update businesstype because it has related records', null, 400);
            }
            $businesstype->NAMA = $validatedData['NAMA'];
            $businesstype->save();
            if (!$businesstype) {
                return ApiResponse::json(false, 'Failed to update Business Type', null, 500);
            }
            $resp_businesstype = array(
                'KODE' => $businesstype->KODE,
                'NAMA' => $businesstype->NAMA,
            );
            return ApiResponse::json(true, 'Business Type successfully updated', $resp_businesstype);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to update Business Type', null, 500);
        }
    }

    public function destroy($KODE)
    {
        try {
            $businesstype = BusinessType::findOrFail($KODE);
            $businesstype->delete();
            return ApiResponse::json(true, 'Business Type successfully deleted', ['KODE' => $KODE]);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e->getMessage(), null, 500);
        }
    }

    public function trash()
    {

        $businesstype = BusinessType::onlyTrashed()->get();

        return ApiResponse::json(true, 'Trash bin fetched',  $businesstype);
    }

    public function restore($id)
    {
        $restored = BusinessType::onlyTrashed()->findOrFail($id);
        $restored->restore();

        return ApiResponse::json(true, 'Restored', ['KODE' => $id]);
    }

    public function delete($id)
    {
        $post = BusinessType::onlyTrashed()->findOrFail($id);

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
        $maxId = BusinessType::where('KODE', 'LIKE', 'JU.%')
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
        $nextId = 'JU.' . $nextNumber;

        return $nextId;
    }
}
