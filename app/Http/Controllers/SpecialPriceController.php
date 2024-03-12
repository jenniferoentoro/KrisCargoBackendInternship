<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Category;
use App\Models\City;
use App\Models\Customer;
use App\Models\Formula;
use App\Models\SpecialPrice;
use App\Models\Harbor;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SpecialPriceController extends Controller
{
    public function dataTableJson(Request $request)
    {
        // try {
        $order = $request->input('order')[0];
        $columnIndex = (int) $order['column'];
        $sortDirection = $order['dir'];

        $columns = $request->input('columns');
        $columnToSort = $columns[$columnIndex]['data'];

        $query = SpecialPrice::query();

        $hasSearch = $request->has('search') && !empty($request->input('search')['value']);
        $dateColumns = ["BERLAKU"]; // Add your date column names here
        // Apply search filter if applicable
        if ($hasSearch) {
            $searchValue = strtoupper($request->input('search')['value']);
            $attributes = SpecialPrice::first()->getAttributes(); // Get all attribute names
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
                'product' => [
                    'table' => 'products',
                    'column' => 'NAMA_PRODUK',
                ],
                'product.category' => [
                    'table' => 'categories',
                    'column' => 'NAMA_KATEGORI',
                ],
                'product.formula' => [
                    'table' => 'formulas',
                    'column' => 'NAMA_RUMUS',
                ],
                'pol' => [
                    'table' => 'harbors',
                    'column' => 'NAMA_PELABUHAN',
                ],
                'pod' => [
                    'table' => 'harbors',
                    'column' => 'NAMA_PELABUHAN',
                ],

                'customer' => [
                    'table' => 'customers',
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
            foreach ($allData as $hargakhusus) {
                $produk = Product::where('KODE', $hargakhusus->KODE_PRODUK)->first();
                $hargakhusus->NAMA_PRODUK = $produk->NAMA_PRODUK;
                $pol = Harbor::where('KODE', $hargakhusus->KODE_POL)->first();
                $hargakhusus->NAMA_POL = $pol->NAMA_PELABUHAN;

                $pod = Harbor::where('KODE', $hargakhusus->KODE_POD)->first();
                $hargakhusus->NAMA_POD = $pod->NAMA_PELABUHAN;


                $kode_formula = Formula::where('KODE', $produk->KODE_RUMUS)->first();
                $hargakhusus->NAMA_RUMUS = $kode_formula->NAMA_RUMUS;


                $kode_kategori = Category::where('KODE', $produk->KODE_KATEGORI)->first();
                $hargakhusus->NAMA_KATEGORI = $kode_kategori->NAMA_KATEGORI;

                $customer = Customer::where('KODE', $hargakhusus->KODE_CUSTOMER)->first();
                $hargakhusus->NAMA_CUSTOMER = $customer->NAMA;
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
        $hargakhususs = $allData->slice($skip)->take($limit);



        // ... rest of your code to enrich the data ...
        if (empty($columnToSort)) {
            foreach ($hargakhususs as $hargakhusus) {
                $produk = Product::where('KODE', $hargakhusus->KODE_PRODUK)->first();
                $hargakhusus->NAMA_PRODUK = $produk->NAMA_PRODUK;
                $pol = Harbor::where('KODE', $hargakhusus->KODE_POL)->first();
                $hargakhusus->NAMA_POL = $pol->NAMA_PELABUHAN;

                $pod = Harbor::where('KODE', $hargakhusus->KODE_POD)->first();
                $hargakhusus->NAMA_POD = $pod->NAMA_PELABUHAN;


                $kode_formula = Formula::where('KODE', $produk->KODE_RUMUS)->first();
                $hargakhusus->NAMA_RUMUS = $kode_formula->NAMA_RUMUS;


                $kode_kategori = Category::where('KODE', $produk->KODE_KATEGORI)->first();
                $hargakhusus->NAMA_KATEGORI = $kode_kategori->NAMA_KATEGORI;

                $customer = Customer::where('KODE', $hargakhusus->KODE_CUSTOMER)->first();
                $hargakhusus->NAMA_CUSTOMER = $customer->NAMA;
            }
        }



        // Prepare the response data structure expected by the frontend

        $response = [
            'draw' => $request->input('draw'),
            'recordsTotal' => SpecialPrice::count(),
            'recordsFiltered' => $total_data_after_search,
            'data' => $hargakhususs->values()->toArray(),
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
            2 => ['product', 'NAMA_PRODUK'],
            3 => ['pol', 'NAMA_PELABUHAN'],
            4 => ['pod', 'NAMA_PELABUHAN'],
            5 => ['product.formula', 'NAMA_RUMUS'],
            6 => ['product.category', 'NAMA_KATEGORI'],
            9 => ['customer', 'NAMA'],

        ];
        try {
            $order = $request->input('order')[0];
            $columnIndex = (int) $order['column'];
            $sortDirection = $order['dir'];

            $columns = $request->input('columns');
            $columnToSort = $columns[$columnIndex]['data'];

            $query = SpecialPrice::query();

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
                foreach ($allData as $hargakhusus) {
                    $produk = Product::where('KODE', $hargakhusus->KODE_PRODUK)->first();
                    $hargakhusus->NAMA_PRODUK = $produk->NAMA_PRODUK;
                    $pol = Harbor::where('KODE', $hargakhusus->KODE_POL)->first();
                    $hargakhusus->NAMA_POL = $pol->NAMA_PELABUHAN;

                    $pod = Harbor::where('KODE', $hargakhusus->KODE_POD)->first();
                    $hargakhusus->NAMA_POD = $pod->NAMA_PELABUHAN;


                    $kode_formula = Formula::where('KODE', $produk->KODE_RUMUS)->first();
                    $hargakhusus->NAMA_RUMUS = $kode_formula->NAMA_RUMUS;


                    $kode_kategori = Category::where('KODE', $produk->KODE_KATEGORI)->first();
                    $hargakhusus->NAMA_KATEGORI = $kode_kategori->NAMA_KATEGORI;

                    $customer = Customer::where('KODE', $hargakhusus->KODE_CUSTOMER)->first();
                    $hargakhusus->NAMA_CUSTOMER = $customer->NAMA;
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

            $hargakhususs = $allData->slice($skip)->take($limit);

            if (empty($columnToSort)) {
                foreach ($hargakhususs as $hargakhusus) {
                    $produk = Product::where('KODE', $hargakhusus->KODE_PRODUK)->first();
                    $hargakhusus->NAMA_PRODUK = $produk->NAMA_PRODUK;
                    $pol = Harbor::where('KODE', $hargakhusus->KODE_POL)->first();
                    $hargakhusus->NAMA_POL = $pol->NAMA_PELABUHAN;

                    $pod = Harbor::where('KODE', $hargakhusus->KODE_POD)->first();
                    $hargakhusus->NAMA_POD = $pod->NAMA_PELABUHAN;


                    $kode_formula = Formula::where('KODE', $produk->KODE_RUMUS)->first();
                    $hargakhusus->NAMA_RUMUS = $kode_formula->NAMA_RUMUS;


                    $kode_kategori = Category::where('KODE', $produk->KODE_KATEGORI)->first();
                    $hargakhusus->NAMA_KATEGORI = $kode_kategori->NAMA_KATEGORI;

                    $customer = Customer::where('KODE', $hargakhusus->KODE_CUSTOMER)->first();
                    $hargakhusus->NAMA_CUSTOMER = $customer->NAMA;
                }
            }


            // ... rest of your code to enrich the data ...




            $response = [
                'draw' => $request->input('draw'),
                'recordsTotal' => SpecialPrice::count(),
                'recordsFiltered' => $total_data_after_search,
                'data' => $hargakhususs->values()->toArray(),
            ];

            return ApiResponse::json(true, 'Data retrieved successfully', $response);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }
    public function findByKode($KODE)
    {
        try {
            $hargakhususs = SpecialPrice::where('KODE', $KODE)->first();
            //convert to array
            $hargakhususs = $hargakhususs->toArray();
            return ApiResponse::json(true, 'hargakhusus retrieved successfully',  $hargakhususs);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to retrieve hargakhusus', null, 500);
        }
    }

    public function getKota()
    {
        $kota = City::all();
        return response()->json([
            'kota' => $kota
        ]);
    }

    public function dropdown()
    {

        $products = Product::all();
        //for each product, get the formula, category, and customer
        foreach ($products as $product) {
            $product->NAMA_RUMUS = Formula::where('KODE', $product->KODE_RUMUS)->first()->NAMA_RUMUS;
            $product->NAMA_KATEGORI = Category::where('KODE', $product->KODE_KATEGORI)->first()->NAMA_KATEGORI;
        }
        $products = $products->toArray();
        //get all harbors
        $harbors = Harbor::all();
        $harbors = $harbors->toArray();
        //get all customers
        $customers = Customer::all();
        $customers = $customers->toArray();
        //return all data
        return ApiResponse::json(true, null, [
            'products' => $products,
            'harbors' => $harbors,
            'customers' => $customers,
        ]);
    }

    public function indexWeb()
    {
        try {
            $hargakhususs = SpecialPrice::orderBy('KODE', 'asc')->get();

            foreach ($hargakhususs as $hargakhusus) {
                $produk = Product::where('KODE', $hargakhusus->KODE_PRODUK)->first();
                $hargakhusus->NAMA_PRODUK = $produk->NAMA_PRODUK;
                $pol = Harbor::where('KODE', $hargakhusus->KODE_POL)->first();
                $hargakhusus->NAMA_POL = $pol->NAMA_PELABUHAN;

                $pod = Harbor::where('KODE', $hargakhusus->KODE_POD)->first();
                $hargakhusus->NAMA_POD = $pod->NAMA_PELABUHAN;


                $kode_formula = Formula::where('KODE', $produk->KODE_RUMUS)->first();
                $hargakhusus->NAMA_RUMUS = $kode_formula->NAMA_RUMUS;


                $kode_kategori = Category::where('KODE', $produk->KODE_KATEGORI)->first();
                $hargakhusus->NAMA_KATEGORI = $kode_kategori->NAMA_KATEGORI;

                $customer = Customer::where('KODE', $hargakhusus->KODE_CUSTOMER)->first();
                $hargakhusus->NAMA_CUSTOMER = $customer->NAMA;
            }
            return ApiResponse::json(true, 'Data retrieved successfully',  $hargakhususs);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to retrieve data', null, 500);
        }
    }

    public function storeWeb(Request $request)
    {
        //use validator here
        $validator = Validator::make($request->all(), [

            'KODE_PRODUK' => 'required',
            'KODE_POL' => 'required',
            'KODE_POD' => 'required',

            'HARGA_JUAL' => 'required',
            'BERLAKU' => 'required|date',
            'KODE_CUSTOMER' => 'required',
        ], [
            'KODE_PRODUK.required' => 'Produk harus diisi',
            'KODE_POL.required' => 'POL harus diisi',
            'KODE_POD.required' => 'POD harus diisi',
            'HARGA_JUAL.required' => 'Harga Jual harus diisi',
            'BERLAKU.required' => 'Berlaku harus diisi',
            'KODE_CUSTOMER.required' => 'Customer harus diisi',

        ]);

        //if validator fails
        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }


        try {
            $validatedData = $validator->validated();


            $new_truck_price = new SpecialPrice();
            $new_truck_price->KODE = $this->getLocalNextId();
            $new_truck_price->KODE_PRODUK = $validatedData['KODE_PRODUK'];
            $new_truck_price->KODE_POL = $validatedData['KODE_POL'];
            $new_truck_price->KODE_POD = $validatedData['KODE_POD'];
            $new_truck_price->HARGA_JUAL = $validatedData['HARGA_JUAL'];
            $new_truck_price->BERLAKU = $validatedData['BERLAKU'];
            $new_truck_price->KODE_CUSTOMER = $validatedData['KODE_CUSTOMER'];
            $new_truck_price->save();
            if (!$new_truck_price) {
                return ApiResponse::json(false, 'Failed to insert data', null, 500);
            }
            $id = $new_truck_price->KODE;

            $hargakhusussr = SpecialPrice::where('KODE', $id)->first();

            $resp_hargakhususr = array(
                'KODE' => $hargakhusussr->KODE,

                'NAMA_PRODUK' => Product::where('KODE', $hargakhusussr->KODE_PRODUK)->first()->NAMA_PRODUK,
                'NAMA_POL' => Harbor::where('KODE', $hargakhusussr->KODE_POL)->first()->NAMA_PELABUHAN,
                'NAMA_POD' => Harbor::where('KODE', $hargakhusussr->KODE_POD)->first()->NAMA_PELABUHAN,
                'NAMA_RUMUS' => Formula::where('KODE', Product::where('KODE', $hargakhusussr->KODE_PRODUK)->first()->KODE_RUMUS)->first()->NAMA_RUMUS,
                'NAMA_KATEGORI' => Category::where('KODE', Product::where('KODE', $hargakhusussr->KODE_PRODUK)->first()->KODE_KATEGORI)->first()->NAMA_KATEGORI,
                'HARGA_JUAL' => $hargakhusussr->HARGA_JUAL,
                'BERLAKU' => $hargakhusussr->BERLAKU,
                'NAMA_CUSTOMER' => Customer::where('KODE', $hargakhusussr->KODE_CUSTOMER)->first()->NAMA,
            );


            return ApiResponse::json(true, "Data inserted successfully with KODE $hargakhusussr->KODE", $resp_hargakhususr, 201);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }

    public function update(Request $request, $KODE)
    {
        //use validator here
        $validator = Validator::make($request->all(), [

            'KODE_PRODUK' => 'required',
            'KODE_POL' => 'required',
            'KODE_POD' => 'required',

            'HARGA_JUAL' => 'required',
            'BERLAKU' => 'required|date',
            'KODE_CUSTOMER' => 'required',
        ], [

            'KODE_PRODUK.required' => 'Produk harus diisi',
            'KODE_POL.required' => 'POL harus diisi',
            'KODE_POD.required' => 'POD harus diisi',

            'HARGA_JUAL.required' => 'Harga Jual harus diisi',
            'BERLAKU.required' => 'Berlaku harus diisi',
            'KODE_CUSTOMER.required' => 'Customer harus diisi',
        ]);

        //if validator fails
        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }

        // Check uniqueness for NAMA case insensitive except for current KODE
        // $hargakhususs = SpecialPrice::where(DB::raw('LOWER("NAMA_BIAYA")'), strtolower($request->NAMA_BIAYA))->where('KODE', '!=', $KODE)->first();
        // if ($hargakhususs) {
        //     return ApiResponse::json(false, ['NAMA_BIAYA' => ['The NAMA_BIAYA has already been taken.']], null, 422);
        // }
        try {
            $validatedData = $validator->validated();



            $hargakhususs = SpecialPrice::findOrFail($KODE);

            $hargakhususs->KODE_PRODUK = $request->get('KODE_PRODUK');
            $hargakhususs->KODE_POL = $request->get('KODE_POL');
            $hargakhususs->KODE_POD = $request->get('KODE_POD');
            $hargakhususs->HARGA_JUAL = $request->get('HARGA_JUAL');
            $hargakhususs->BERLAKU = $request->get('BERLAKU');
            $hargakhususs->KODE_CUSTOMER = $request->get('KODE_CUSTOMER');
            $hargakhususs->save();
            if (!$hargakhususs) {
                return ApiResponse::json(false, 'Failed to update data', null, 500);
            }

            $hargakhususs = SpecialPrice::where('KODE', $KODE)->first();

            $resp_hargakhusus = array(
                'KODE' => $hargakhususs->KODE,

                'NAMA_PRODUK' => Product::where('KODE', $hargakhususs->KODE_PRODUK)->first()->NAMA_PRODUK,
                'NAMA_POL' => Harbor::where('KODE', $hargakhususs->KODE_POL)->first()->NAMA_PELABUHAN,
                'NAMA_POD' => Harbor::where('KODE', $hargakhususs->KODE_POD)->first()->NAMA_PELABUHAN,
                'NAMA_RUMUS' => Formula::where('KODE', Product::where('KODE', $hargakhususs->KODE_PRODUK)->first()->KODE_RUMUS)->first()->NAMA_RUMUS,
                'NAMA_KATEGORI' => Category::where('KODE', Product::where('KODE', $hargakhususs->KODE_PRODUK)->first()->KODE_KATEGORI)->first()->NAMA_KATEGORI,
                'HARGA_JUAL' => $hargakhususs->HARGA_JUAL,
                'BERLAKU' => $hargakhususs->BERLAKU,
                'NAMA_CUSTOMER' => Customer::where('KODE', $hargakhususs->KODE_CUSTOMER)->first()->NAMA,
            );
            return ApiResponse::json(true, 'hargakhusus successfully updated', $resp_hargakhusus);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }

    public function destroy($KODE)
    {
        try {
            $city = SpecialPrice::findOrFail($KODE);
            $city->delete();
            return ApiResponse::json(true, 'hargakhusus successfully deleted', ['KODE' => $KODE]);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to delete hargakhusus', null, 500);
        }
    }

    public function trash()
    {
        $provinces = SpecialPrice::onlyTrashed()->get();

        $resp_provinces = [];

        foreach ($provinces as $hargakhususs) {

            $resp_province = [
                'KODE' => $hargakhususs->KODE,
                'NAMA_PRODUK' => Product::where('KODE', $hargakhususs->KODE_PRODUK)->first()->NAMA_PRODUK,
                'NAMA_POL' => Harbor::where('KODE', $hargakhususs->KODE_POL)->first()->NAMA_PELABUHAN,
                'NAMA_POD' => Harbor::where('KODE', $hargakhususs->KODE_POD)->first()->NAMA_PELABUHAN,
                'NAMA_RUMUS' => Formula::where('KODE', Product::where('KODE', $hargakhususs->KODE_PRODUK)->first()->KODE_RUMUS)->first()->NAMA_RUMUS,
                'NAMA_KATEGORI' => Category::where('KODE', Product::where('KODE', $hargakhususs->KODE_PRODUK)->first()->KODE_KATEGORI)->first()->NAMA_KATEGORI,

                'HARGA_JUAL' => $hargakhususs->HARGA_JUAL,
                'BERLAKU' => $hargakhususs->BERLAKU,
                'NAMA_CUSTOMER' => Customer::where('KODE', $hargakhususs->KODE_CUSTOMER)->first()->NAMA,
            ];

            $resp_provinces[] = $resp_province;
        }

        return ApiResponse::json(true, 'Trash bin fetched', $resp_provinces);
    }

    public function restore($id)
    {
        $restored = SpecialPrice::onlyTrashed()->findOrFail($id);
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
        $maxId = SpecialPrice::where('KODE', 'LIKE', 'LCL.%')
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
        $nextId = 'LCL.' . $nextNumber;

        return $nextId;
    }
}
