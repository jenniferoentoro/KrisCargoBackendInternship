<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Commodity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class CommodityController extends Controller
{

    public function dataTableJson(Request $request)
    {
        try {
            $order = $request->input('order')[0];
            $columnIndex = (int) $order['column'];
            $sortDirection = $order['dir'];

            $columns = $request->input('columns');
            $columnToSort = $columns[$columnIndex]['data'];


            $query = Commodity::query();

            $hasSearch = $request->has('search') && !empty($request->input('search')['value']);
            // Apply search filter if applicable
            if ($hasSearch) {
                $searchValue = strtoupper($request->input('search')['value']);
                $attributes = Commodity::first()->getAttributes(); // Get all attribute names
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

            // Convert the collection of cities to an array

            // Apply pagination and limit to the sorted and converted data
            $limit = $request->get('length', 10);
            $total_data_after_search = $query->count();
            if ($limit == -1) {
                $limit = $total_data_after_search; // Use the total count after search filters
            }
            $page = $request->get('start', 0) / $limit + 1;
            $skip = ($page - 1) * $limit;

            $commodities = $allData->slice($skip)->take($limit);

            if (empty($columnToSort)) {
            }

            // ... (rest of your code to enrich the data)

            $response = [
                'draw' => $request->input('draw'),
                'recordsTotal' => Commodity::count(),
                'recordsFiltered' => $total_data_after_search,
                'data' => $commodities->values()->toArray(),
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
        try {
            $order = $request->input('order')[0];
            $columnIndex = (int) $order['column'];
            $sortDirection = $order['dir'];

            $columns = $request->input('columns');
            $columnToSort = $columns[$columnIndex]['data'];

            $query = Commodity::query();

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

            // Convert the collection of cities to an array
            // Apply pagination and limit
            $limit = $request->get('length', 10);
            $total_data_after_search = $query->count();
            if ($limit == -1) {
                $limit = $total_data_after_search; // Use the total count after search filters
            }
            $page = $request->get('start', 0) / $limit + 1;
            $skip = ($page - 1) * $limit;

            $commodties = $allData->slice($skip)->take($limit);

            if (empty($columnToSort)) {
            }



            // ... rest of your code to enrich the data ...




            $response = [
                'draw' => $request->input('draw'),
                'recordsTotal' => Commodity::count(),
                'recordsFiltered' => $total_data_after_search,
                'data' => $commodties->values()->toArray(),
            ];

            return ApiResponse::json(true, 'Data retrieved successfully', $response);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }
    public function findByKode($KODE)
    {
        try {
            $User = Commodity::where('KODE', $KODE)->first();
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
            //get all Commodity order by KODE
            $commodityT = Commodity::orderBy('KODE')->get();
            return ApiResponse::json(true, 'Data retrieved successfully',  $commodityT);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to retrieve data', null, 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // unique case insensitive
            'NAMA' => 'required|unique:commodities,NAMA',
        ], [
            'NAMA.required' => 'The NAMA field is required.',
            'NAMA.unique' => 'The NAMA has already been taken.',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }

        // Check uniqueness for NAMA case insensitive
        $commodityT = Commodity::where(DB::raw('LOWER("NAMA")'), strtolower($request->NAMA))->first();
        if ($commodityT) {
            return ApiResponse::json(false, ['NAMA' => ['The NAMA has already been taken.']], null, 422);
        }



        try {
            $validatedData = $validator->validated();

            $new_commodity = new Commodity();
            $new_commodity->KODE = $this->getLocalNextId();
            $new_commodity->NAMA = $validatedData['NAMA'];
            $new_commodity->save();



            if (!$new_commodity) {
                return ApiResponse::json(false, 'Failed to insert data', null, 500);
            }
            $resp_containertype = array(
                'KODE' => $new_commodity->KODE,
                'NAMA' => $new_commodity->NAMA,
            );


            return ApiResponse::json(true, "Data inserted successfully with KODE $new_commodity->KODE", $resp_containertype, 201);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e->getMessage(), null, 500);
        }
    }
    public function update(Request $request, $KODE)
    {
        $validator = Validator::make($request->all(), [
            'NAMA' => 'required|unique:commodities,NAMA,' . $KODE . ',KODE',
        ], [
            'NAMA.required' => 'The NAMA field is required.',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }

        // Check uniqueness for NAMA case insensitive except for current KODE
        $commodityT = Commodity::where(DB::raw('LOWER("NAMA")'), strtolower($request->NAMA))->where('KODE', '!=', $KODE)->first();
        if ($commodityT) {
            return ApiResponse::json(false, ['NAMA' => ['The NAMA has already been taken.']], null, 422);
        }



        try {
            $validatedData = $validator->validated();
            $commodityT = Commodity::findOrFail($KODE);
            if ($commodityT->hasRelatedRecords()) {
                return ApiResponse::json(false, 'Cannot commodity because it has related records', null, 400);
            }
            $commodityT->NAMA = $validatedData['NAMA'];
            $commodityT->save();
            if (!$commodityT) {
                return ApiResponse::json(false, 'Failed to update Commodity', null, 500);
            }
            $resp_containertype = array(
                'KODE' => $commodityT->KODE,
                'NAMA' => $commodityT->NAMA,
            );
            return ApiResponse::json(true, 'Commodity successfully updated', $resp_containertype);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to update Commodity', null, 500);
        }
    }

    public function destroy($KODE)
    {
        try {
            $commodityT = Commodity::findOrFail($KODE);
            $commodityT->delete();
            return ApiResponse::json(true, 'Commodity successfully deleted', ['KODE' => $KODE]);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e->getMessage(), null, 500);
        }
    }

    public function trash()
    {

        $commodityTs = Commodity::onlyTrashed()->get();

        return ApiResponse::json(true, 'Trash bin fetched',  $commodityTs);
    }

    public function restore($id)
    {
        $restored = Commodity::onlyTrashed()->findOrFail($id);
        $restored->restore();

        return ApiResponse::json(true, 'Restored', ['KODE' => $id]);
    }

    public function delete($id)
    {
        $post = Commodity::onlyTrashed()->findOrFail($id);

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
        $maxId = Commodity::where('KODE', 'LIKE', 'COM.%')
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
        $nextId = 'COM.' . $nextNumber;

        return $nextId;
    }
}
