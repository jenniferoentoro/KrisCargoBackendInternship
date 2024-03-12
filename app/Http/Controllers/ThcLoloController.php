<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\ContainerType;
use App\Models\Harbor;
use App\Models\Size;
use App\Models\ThcLolo;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ThcLoloController extends Controller
{
    public function dataTableJson(Request $request)
    {
        // try {
        $order = $request->input('order')[0];
        $columnIndex = (int) $order['column'];
        $sortDirection = $order['dir'];

        $columns = $request->input('columns');
        $columnToSort = $columns[$columnIndex]['data'];

        $query = ThcLolo::query();

        $hasSearch = $request->has('search') && !empty($request->input('search')['value']);
        $dateColumns = ["TGL_MULAI_BERLAKU", "TGL_AKHIR_BERLAKU"]; // Add your date column names here
        // Apply search filter if applicable
        if ($hasSearch) {
            $searchValue = strtoupper($request->input('search')['value']);
            $attributes = ThcLolo::first()->getAttributes(); // Get all attribute names
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
                'harbor' => [
                    'table' => 'harbors',
                    'column' => 'NAMA_PELABUHAN',
                ],
                'size' => [
                    'table' => 'sizes',
                    'column' => 'NAMA',
                ],
                'containerType' => [
                    'table' => 'container_types',
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
            foreach ($allData as $thclolo) {
                $thclolo->NAMA_VENDOR = Vendor::where('KODE', $thclolo->KODE_VENDOR)->first()->NAMA;
                $thclolo->NAMA_PELABUHAN = Harbor::where('KODE', $thclolo->KODE_PELABUHAN)->first()->NAMA_PELABUHAN;
                $thclolo->NAMA_UK_KONTAINER = Size::where('KODE', $thclolo->KODE_UK_KONTAINER)->first()->NAMA;
                $thclolo->NAMA_JENIS_KONTAINER = ContainerType::where('KODE', $thclolo->KODE_JENIS_KONTAINER)->first()->NAMA;
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
        $thclolos = $allData->slice($skip)->take($limit);



        // ... rest of your code to enrich the data ...
        if (empty($columnToSort)) {
            foreach ($thclolos as $thclolo) {
                $thclolo->NAMA_VENDOR = Vendor::where('KODE', $thclolo->KODE_VENDOR)->first()->NAMA;
                $thclolo->NAMA_PELABUHAN = Harbor::where('KODE', $thclolo->KODE_PELABUHAN)->first()->NAMA_PELABUHAN;
                $thclolo->NAMA_UK_KONTAINER = Size::where('KODE', $thclolo->KODE_UK_KONTAINER)->first()->NAMA;
                $thclolo->NAMA_JENIS_KONTAINER = ContainerType::where('KODE', $thclolo->KODE_JENIS_KONTAINER)->first()->NAMA;
            }
        }



        // Prepare the response data structure expected by the frontend

        $response = [
            'draw' => $request->input('draw'),
            'recordsTotal' => ThcLolo::count(),
            'recordsFiltered' => $total_data_after_search,
            'data' => $thclolos->values()->toArray(),
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
            2 => ['vendor', 'NAMA'],
            3 => ['harbor', 'NAMA_PELABUHAN'],
            4 => ['size', 'NAMA'],
            5 => ['containerType', 'NAMA'],

        ];
        try {
            $order = $request->input('order')[0];
            $columnIndex = (int) $order['column'];
            $sortDirection = $order['dir'];

            $columns = $request->input('columns');
            $columnToSort = $columns[$columnIndex]['data'];

            $query = ThcLolo::query();

            //check for each column hasSearch columns[x][search][value]
            $columns = $request->input('columns');
            $hasSearch = false;
            foreach ($columns as $index => $column) {
                if (isset($column['search']['value']) && !empty($column['search']['value'])) {
                    $hasSearch = true;
                    break;
                }
            }


            $dateColumns = ["TGL_MULAI_BERLAKU", "TGL_AKHIR_BERLAKU"]; // Add your date column names here


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
                foreach ($allData as $thclolo) {
                    $thclolo->NAMA_VENDOR = Vendor::where('KODE', $thclolo->KODE_VENDOR)->first()->NAMA;
                    $thclolo->NAMA_PELABUHAN = Harbor::where('KODE', $thclolo->KODE_PELABUHAN)->first()->NAMA_PELABUHAN;
                    $thclolo->NAMA_UK_KONTAINER = Size::where('KODE', $thclolo->KODE_UK_KONTAINER)->first()->NAMA;
                    $thclolo->NAMA_JENIS_KONTAINER = ContainerType::where('KODE', $thclolo->KODE_JENIS_KONTAINER)->first()->NAMA;
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

            $thclolos = $allData->slice($skip)->take($limit);

            if (empty($columnToSort)) {
                foreach ($thclolos as $thclolo) {
                    $thclolo->NAMA_VENDOR = Vendor::where('KODE', $thclolo->KODE_VENDOR)->first()->NAMA;
                    $thclolo->NAMA_PELABUHAN = Harbor::where('KODE', $thclolo->KODE_PELABUHAN)->first()->NAMA_PELABUHAN;
                    $thclolo->NAMA_UK_KONTAINER = Size::where('KODE', $thclolo->KODE_UK_KONTAINER)->first()->NAMA;
                    $thclolo->NAMA_JENIS_KONTAINER = ContainerType::where('KODE', $thclolo->KODE_JENIS_KONTAINER)->first()->NAMA;
                }
            }


            // ... rest of your code to enrich the data ...




            $response = [
                'draw' => $request->input('draw'),
                'recordsTotal' => ThcLolo::count(),
                'recordsFiltered' => $total_data_after_search,
                'data' => $thclolos->values()->toArray(),
            ];

            return ApiResponse::json(true, 'Data retrieved successfully', $response);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }
    public function findByKode($KODE)
    {
        try {
            $thclolo = ThcLolo::where('KODE', $KODE)->first();
            //convert to array
            $thclolo = $thclolo->toArray();
            return ApiResponse::json(true, 'THC LOLO retrieved successfully',  $thclolo);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to retrieve THC LOLO', null, 500);
        }
    }


    public function index()
    {
        try {
            //order by KODE
            $thclolos = ThcLolo::orderBy('KODE')->get();
            foreach ($thclolos as $thclolo) {
                $thclolo->NAMA_VENDOR = Vendor::where('KODE', $thclolo->KODE_VENDOR)->first()->NAMA;
                $thclolo->NAMA_PELABUHAN = Harbor::where('KODE', $thclolo->KODE_PELABUHAN)->first()->NAMA_PELABUHAN;
                $thclolo->NAMA_UK_KONTAINER = Size::where('KODE', $thclolo->KODE_UK_KONTAINER)->first()->NAMA;
                $thclolo->NAMA_JENIS_KONTAINER = ContainerType::where('KODE', $thclolo->KODE_JENIS_KONTAINER)->first()->NAMA;
            }
            return ApiResponse::json(true, 'Data retrieved successfully',  $thclolos);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to retrieve data', null, 500);
        }
    }

    public function storeWeb(Request $request)
    {
        //use validator here
        $validator = Validator::make($request->all(), [
            'KODE_VENDOR' => 'required',
            'KODE_PELABUHAN' => 'required',
            'KODE_UK_KONTAINER' => 'required',
            'KODE_JENIS_KONTAINER' => 'required',
            'THC' => 'required|numeric',
            'LOLO_LUAR' => 'required|numeric',
            'LOLO_DALAM' => 'required|numeric',
            'TGL_MULAI_BERLAKU' => 'required|date',
            'TGL_AKHIR_BERLAKU' => 'required|date',
        ], [
            'KODE_VENDOR.required' => 'Kode Vendor harus diisi',
            'KODE_PELABUHAN.required' => 'Kode Pelabuhan harus diisi',
            'KODE_UK_KONTAINER.required' => 'Kode Ukuran Kontainer harus diisi',
            'KODE_JENIS_KONTAINER.required' => 'Kode Jenis Kontainer harus diisi',
            'THC.required' => 'THC harus diisi',
            'THC.numeric' => 'THC harus berupa angka',
            'LOLO_LUAR.required' => 'LOLO Luar harus diisi',
            'LOLO_LUAR.numeric' => 'LOLO Luar harus berupa angka',
            'LOLO_DALAM.required' => 'LOLO Dalam harus diisi',
            'LOLO_DALAM.numeric' => 'LOLO Dalam harus berupa angka',
            'TGL_MULAI_BERLAKU.required' => 'Tanggal Mulai Berlaku harus diisi',
            'TGL_MULAI_BERLAKU.date' => 'Tanggal Mulai Berlaku harus berupa tanggal',
            'TGL_AKHIR_BERLAKU.required' => 'Tanggal Akhir Berlaku harus diisi',
            'TGL_AKHIR_BERLAKU.date' => 'Tanggal Akhir Berlaku harus berupa tanggal',
        ]);


        //if validator fails
        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }

        // Check uniqueness for NAMA case insensitive
        // $existingData = ThcLolo::where(DB::raw('LOWER("KAPAL")'), strtolower($request->KAPAL))->first();
        // if ($existingData) {
        //     return ApiResponse::json(false, ['KAPAL' => ['The KAPAL has already been taken.']], null, 422);
        // }


        try {

            $new_ThcLolo = new ThcLolo();
            $new_ThcLolo->KODE = $this->getLocalNextId();
            $new_ThcLolo->KODE_VENDOR = $request->KODE_VENDOR;
            $new_ThcLolo->KODE_PELABUHAN = $request->KODE_PELABUHAN;
            $new_ThcLolo->KODE_UK_KONTAINER = $request->KODE_UK_KONTAINER;
            $new_ThcLolo->KODE_JENIS_KONTAINER = $request->KODE_JENIS_KONTAINER;
            $new_ThcLolo->THC = $request->THC;
            $new_ThcLolo->LOLO_LUAR = $request->LOLO_LUAR;
            $new_ThcLolo->LOLO_DALAM = $request->LOLO_DALAM;
            $new_ThcLolo->TGL_MULAI_BERLAKU = $request->TGL_MULAI_BERLAKU;
            $new_ThcLolo->TGL_AKHIR_BERLAKU = $request->TGL_AKHIR_BERLAKU;


            $new_ThcLolo->save();
            if (!$new_ThcLolo) {
                return ApiResponse::json(false, 'Failed to insert data', null, 500);
            }
            $id = $new_ThcLolo->KODE;

            $thclolo = ThcLolo::where('KODE', $id)->first();
            $resp_ThcLoloPortDischarge = array(
                'KODE' => $thclolo->KODE,
                'NAMA_VENDOR' => Vendor::where('KODE', $thclolo->KODE_VENDOR)->first()->NAMA,
                'NAMA_PELABUHAN' => Harbor::where('KODE', $thclolo->KODE_PELABUHAN)->first()->NAMA_PELABUHAN,
                'NAMA_UK_KONTAINER' => Size::where('KODE', $thclolo->KODE_UK_KONTAINER)->first()->NAMA,
                'NAMA_JENIS_KONTAINER' => ContainerType::where('KODE', $thclolo->KODE_JENIS_KONTAINER)->first()->NAMA,
                'THC' => $thclolo->THC,
                'LOLO_LUAR' => $thclolo->LOLO_LUAR,
                'LOLO_DALAM' => $thclolo->LOLO_DALAM,
                'TGL_MULAI_BERLAKU' => $thclolo->TGL_MULAI_BERLAKU,
                'TGL_AKHIR_BERLAKU' => $thclolo->TGL_AKHIR_BERLAKU,

            );


            return ApiResponse::json(true, "Data inserted successfully with KODE $thclolo->KODE", $resp_ThcLoloPortDischarge, 201);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }

    public function update(Request $request, $KODE)
    {
        //use validator here
        $validator = Validator::make($request->all(), [
            'KODE_VENDOR' => 'required',
            'KODE_PELABUHAN' => 'required',
            'KODE_UK_KONTAINER' => 'required',
            'KODE_JENIS_KONTAINER' => 'required',
            'THC' => 'required|numeric',
            'LOLO_LUAR' => 'required|numeric',
            'LOLO_DALAM' => 'required|numeric',
            'TGL_MULAI_BERLAKU' => 'required|date',
            'TGL_AKHIR_BERLAKU' => 'required|date',
        ], []);

        //if validator fails
        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }

        // Check uniqueness for NAMA case insensitive except for current KODE
        // $existingData = ThcLolo::where(DB::raw('LOWER("KAPAL")'), strtolower($request->KAPAL))->where('KODE', '!=', $KODE)->first();
        // if ($existingData) {
        //     return ApiResponse::json(false, ['KAPAL' => ['The KAPAL has already been taken.']], null, 422);
        // }
        try {

            $thclolo = ThcLolo::findOrFail($KODE);

            $thclolo->KODE_VENDOR = $request->KODE_VENDOR;
            $thclolo->KODE_PELABUHAN = $request->KODE_PELABUHAN;
            $thclolo->KODE_UK_KONTAINER = $request->KODE_UK_KONTAINER;
            $thclolo->KODE_JENIS_KONTAINER = $request->KODE_JENIS_KONTAINER;
            $thclolo->THC = $request->THC;
            $thclolo->LOLO_LUAR = $request->LOLO_LUAR;
            $thclolo->LOLO_DALAM = $request->LOLO_DALAM;
            $thclolo->TGL_MULAI_BERLAKU = $request->TGL_MULAI_BERLAKU;
            $thclolo->TGL_AKHIR_BERLAKU = $request->TGL_AKHIR_BERLAKU;

            $thclolo->save();
            if (!$thclolo) {
                return ApiResponse::json(false, 'Failed to update data', null, 500);
            }

            $thclolo = ThcLolo::where('KODE', $KODE)->first();
            $resp_ThcLoloPortDischarge = array(
                'KODE' => $thclolo->KODE,
                'NAMA_VENDOR' => Vendor::where('KODE', $thclolo->KODE_VENDOR)->first()->NAMA,
                'NAMA_PELABUHAN' => Harbor::where('KODE', $thclolo->KODE_PELABUHAN)->first()->NAMA_PELABUHAN,
                'NAMA_UK_KONTAINER' => Size::where('KODE', $thclolo->KODE_UK_KONTAINER)->first()->NAMA,
                'NAMA_JENIS_KONTAINER' => ContainerType::where('KODE', $thclolo->KODE_JENIS_KONTAINER)->first()->NAMA,
                'THC' => $thclolo->THC,
                'LOLO_LUAR' => $thclolo->LOLO_LUAR,
                'LOLO_DALAM' => $thclolo->LOLO_DALAM,
                'TGL_MULAI_BERLAKU' => $thclolo->TGL_MULAI_BERLAKU,
                'TGL_AKHIR_BERLAKU' => $thclolo->TGL_AKHIR_BERLAKU,


            );
            return ApiResponse::json(true, 'THC LOLO successfully updated', $resp_ThcLoloPortDischarge);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }

    public function destroy($KODE)
    {
        try {
            $thclolo = ThcLolo::findOrFail($KODE);
            $thclolo->delete();
            return ApiResponse::json(true, 'THC LOLO successfully deleted', ['KODE' => $KODE]);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to delete THC LOLO', null, 500);
        }
    }

    public function trash()
    {
        $thclolos = ThcLolo::onlyTrashed()->get();

        $resp_ThcLoloPortDischarges = [];

        foreach ($thclolos as $thclolo) {
            $resp_ThcLoloPortDischarge = [
                'KODE' => $thclolo->KODE,
                'NAMA_VENDOR' => Vendor::where('KODE', $thclolo->KODE_VENDOR)->first()->NAMA,
                'NAMA_PELABUHAN' => Harbor::where('KODE', $thclolo->KODE_PELABUHAN)->first()->NAMA_PELABUHAN,
                'NAMA_UK_KONTAINER' => Size::where('KODE', $thclolo->KODE_UK_KONTAINER)->first()->NAMA,
                'NAMA_JENIS_KONTAINER' => ContainerType::where('KODE', $thclolo->KODE_JENIS_KONTAINER)->first()->NAMA,
                'THC' => $thclolo->THC,
                'LOLO_LUAR' => $thclolo->LOLO_LUAR,
                'LOLO_DALAM' => $thclolo->LOLO_DALAM,
                'TGL_MULAI_BERLAKU' => $thclolo->TGL_MULAI_BERLAKU,
                'TGL_AKHIR_BERLAKU' => $thclolo->TGL_AKHIR_BERLAKU,

            ];

            $resp_ThcLoloPortDischarges[] = $resp_ThcLoloPortDischarge;
        }

        return ApiResponse::json(true, 'Trash bin fetched', $resp_ThcLoloPortDischarges);
    }





    public function restore($id)
    {
        $restored = ThcLolo::onlyTrashed()->findOrFail($id);
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
        $maxId = ThcLolo::where('KODE', 'LIKE', 'TLO.%')
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
        $nextId = 'TLO.' . $nextNumber;

        return $nextId;
    }
}
