<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\City;
use App\Models\Country;
use App\Models\Harbor;
use App\Models\Province;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class HarborController extends Controller
{
    public function dataTableJson(Request $request)
    {
        // try {
        $order = $request->input('order')[0];
        $columnIndex = (int) $order['column'];
        $sortDirection = $order['dir'];

        $columns = $request->input('columns');
        $columnToSort = $columns[$columnIndex]['data'];

        $query = Harbor::query();

        $hasSearch = $request->has('search') && !empty($request->input('search')['value']);
        // Apply search filter if applicable
        if ($hasSearch) {
            $searchValue = strtoupper($request->input('search')['value']);
            $attributes = Harbor::first()->getAttributes(); // Get all attribute names
            $query->where(function ($q) use ($searchValue, $attributes) {
                foreach ($attributes as $attribute => $value) {
                    $q->orWhere($attribute, 'LIKE', "%$searchValue%");
                }
            });

            $relatedColumns = [

                'city' => [
                    'column' => 'NAMA',
                ],
                'city.province' => [
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
            foreach ($allData as $harbor) {
                $city = City::where('KODE', $harbor->KODE_KOTA)->first();
                $harbor->NAMA_KOTA = $city->NAMA;
                $province = Province::where('KODE', $city->KODE_PROVINSI)->first();
                $harbor->NAMA_PROVINSI = $province->NAMA;
                $country = Country::where('KODE', $province->KODE_NEGARA)->first();
                $harbor->NAMA_NEGARA = $country->NAMA;
            }
            $sortBoolean = $sortDirection === 'asc' ? false : true;
            //check if columnToSort is KODE
            if ($columnIndex === 1) {


                //include the sort boolean
                $allData = $allData->sortBy(function ($data) {
                    return $data->KODE;
                }, SORT_STRING, $sortBoolean);
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
        $harbors = $allData->slice($skip)->take($limit);



        // ... rest of your code to enrich the data ...
        if (empty($columnToSort)) {
            foreach ($harbors as $harbor) {
                $city = City::where('KODE', $harbor->KODE_KOTA)->first();
                $harbor->NAMA_KOTA = $city->NAMA;
                $province = Province::where('KODE', $city->KODE_PROVINSI)->first();
                $harbor->NAMA_PROVINSI = $province->NAMA;
                $country = Country::where('KODE', $province->KODE_NEGARA)->first();
                $harbor->NAMA_NEGARA = $country->NAMA;
            }
        }



        // Prepare the response data structure expected by the frontend

        $response = [
            'draw' => $request->input('draw'),
            'recordsTotal' => Harbor::count(),
            'recordsFiltered' => $total_data_after_search,
            'data' => $harbors->values()->toArray(),
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
            3 => ['city', 'NAMA'],
            4 => ['city.province', 'NAMA'],

        ];
        try {
            $order = $request->input('order')[0];
            $columnIndex = (int) $order['column'];
            $sortDirection = $order['dir'];

            $columns = $request->input('columns');
            $columnToSort = $columns[$columnIndex]['data'];

            $query = Harbor::query();

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

            $query->orderByRaw('CAST(SUBSTRING("KODE" FROM \'\\.([0-9]+)$\') AS INTEGER) ASC, "KODE" ASC');

            $allData = $query->get();



            if (!empty($columnToSort)) {
                foreach ($allData as $harbor) {
                    $city = City::where('KODE', $harbor->KODE_KOTA)->first();
                    $harbor->NAMA_KOTA = $city->NAMA;
                    $province = Province::where('KODE', $city->KODE_PROVINSI)->first();
                    $harbor->NAMA_PROVINSI = $province->NAMA;
                    $country = Country::where('KODE', $province->KODE_NEGARA)->first();
                    $harbor->NAMA_NEGARA = $country->NAMA;
                }
                $sortBoolean = $sortDirection === 'asc' ? false : true;
                //check if columnToSort is KODE
                if ($columnIndex === 1) {


                    //include the sort boolean
                    $allData = $allData->sortBy(function ($data) {
                        return $data->KODE;
                    }, SORT_STRING, $sortBoolean);
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

            $harbors = $allData->slice($skip)->take($limit);

            if (empty($columnToSort)) {
                foreach ($harbors as $harbor) {
                    $city = City::where('KODE', $harbor->KODE_KOTA)->first();
                    $harbor->NAMA_KOTA = $city->NAMA;
                    $province = Province::where('KODE', $city->KODE_PROVINSI)->first();
                    $harbor->NAMA_PROVINSI = $province->NAMA;
                    $country = Country::where('KODE', $province->KODE_NEGARA)->first();
                    $harbor->NAMA_NEGARA = $country->NAMA;
                }
            }


            // ... rest of your code to enrich the data ...




            $response = [
                'draw' => $request->input('draw'),
                'recordsTotal' => Harbor::count(),
                'recordsFiltered' => $total_data_after_search,
                'data' => $harbors->values()->toArray(),
            ];

            return ApiResponse::json(true, 'Data retrieved successfully', $response);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }
    public function findByKode($KODE)
    {
        try {
            $User = Harbor::where('KODE', $KODE)->first();
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
            $harbors = Harbor::orderBy('KODE', 'asc')->get();

            foreach ($harbors as $harbor) {
                $city = City::where('KODE', $harbor->KODE_KOTA)->first();
                $harbor->NAMA_KOTA = $city->NAMA;
                $province = Province::where('KODE', $city->KODE_PROVINSI)->first();
                $harbor->NAMA_PROVINSI = $province->NAMA;
                $country = Country::where('KODE', $province->KODE_NEGARA)->first();
                $harbor->NAMA_NEGARA = $country->NAMA;
            }
            return ApiResponse::json(true, 'Data retrieved successfully',  $harbors);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to retrieve data', null, 500);
        }
    }

    public function storeWeb(Request $request)
    {
        //use validator here
        $validator = Validator::make($request->all(), [
            //regex 3 A-Z character
            'KODE' => 'required|string|unique:harbors,KODE|regex:/^[A-Z]{3}$/i',
            'NAMA_PELABUHAN' => 'required|unique:harbors,NAMA_PELABUHAN',
            'KODE_KOTA' => 'required',
            'KETERANGAN' => '',
        ], [
            'KODE.regex' => 'KODE must be in format XXX (UPPERCASE), ex: ABC',

            'NAMA_PELABUHAN.required' => 'NAMA_PELABUHAN is required!',
            'KODE_KOTA.required' => 'KODE_KOTA is required!',
        ]);

        //if validator fails
        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }

        //check uniqueness for KODE case insensitive
        $harbor = Harbor::where(DB::raw('LOWER("KODE")'), strtolower($request->KODE))->first();
        if ($harbor) {
            return ApiResponse::json(false, ['KODE' => ['The KODE has already been taken.']], null, 422);
        }

        // Check uniqueness for NAMA case insensitive
        $harbor = Harbor::where(DB::raw('LOWER("NAMA_PELABUHAN")'), strtolower($request->NAMA_PELABUHAN))->first();
        if ($harbor) {
            return ApiResponse::json(false, ['NAMA_PELABUHAN' => ['The NAMA PELABUHAN has already been taken.']], null, 422);
        }
        try {
            $validatedData = $validator->validated();
            $KODE_KOTA = $validatedData['KODE_KOTA'];
            $province = City::where('KODE', $KODE_KOTA)->first();

            if (!$province) {
                return ApiResponse::json(false, 'Invalid KODE_KOTA', null, 400);
            }

            $new_harbor = new Harbor();
            $new_harbor->KODE = $validatedData['KODE'];
            $new_harbor->KODE_KOTA = $KODE_KOTA;
            $new_harbor->NAMA_PELABUHAN = $validatedData['NAMA_PELABUHAN'];
            $new_harbor->KETERANGAN = $validatedData['KETERANGAN'];
            $new_harbor->save();
            if (!$new_harbor) {
                return ApiResponse::json(false, 'Failed to insert data', null, 500);
            }
            $id = $new_harbor->KODE;

            $harborr = Harbor::where('KODE', $id)->first();
            $resp_harborr = array(
                'KODE' => $harborr->KODE,
                'NAMA_PELABUHAN' => $harborr->NAMA_PELABUHAN,
                'NAMA_KOTA' => City::where('KODE', $harborr->KODE_KOTA)->first()->NAMA,
                'NAMA_PROVINSI' => $province->NAMA,
                'KETERANGAN' => $harborr->KETERANGAN,

            );


            return ApiResponse::json(true, "Data inserted successfully with KODE $harborr->KODE", $resp_harborr, 201);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }

    public function update(Request $request, $KODE)
    {
        //use validator here
        $validator = Validator::make($request->all(), [
            //unique except current KODE
            'KODE' => 'required|string|unique:harbors,KODE,' . $KODE . ',KODE|regex:/^[A-Z]{3}$/i',
            'NAMA_PELABUHAN' => 'required|unique:harbors,NAMA_PELABUHAN,' . $KODE . ',KODE',
            'KODE_KOTA' => 'required',
            'KETERANGAN' => '',
        ], [
            'KODE.regex' => 'KODE must be in format XXX (UPPERCASE), ex: ABC',
            'NAMA_PELABUHAN.required' => 'NAMA_PELABUHAN is required!',
            'KODE_KOTA.required' => 'KODE_KOTA is required!',
        ]);

        //if validator fails
        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }

        //check uniqueness for KODE case insensitive except for current KODE
        $harbor = Harbor::where(DB::raw('LOWER("KODE")'), strtolower($request->KODE))->where('KODE', '!=', $KODE)->first();
        if ($harbor) {
            return ApiResponse::json(false, ['KODE' => ['The KODE has already been taken.']], null, 422);
        }

        // Check uniqueness for NAMA case insensitive except for current KODE
        $harbor = Harbor::where(DB::raw('LOWER("NAMA_PELABUHAN")'), strtolower($request->NAMA_PELABUHAN))->where('KODE', '!=', $KODE)->first();
        if ($harbor) {
            return ApiResponse::json(false, ['NAMA_PELABUHAN' => ['The NAMA PELABUHAN has already been taken.']], null, 422);
        }
        try {
            $validatedData = $validator->validated();

            $KODE_KOTA = $validatedData['KODE_KOTA'];
            $province = City::where('KODE', $KODE_KOTA)->first();


            if (!$province) {
                return ApiResponse::json(false, 'Invalid KODE_KOTA', $KODE_KOTA, 400);
            }
            $harbor = Harbor::findOrFail($KODE);
            if ($harbor->hasRelatedRecords()) {
                return ApiResponse::json(false, 'Cannot update pelabuhan because it has related records', null, 400);
            }
            $harbor->KODE = $request->get('KODE');
            $harbor->KODE_KOTA = $request->get('KODE_KOTA');
            $harbor->NAMA_PELABUHAN = $request->get('NAMA_PELABUHAN');
            $harbor->KETERANGAN = $request->get('KETERANGAN');
            $harbor->save();
            if (!$harbor) {
                return ApiResponse::json(false, 'Failed to update data', null, 500);
            }
            $resp_harbor = array(
                'KODE' => $harbor->KODE,
                'NAMA_PELABUHAN' => $harbor->NAMA_PELABUHAN,
                'NAMA_KOTA' => City::where('KODE', $harbor->KODE_KOTA)->first()->NAMA,
                'NAMA_PROVINSI' => $province->NAMA,
                'KETERANGAN' => $harbor->KETERANGAN,
            );
            return ApiResponse::json(true, 'harbor successfully updated', $resp_harbor);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }

    public function destroy($KODE)
    {
        try {
            $city = Harbor::findOrFail($KODE);
            $city->delete();
            return ApiResponse::json(true, 'Harbor successfully deleted', ['KODE' => $KODE]);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e->getMessage(), null, 500);
        }
    }

    public function trash()
    {
        $provinces = Harbor::onlyTrashed()->get();

        $resp_provinces = [];

        foreach ($provinces as $harbor) {
            $city = City::where('KODE', $harbor->KODE_KOTA)->first();
            // get the province based on the city
            $province = Province::where('KODE', $city->KODE_PROVINSI)->first();
            $resp_province = [
                'KODE' => $harbor->KODE,
                'NAMA_PELABUHAN' => $harbor->NAMA_PELABUHAN,
                'NAMA_KOTA' => $city ? $city->NAMA : null,
                'NAMA_PROVINSI' => $province ? $province->NAMA : null,
                'KETERANGAN' => $harbor->KETERANGAN,
            ];

            $resp_provinces[] = $resp_province;
        }

        return ApiResponse::json(true, 'Trash bin fetched', $resp_provinces);
    }

    public function restore($id)
    {
        $restored = Harbor::onlyTrashed()->findOrFail($id);
        $restored->restore();

        return ApiResponse::json(true, 'Restored', ['KODE' => $id]);
    }

    public function getNextId()
    {
        //get max KODE
        $maxKODE = Harbor::withTrashed()->max('KODE');
        //get next KODE
        $nextKODE = $maxKODE + 1;
        return ApiResponse::json(true, 'Data retrieved successfully',  $nextKODE);
    }
}
