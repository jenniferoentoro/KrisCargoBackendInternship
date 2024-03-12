<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\City;
use App\Models\Formula;
use App\Models\GeneralPrice;
use App\Models\Harbor;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GeneralPriceController extends Controller
{
    public function findByKode($KODE)
    {
        try {
            $User = GeneralPrice::where('KODE', $KODE)->first();
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
            $hargaumums = GeneralPrice::orderBy('KODE', 'asc')->get();

            foreach ($hargaumums as $hargaumum) {
                $produk = Product::where('KODE', $hargaumum->KODE_PRODUK)->first();
                $hargaumum->NAMA_PRODUK = $produk->NAMA_PRODUK;
                $pol = Harbor::where('KODE', $hargaumum->KODE_POL)->first();
                $hargaumum->NAMA_POL = $pol->NAMA_PELABUHAN;

                $pod = Harbor::where('KODE', $hargaumum->KODE_POD)->first();
                $hargaumum->NAMA_POD = $pod->NAMA_PELABUHAN;


                $kode_formula = Formula::where('KODE', $hargaumum->KODE_RUMUS)->first();
                $hargaumum->NAMA_RUMUS = $kode_formula->NAMA_RUMUS;
            }
            return ApiResponse::json(true, 'Data retrieved successfully',  $hargaumums);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to retrieve data', null, 500);
        }
    }

    public function storeWeb(Request $request)
    {
        //use validator here
        $validator = Validator::make($request->all(), [
            'NAMA_HARGA_UMUM' => 'required|unique:general_prices,NAMA_HARGA_UMUM',
            'KODE_PRODUK' => 'required',
            'KODE_POL' => 'required',
            'KODE_POD' => 'required',
            'KODE_RUMUS' => 'required',
            'HARGA_JUAL' => 'required',
            'BERLAKU' => 'required|date',
        ], [
            'NAMA_HARGA_UMUM.required' => 'Nama Harga Umum harus diisi',
            'NAMA_HARGA_UMUM.unique' => 'Nama Harga Umum sudah ada',
            'KODE_PRODUK.required' => 'Produk harus diisi',
            'KODE_POL.required' => 'POL harus diisi',
            'KODE_POD.required' => 'POD harus diisi',
            'KODE_RUMUS.required' => 'Rumus harus diisi',
            'HARGA_JUAL.required' => 'Harga Jual harus diisi',
            'BERLAKU.required' => 'Berlaku harus diisi',
        ]);

        //if validator fails
        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }


        try {
            $validatedData = $validator->validated();


            $new_truck_price = new GeneralPrice();
            $new_truck_price->KODE = GeneralPrice::withTrashed()->max('KODE') + 1;
            $new_truck_price->NAMA_HARGA_UMUM = $validatedData['NAMA_HARGA_UMUM'];
            $new_truck_price->KODE_PRODUK = $validatedData['KODE_PRODUK'];
            $new_truck_price->KODE_POL = $validatedData['KODE_POL'];
            $new_truck_price->KODE_POD = $validatedData['KODE_POD'];
            $new_truck_price->KODE_RUMUS = $validatedData['KODE_RUMUS'];
            $new_truck_price->HARGA_JUAL = $validatedData['HARGA_JUAL'];
            $new_truck_price->BERLAKU = $validatedData['BERLAKU'];
            $new_truck_price->save();
            if (!$new_truck_price) {
                return ApiResponse::json(false, 'Failed to insert data', null, 500);
            }
            $id = $new_truck_price->KODE;

            $hargaumumsr = GeneralPrice::where('KODE', $id)->first();

            $resp_hargaumumr = array(
                'KODE' => $hargaumumsr->KODE,
                'NAMA_HARGA_UMUM' => $hargaumumsr->NAMA_HARGA_UMUM,
                'NAMA_PRODUK' => Product::where('KODE', $hargaumumsr->KODE_PRODUK)->first()->NAMA_PRODUK,
                'NAMA_POL' => Harbor::where('KODE', $hargaumumsr->KODE_POL)->first()->NAMA_PELABUHAN,
                'NAMA_POD' => Harbor::where('KODE', $hargaumumsr->KODE_POD)->first()->NAMA_PELABUHAN,
                'NAMA_RUMUS' => Formula::where('KODE', $hargaumumsr->KODE_RUMUS)->first()->NAMA_RUMUS,
                'HARGA_JUAL' => $hargaumumsr->HARGA_JUAL,
                'BERLAKU' => $hargaumumsr->BERLAKU,
            );


            return ApiResponse::json(true, "Data inserted successfully with KODE $hargaumumsr->KODE", $resp_hargaumumr, 201);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }

    public function update(Request $request, $KODE)
    {
        //use validator here
        $validator = Validator::make($request->all(), [
            'NAMA_HARGA_UMUM' => 'required|unique:general_prices,NAMA_HARGA_UMUM,' . $KODE . ',KODE',
            'KODE_PRODUK' => 'required',
            'KODE_POL' => 'required',
            'KODE_POD' => 'required',
            'KODE_RUMUS' => 'required',
            'HARGA_JUAL' => 'required',
            'BERLAKU' => 'required|date',
        ], [
            'NAMA_HARGA_UMUM.required' => 'Nama Harga Umum harus diisi',
            'NAMA_HARGA_UMUM.unique' => 'Nama Harga Umum sudah ada',
            'KODE_PRODUK.required' => 'Produk harus diisi',
            'KODE_POL.required' => 'POL harus diisi',
            'KODE_POD.required' => 'POD harus diisi',
            'KODE_RUMUS.required' => 'Rumus harus diisi',
            'HARGA_JUAL.required' => 'Harga Jual harus diisi',
            'BERLAKU.required' => 'Berlaku harus diisi',
        ]);

        //if validator fails
        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }

        // Check uniqueness for NAMA case insensitive except for current KODE
        // $hargaumums = GeneralPrice::where(DB::raw('LOWER("NAMA_BIAYA")'), strtolower($request->NAMA_BIAYA))->where('KODE', '!=', $KODE)->first();
        // if ($hargaumums) {
        //     return ApiResponse::json(false, ['NAMA_BIAYA' => ['The NAMA_BIAYA has already been taken.']], null, 422);
        // }
        try {
            $validatedData = $validator->validated();



            $hargaumums = GeneralPrice::findOrFail($KODE);
            $hargaumums->NAMA_HARGA_UMUM = $request->get('NAMA_HARGA_UMUM');
            $hargaumums->KODE_PRODUK = $request->get('KODE_PRODUK');
            $hargaumums->KODE_POL = $request->get('KODE_POL');
            $hargaumums->KODE_POD = $request->get('KODE_POD');
            $hargaumums->KODE_RUMUS = $request->get('KODE_RUMUS');
            $hargaumums->HARGA_JUAL = $request->get('HARGA_JUAL');
            $hargaumums->BERLAKU = $request->get('BERLAKU');
            $hargaumums->save();
            if (!$hargaumums) {
                return ApiResponse::json(false, 'Failed to update data', null, 500);
            }

            $resp_hargaumum = array(
                'KODE' => $hargaumums->KODE,
                'NAMA_HARGA_UMUM' => $hargaumums->NAMA_HARGA_UMUM,
                'NAMA_PRODUK' => Product::where('KODE', $hargaumums->KODE_PRODUK)->first()->NAMA_PRODUK,
                'NAMA_POL' => Harbor::where('KODE', $hargaumums->KODE_POL)->first()->NAMA_PELABUHAN,
                'NAMA_POD' => Harbor::where('KODE', $hargaumums->KODE_POD)->first()->NAMA_PELABUHAN,
                'NAMA_RUMUS' => Formula::where('KODE', $hargaumums->KODE_RUMUS)->first()->NAMA_RUMUS,
                'HARGA_JUAL' => $hargaumums->HARGA_JUAL,
                'BERLAKU' => $hargaumums->BERLAKU,
            );
            return ApiResponse::json(true, 'hargaumum successfully updated', $resp_hargaumum);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }

    public function destroy($KODE)
    {
        try {
            $city = GeneralPrice::findOrFail($KODE);
            $city->delete();
            return ApiResponse::json(true, 'hargaumum successfully deleted', ['KODE' => $KODE]);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to delete hargaumum', null, 500);
        }
    }

    public function trash()
    {
        $provinces = GeneralPrice::onlyTrashed()->get();

        $resp_provinces = [];

        foreach ($provinces as $hargaumums) {

            $resp_province = [
                'KODE' => $hargaumums->KODE,
                'NAMA_HARGA_UMUM' => $hargaumums->NAMA_HARGA_UMUM,
                'NAMA_PRODUK' => Product::where('KODE', $hargaumums->KODE_PRODUK)->first()->NAMA_PRODUK,
                'NAMA_POL' => Harbor::where('KODE', $hargaumums->KODE_POL)->first()->NAMA_PELABUHAN,
                'NAMA_POD' => Harbor::where('KODE', $hargaumums->KODE_POD)->first()->NAMA_PELABUHAN,
                'NAMA_RUMUS' => Formula::where('KODE', $hargaumums->KODE_RUMUS)->first()->NAMA_RUMUS,
                'HARGA_JUAL' => $hargaumums->HARGA_JUAL,
                'BERLAKU' => $hargaumums->BERLAKU,
            ];

            $resp_provinces[] = $resp_province;
        }

        return ApiResponse::json(true, 'Trash bin fetched', $resp_provinces);
    }

    public function restore($id)
    {
        $restored = GeneralPrice::onlyTrashed()->findOrFail($id);
        $restored->restore();

        return ApiResponse::json(true, 'Restored', ['KODE' => $id]);
    }

    public function getNextId()
    {
        //get max KODE
        $maxKODE = GeneralPrice::withTrashed()->max('KODE');
        //get next KODE
        $nextKODE = $maxKODE + 1;
        return ApiResponse::json(true, 'Data retrieved successfully',  $nextKODE);
    }
}
