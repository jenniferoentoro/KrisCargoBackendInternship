<?php

use App\Http\Controllers\AccCustomerController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\AprovalController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BusinessTypeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\CommodityController;
use App\Http\Controllers\ContainerTypeController;
use App\Http\Controllers\CostController;
use App\Http\Controllers\CostGroupController;
use App\Http\Controllers\CostRateController;
use App\Http\Controllers\CostTypeController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerGroupController;
use App\Http\Controllers\FormulaController;
use App\Http\Controllers\GeneralPriceController;
use App\Http\Controllers\HarborController;
use App\Http\Controllers\HppTruckController;
use App\Http\Controllers\JoaController;
use App\Http\Controllers\NegaraController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\OrderTypeController;
use App\Http\Controllers\PictureController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\PraJoaController;
use App\Http\Controllers\PraJoaOtherCostController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProvinceController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ShipController;
use App\Http\Controllers\SizeController;
use App\Http\Controllers\SpecialPriceController;
use App\Http\Controllers\ThcLoloPortDischargeController;
use App\Http\Controllers\ThcLoloPortLoadingController;
use App\Http\Controllers\TruckController;
use App\Http\Controllers\TruckPriceController;
use App\Http\Controllers\TruckRouteController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\VendorTypeController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\ThcLoloController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\WarehouseController;
use App\Models\GeneralPrice;
use App\Models\SpecialPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
Route::group(['prefix' => 'web'], function () {
    Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
    Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
    Route::get('/validate-token', [AuthController::class, 'isLoggedIn'])->name('auth.isLoggedIn');
});


Route::group(['middleware' => ['check.token'], 'prefix' => 'web'], function () {
    //country
    Route::post('/negara/json-adv', [NegaraController::class, 'dataTableAdvJson'])->name('negara.jsonadv');
    Route::post('/negara/json', [NegaraController::class, 'dataTableJson'])->name('negara.json');
    Route::get('/negara/trash', [NegaraController::class, 'trash'])->name('negara.trash');
    Route::get('/negara/get-next-id', [NegaraController::class, 'getNextId'])->name('negara.getNextId');
    Route::get('/negara', [NegaraController::class, 'index'])->name('negara.index');
    Route::get('/negara/{KODE}', [NegaraController::class, 'findByKode'])->name('negara.findByKode');
    Route::post('/negara', [NegaraController::class, 'store'])->name('negara.store');
    Route::put('/negara/{KODE}', [NegaraController::class, 'update'])->name('negara.update');
    Route::delete('/negara/{KODE}', [NegaraController::class, 'destroy'])->name('negara.destroy');
    Route::post('/negara/restore/{KODE}', [NegaraController::class, 'restore'])->name('negara.restore');



    //province
    Route::post('/province/json-adv', [ProvinceController::class, 'dataTableAdvJson'])->name('province.jsonadv');
    Route::post('/province/json', [ProvinceController::class, 'dataTableJson'])->name('province.json');
    Route::get('/province/trash', [ProvinceController::class, 'trash'])->name('province.trash');
    Route::get('/province/get-next-id', [ProvinceController::class, 'getNextId'])->name('province.getNextId');
    Route::post("/province", [ProvinceController::class, "storeWeb"])->name("province.storeWeb");
    // Route::get("/province", [ProvinceController::class, "index"])->name("province.index");
    Route::get("/province", [ProvinceController::class, "indexWeb"])->name("province.indexWeb");
    Route::get("/province/{KODE}", [ProvinceController::class, "findByKode"])->name("province.findByKode");
    Route::put("/province/{KODE}", [ProvinceController::class, "update"])->name("province.update");
    Route::delete('/province/{KODE}', [ProvinceController::class, 'destroy'])->name('province.destroy');
    Route::post('/province/restore/{KODE}', [ProvinceController::class, 'restore'])->name('province.restore');


    //City routes

    Route::post('/city/json-adv', [CityController::class, 'dataTableAdvJson'])->name('city.jsonadv');
    Route::post('/city/json', [CityController::class, 'dataTableJson'])->name('city.json');
    Route::get('/city/dropdown', [CityController::class, 'dropdown'])->name('city.dropdown');

    Route::get('/city/trash', [CityController::class, 'trash'])->name('city.trash');
    Route::get('/city/get-next-id', [CityController::class, 'getNextId'])->name('city.getNextId');
    // Route::get('/city', [CityController::class, 'index'])->name('city.index');
    Route::get('/city', [CityController::class, 'indexWeb'])->name('city.indexWeb');
    Route::post('/city', [CityController::class, 'storeWeb'])->name('city.storeWeb');
    Route::put('/city/{KODE}', [CityController::class, 'update'])->name('city.update');
    Route::delete('/city/{KODE}', [CityController::class, 'destroy'])->name('city.destroy');
    Route::get('/city/{KODE}', [CityController::class, 'findByKode'])->name('city.findByKode');
    Route::post('/city/restore/{KODE}', [CityController::class, 'restore'])->name('city.restore');


    // Customer Group routes
    Route::post('/customer-group/json-adv', [CustomerGroupController::class, 'dataTableAdvJson'])->name('customer-group.jsonadv');
    Route::post('/customer-group/json', [CustomerGroupController::class, 'dataTableJson'])->name('customer-group.json');
    Route::get('/customer-group/trash', [CustomerGroupController::class, 'trash'])->name('customer-group.trash');
    // Route::post('/customer-group/continue', [CustomerGroupController::class, 'storeContinue'])->name('customer-group.storeContinue');
    Route::get('/customer-group/get-next-id', [CustomerGroupController::class, 'getNextId'])->name('customer-group.getNextId');
    // Route::get('/customer-group', [CustomerGroupController::class, 'index'])->name('customer-group.index');
    Route::get('/customer-group', [CustomerGroupController::class, 'indexWeb'])->name('customer-group.indexWeb');
    Route::get('/customer-group/{KODE}', [CustomerGroupController::class, 'findByKode'])->name('customer-group.findByKode');
    Route::post('/customer-group', [CustomerGroupController::class, 'store'])->name('customer-group.store');
    // Route::put('/customer-group/continue/{KODE}', [CustomerGroupController::class, 'updateContinue'])->name('customer-group.updateContinue');
    Route::put('/customer-group/{KODE}', [CustomerGroupController::class, 'update'])->name('customer-group.update');
    Route::delete('/customer-group/{KODE}', [CustomerGroupController::class, 'destroy'])->name('customer-group.destroy');
    Route::post('/customer-group/restore/{KODE}', [CustomerGroupController::class, 'restore'])->name('customer-group.restore');

    // Customer routes
    Route::get('/customer/dropdown', [CustomerController::class, 'dropdown'])->name('customer.dropdown');

    Route::post('/customer/json-adv', [CustomerController::class, 'dataTableAdvJson'])->name('customer.jsonadv');
    Route::post('/customer/json', [CustomerController::class, 'dataTableJson'])->name('customer.json');
    Route::get('/customer/trash', [CustomerController::class, 'trash'])->name('customer.trash');
    Route::get('/customer/get-next-id', [CustomerController::class, 'getNextId'])->name('customer.getNextId');
    Route::post('/customer/restore/{KODE}', [CustomerController::class, 'restore'])->name('customer.restore');
    Route::get('/customer/{KODE}', [CustomerController::class, 'findByKode'])->name('customer.findByKode');
    Route::get('/customer', [CustomerController::class, 'index2'])->name('customer.index');

    Route::post('/customer', [CustomerController::class, 'store'])->name('customer.store');
    Route::post('/customer/{KODE}', [CustomerController::class, 'update'])->name('customer.update');
    Route::delete('/customer/{KODE}', [CustomerController::class, 'destroy'])->name('customer.destroy');



    // harbor routes
    Route::post('/harbor/json-adv', [HarborController::class, 'dataTableAdvJson'])->name('harbor.jsonadv');
    Route::post('/harbor/json', [HarborController::class, 'dataTableJson'])->name('harbor.json');
    Route::get('/harbor/trash', [HarborController::class, 'trash'])->name('harbor.trash');
    Route::get('/harbor/get-next-id', [HarborController::class, 'getNextId'])->name('harbor.getNextId');
    // Route::get('/harbor', [HarborController::class, 'index'])->name('harbor.index');
    Route::get('/harbor', [HarborController::class, 'indexWeb'])->name('harbor.indexWeb');
    Route::post('/harbor', [HarborController::class, 'storeWeb'])->name('harbor.storeWeb');
    Route::put('/harbor/{KODE}', [HarborController::class, 'update'])->name('harbor.update');
    Route::delete('/harbor/{KODE}', [HarborController::class, 'destroy'])->name('harbor.destroy');
    Route::get('/harbor/{KODE}', [HarborController::class, 'findByKode'])->name('harbor.findByKode');
    Route::post('/harbor/restore/{KODE}', [HarborController::class, 'restore'])->name('harbor.restore');



    //vendor type routes
    Route::post('/vendor-type/json-adv', [VendorTypeController::class, 'dataTableAdvJson'])->name('vendor-type.jsonadv');
    Route::post('/vendor-type/json', [VendorTypeController::class, 'dataTableJson'])->name('vendor-type.json');
    Route::get('/vendor-type/trash', [VendorTypeController::class, 'trash'])->name('vendor-type.trash');
    Route::get('/vendor-type/get-next-id', [VendorTypeController::class, 'getNextId'])->name('vendor-type.getNextId');
    Route::get('/vendor-type', [VendorTypeController::class, 'index'])->name('vendor-type.index');
    Route::get('/vendor-type/{KODE}', [VendorTypeController::class, 'findByKode'])->name('vendor-type.findByKode');
    Route::post('/vendor-type', [VendorTypeController::class, 'store'])->name('vendor-type.store');
    Route::put('/vendor-type/{KODE}', [VendorTypeController::class, 'update'])->name('vendor-type.update');
    Route::delete('/vendor-type/{KODE}', [VendorTypeController::class, 'destroy'])->name('vendor-type.destroy');
    Route::post('/vendor-type/restore/{KODE}', [VendorTypeController::class, 'restore'])->name('vendor-type.restore');

    //business type routes
    Route::post('/business-type/json-adv', [BusinessTypeController::class, 'dataTableAdvJson'])->name('business-type.jsonadv');
    Route::post('/business-type/json', [BusinessTypeController::class, 'dataTableJson'])->name('business-type.json');
    Route::get('/business-type/trash', [BusinessTypeController::class, 'trash'])->name('business-type.trash');
    Route::get('/business-type/get-next-id', [BusinessTypeController::class, 'getNextId'])->name('business-type.getNextId');
    Route::get('/business-type', [BusinessTypeController::class, 'index'])->name('business-type.index');
    Route::get('/business-type/{KODE}', [BusinessTypeController::class, 'findByKode'])->name('business-type.findByKode');
    Route::post('/business-type', [BusinessTypeController::class, 'store'])->name('business-type.store');
    Route::put('/business-type/{KODE}', [BusinessTypeController::class, 'update'])->name('business-type.update');
    Route::delete('/business-type/{KODE}', [BusinessTypeController::class, 'destroy'])->name('business-type.destroy');
    Route::post('/business-type/restore/{KODE}', [BusinessTypeController::class, 'restore'])->name('business-type.restore');


    //size type routes
    Route::post('/size/json-adv', [SizeController::class, 'dataTableAdvJson'])->name('size.jsonadv');
    Route::post('/size/json', [SizeController::class, 'dataTableJson'])->name('size.json');
    Route::get('/size/trash', [SizeController::class, 'trash'])->name('size.trash');
    Route::get('/size/get-next-id', [SizeController::class, 'getNextId'])->name('size.getNextId');
    Route::get('/size', [SizeController::class, 'index'])->name('size.index');
    Route::get('/size/{KODE}', [SizeController::class, 'findByKode'])->name('size.findByKode');
    Route::post('/size', [SizeController::class, 'store'])->name('size.store');
    Route::put('/size/{KODE}', [SizeController::class, 'update'])->name('size.update');
    Route::delete('/size/{KODE}', [SizeController::class, 'destroy'])->name('size.destroy');
    Route::post('/size/restore/{KODE}', [SizeController::class, 'restore'])->name('size.restore');


    //container type
    Route::post('/container-type/json-adv', [ContainerTypeController::class, 'dataTableAdvJson'])->name('container-type.jsonadv');
    Route::post('/container-type/json', [ContainerTypeController::class, 'dataTableJson'])->name('container-type.json');
    Route::get('/container-type/trash', [ContainerTypeController::class, 'trash'])->name('container-type.trash');
    Route::get('/container-type/get-next-id', [ContainerTypeController::class, 'getNextId'])->name('container-type.getNextId');
    Route::get('/container-type', [ContainerTypeController::class, 'index'])->name('container-type.index');
    Route::get('/container-type/{KODE}', [ContainerTypeController::class, 'findByKode'])->name('container-type.findByKode');
    Route::post('/container-type', [ContainerTypeController::class, 'store'])->name('container-type.store');
    Route::put('/container-type/{KODE}', [ContainerTypeController::class, 'update'])->name('container-type.update');
    Route::delete('/container-type/{KODE}', [ContainerTypeController::class, 'destroy'])->name('container-type.destroy');
    Route::post('/container-type/restore/{KODE}', [ContainerTypeController::class, 'restore'])->name('container-type.restore');

    //commodity type
    Route::post('/commodity/json-adv', [CommodityController::class, 'dataTableAdvJson'])->name('commodity.jsonadv');
    Route::post('/commodity/json', [CommodityController::class, 'dataTableJson'])->name('commodity.json');
    Route::get('/commodity/trash', [CommodityController::class, 'trash'])->name('commodity.trash');
    Route::get('/commodity/get-next-id', [CommodityController::class, 'getNextId'])->name('commodity.getNextId');
    Route::get('/commodity', [CommodityController::class, 'index'])->name('commodity.index');
    Route::get('/commodity/{KODE}', [CommodityController::class, 'findByKode'])->name('commodity.findByKode');
    Route::post('/commodity', [CommodityController::class, 'store'])->name('commodity.store');
    Route::put('/commodity/{KODE}', [CommodityController::class, 'update'])->name('commodity.update');
    Route::delete('/commodity/{KODE}', [CommodityController::class, 'destroy'])->name('commodity.destroy');
    Route::post('/commodity/restore/{KODE}', [CommodityController::class, 'restore'])->name('commodity.restore');

    //positions
    Route::post('/position/json-adv', [PositionController::class, 'dataTableAdvJson'])->name('position.jsonadv');
    Route::post('/position/json', [PositionController::class, 'dataTableJson'])->name('position.json');
    Route::get('/position/trash', [PositionController::class, 'trash'])->name('position.trash');
    Route::get('/position/get-next-id', [PositionController::class, 'getNextId'])->name('position.getNextId');
    Route::get('/position', [PositionController::class, 'index'])->name('position.index');
    Route::get('/position/{KODE}', [PositionController::class, 'findByKode'])->name('position.findByKode');
    Route::post('/position', [PositionController::class, 'storeWeb'])->name('position.store');
    Route::put('/position/{KODE}', [PositionController::class, 'update'])->name('position.update');
    Route::delete('/position/{KODE}', [PositionController::class, 'destroy'])->name('position.destroy');
    Route::post('/position/restore/{KODE}', [PositionController::class, 'restore'])->name('position.restore');





    //order type routes
    Route::post('/order-type/json-adv', [OrderTypeController::class, 'dataTableAdvJson'])->name('order-type.jsonadv');
    Route::post('/order-type/json', [OrderTypeController::class, 'dataTableJson'])->name('order-type.json');
    Route::get('/order-type/trash', [OrderTypeController::class, 'trash'])->name('order-type.trash');
    Route::get('/order-type/get-next-id', [OrderTypeController::class, 'getNextId'])->name('order-type.getNextId');
    Route::get('/order-type', [OrderTypeController::class, 'index'])->name('order-type.index');
    Route::get('/order-type/{KODE}', [OrderTypeController::class, 'findByKode'])->name('order-type.findByKode');
    Route::post('/order-type', [OrderTypeController::class, 'store'])->name('order-type.store');
    Route::put('/order-type/{KODE}', [OrderTypeController::class, 'update'])->name('order-type.update');
    Route::delete('/order-type/{KODE}', [OrderTypeController::class, 'destroy'])->name('order-type.destroy');
    Route::post('/order-type/restore/{KODE}', [OrderTypeController::class, 'restore'])->name('order-type.restore');

    //service routres
    Route::post('/service/json-adv', [ServiceController::class, 'dataTableAdvJson'])->name('service.jsonadv');
    Route::post('/service/json', [ServiceController::class, 'dataTableJson'])->name('service.json');
    Route::get('/service/trash', [ServiceController::class, 'trash'])->name('service.trash');
    Route::get('/service/get-next-id', [ServiceController::class, 'getNextId'])->name('service.getNextId');
    Route::get('/service', [ServiceController::class, 'index'])->name('service.index');
    Route::get('/service/{KODE}', [ServiceController::class, 'findByKode'])->name('service.findByKode');
    Route::post('/service', [ServiceController::class, 'store'])->name('service.store');
    Route::put('/service/{KODE}', [ServiceController::class, 'update'])->name('service.update');
    Route::delete('/service/{KODE}', [ServiceController::class, 'destroy'])->name('service.destroy');
    Route::post('/service/restore/{KODE}', [ServiceController::class, 'restore'])->name('service.restore');


    //truck routres
    Route::post('/truck/json-adv', [TruckController::class, 'dataTableAdvJson'])->name('truck.jsonadv');
    Route::post('/truck/json', [TruckController::class, 'dataTableJson'])->name('truck.json');
    Route::get('/truck/json', [TruckController::class, 'json'])->name('truck.json');
    Route::get('/truck/trash', [TruckController::class, 'trash'])->name('truck.trash');
    Route::get('/truck/get-next-id', [TruckController::class, 'getNextId'])->name('truck.getNextId');
    Route::get('/truck', [TruckController::class, 'index'])->name('truck.index');
    Route::get('/truck/{KODE}', [TruckController::class, 'findByKode'])->name('truck.findByKode');
    Route::post('/truck', [TruckController::class, 'store'])->name('truck.store');
    Route::put('/truck/{KODE}', [TruckController::class, 'update'])->name('truck.update');
    Route::delete('/truck/{KODE}', [TruckController::class, 'destroy'])->name('truck.destroy');
    Route::post('/truck/restore/{KODE}', [TruckController::class, 'restore'])->name('truck.restore');

    //Cost Group routres
    Route::post('/cost-group/json-adv', [CostGroupController::class, 'dataTableAdvJson'])->name('cost-group.jsonadv');
    Route::post('/cost-group/json', [CostGroupController::class, 'dataTableJson'])->name('cost-group.json');
    Route::get('/cost-group/trash', [CostGroupController::class, 'trash'])->name('cost-group.trash');
    Route::get('/cost-group/get-next-id', [CostGroupController::class, 'getNextId'])->name('cost-group.getNextId');
    Route::get('/cost-group', [CostGroupController::class, 'index'])->name('cost-group.index');
    Route::get('/cost-group/{KODE}', [CostGroupController::class, 'findByKode'])->name('cost-group.findByKode');
    Route::post('/cost-group', [CostGroupController::class, 'store'])->name('cost-group.store');
    Route::put('/cost-group/{KODE}', [CostGroupController::class, 'update'])->name('cost-group.update');
    Route::delete('/cost-group/{KODE}', [CostGroupController::class, 'destroy'])->name('cost-group.destroy');
    Route::post('/cost-group/restore/{KODE}', [CostGroupController::class, 'restore'])->name('cost-group.restore');

    //Cost Type routres
    Route::post('/cost-type/json-adv', [CosttypeController::class, 'dataTableAdvJson'])->name('cost-type.jsonadv');
    Route::post('/cost-type/json', [CostTypeController::class, 'dataTableJson'])->name('cost-type.json');
    Route::get('/cost-type/trash', [CostTypeController::class, 'trash'])->name('cost-type.trash');
    Route::get('/cost-type/get-next-id', [CostTypeController::class, 'getNextId'])->name('cost-type.getNextId');
    Route::get('/cost-type', [CostTypeController::class, 'index'])->name('cost-type.index');
    Route::get('/cost-type/{KODE}', [CostTypeController::class, 'findByKode'])->name('cost-type.findByKode');
    Route::post('/cost-type', [CostTypeController::class, 'store'])->name('cost-type.store');
    Route::put('/cost-type/{KODE}', [CostTypeController::class, 'update'])->name('cost-type.update');
    Route::delete('/cost-type/{KODE}', [CostTypeController::class, 'destroy'])->name('cost-type.destroy');
    Route::post('/cost-type/restore/{KODE}', [CostTypeController::class, 'restore'])->name('cost-type.restore');

    //pitcure routes
    // Route::get('/picture-ktp/{filename}', [PictureController::class, 'showPictureKTP'])->name('picture.showPictureKTP');
    // Route::get('/picture-npwp/{filename}', [PictureController::class, 'showPictureNPWP'])->name('picture.storePictureKTP');

    // truck route routes
    Route::post('/truckroute/json-adv', [TruckRouteController::class, 'dataTableAdvJson'])->name('truckroute.jsonadv');
    Route::post('/truckroute/json', [TruckRouteController::class, 'dataTableJson'])->name('truckroute.json');
    Route::get('/truckroute/trash', [TruckRouteController::class, 'trash'])->name('truckroute.trash');
    Route::get('/truckroute/get-next-id', [TruckRouteController::class, 'getNextId'])->name('truckroute.getNextId');
    // Route::get('/truckroute', [TruckRouteController::class, 'index'])->name('truckroute.index');
    Route::get('/truckroute', [TruckRouteController::class, 'indexWeb'])->name('truckroute.indexWeb');
    Route::post('/truckroute', [TruckRouteController::class, 'storeWeb'])->name('truckroute.storeWeb');
    Route::put('/truckroute/{KODE}', [TruckRouteController::class, 'update'])->name('truckroute.update');
    Route::delete('/truckroute/{KODE}', [TruckRouteController::class, 'destroy'])->name('truckroute.destroy');
    Route::get('/truckroute/{KODE}', [TruckRouteController::class, 'findByKode'])->name('truckroute.findByKode');
    Route::post('/truckroute/restore/{KODE}', [TruckRouteController::class, 'restore'])->name('truckroute.restore');

    // cost routes
    Route::post('/cost/json-adv', [CostController::class, 'dataTableAdvJson'])->name('cost.jsonadv');
    Route::post('/cost/json', [CostController::class, 'dataTableJson'])->name('cost.json');
    Route::get('/cost/trash', [CostController::class, 'trash'])->name('cost.trash');
    Route::get('/cost/get-next-id', [CostController::class, 'getNextId'])->name('cost.getNextId');
    // Route::get('/cost', [CostController::class, 'index'])->name('cost.index');
    Route::get('/cost', [CostController::class, 'indexWeb'])->name('cost.indexWeb');
    Route::post('/cost', [CostController::class, 'storeWeb'])->name('cost.storeWeb');
    Route::put('/cost/{KODE}', [CostController::class, 'update'])->name('cost.update');
    Route::delete('/cost/{KODE}', [CostController::class, 'destroy'])->name('cost.destroy');
    Route::get('/cost/{KODE}', [CostController::class, 'findByKode'])->name('cost.findByKode');
    Route::post('/cost/restore/{KODE}', [CostController::class, 'restore'])->name('cost.restore');

    // truck price route
    Route::get('/truck-price/dropdown', [TruckPriceController::class, 'dropdown'])->name('truck-price.dropdown');
    Route::post('/truck-price/json-adv', [TruckPriceController::class, 'dataTableAdvJson'])->name('truck-price.jsonadv');
    Route::post('/truck-price/json', [TruckPriceController::class, 'dataTableJson'])->name('truck-price.json');
    Route::get('/truck-price/json', [TruckPriceController::class, 'json'])->name('truck-price.json');
    Route::get('/truck-price/trash', [TruckPriceController::class, 'trash'])->name('truck-price.trash');
    Route::get('/truck-price/get-next-id', [TruckPriceController::class, 'getNextId'])->name('truck-price.getNextId');
    // Route::get('/truck-price', [TruckPriceController::class, 'index'])->name('truck-price.index');
    Route::get('/truck-price', [TruckPriceController::class, 'indexWeb'])->name('truck-price.indexWeb');
    Route::post('/truck-price', [TruckPriceController::class, 'storeWeb'])->name('truck-price.storeWeb');
    Route::put('/truck-price/{KODE}', [TruckPriceController::class, 'update'])->name('truck-price.update');
    Route::delete('/truck-price/{KODE}', [TruckPriceController::class, 'destroy'])->name('truck-price.destroy');
    Route::get('/truck-price/{KODE}', [TruckPriceController::class, 'findByKode'])->name('truck-price.findByKode');
    Route::post('/truck-price/restore/{KODE}', [TruckPriceController::class, 'restore'])->name('truck-price.restore');
    Route::get('/files/{filename}', [PictureController::class, 'showFiles'])->name('picture.showFiles');

    // cost rate routes
    Route::get('/cost-rate/dropdown', [CostRateController::class, 'dropdown'])->name('cost-rate.dropdown');
    Route::post('/cost-rate/find-ocean-freight', [CostRateController::class, 'findOceanFreight'])->name('cost-rate.findOceanFreight');
    Route::post('/cost-rate/find-freight-surcharge', [CostRateController::class, 'findFreightSurcharge'])->name('cost-rate.findFreightSurcharge');

    Route::post('/cost-rate/json-adv', [CostRateController::class, 'dataTableAdvJson'])->name('cost-rate.jsonadv');
    Route::post('/cost-rate/json', [CostRateController::class, 'dataTableJson'])->name('cost-rate.json');
    Route::get('/cost-rate/trash', [CostRateController::class, 'trash'])->name('cost-rate.trash');
    Route::get('/cost-rate/get-next-id', [CostRateController::class, 'getNextId'])->name('cost-rate.getNextId');
    // Route::get('/cost-rate', [CostRateController::class, 'index'])->name('cost-rate.index');
    Route::get('/cost-rate', [CostRateController::class, 'indexWeb'])->name('cost-rate.indexWeb');
    Route::post('/cost-rate', [CostRateController::class, 'storeWeb'])->name('cost-rate.storeWeb');
    Route::put('/cost-rate/{KODE}', [CostRateController::class, 'update'])->name('cost-rate.update');
    Route::delete('/cost-rate/{KODE}', [CostRateController::class, 'destroy'])->name('cost-rate.destroy');
    Route::get('/cost-rate/{KODE}', [CostRateController::class, 'findByKode'])->name('cost-rate.findByKode');
    Route::post('/cost-rate/restore/{KODE}', [CostRateController::class, 'restore'])->name('cost-rate.restore');

    Route::post('/vendor/json-adv', [VendorController::class, 'dataTableAdvJson'])->name('vendor.jsonadv');
    Route::post('/vendor/json', [VendorController::class, 'dataTableJson'])->name('vendor.json');
    Route::get('/vendor/trash', [VendorController::class, 'trash'])->name('vendor.trash');
    Route::get('/vendor/get-next-id', [VendorController::class, 'getNextId'])->name('vendor.getNextId');
    Route::get('/vendor', [VendorController::class, 'indexWeb'])->name('vendor.index');
    Route::get('/vendor/{KODE}', [VendorController::class, 'findByKode'])->name('vendor.findByKode');
    Route::post('/vendor', [VendorController::class, 'store'])->name('vendor.store');
    // Route::post('/vendor/continue', [VendorController::class, 'storeContinue'])->name('vendor.storeContinue');
    // Route::post('/vendor/continue/{KODE}', [VendorController::class, 'updateContinue'])->name('vendor.updateContinue');
    Route::post('/vendor/{KODE}', [VendorController::class, 'update'])->name('vendor.update');
    Route::delete('/vendor/{KODE}', [VendorController::class, 'destroy'])->name('vendor.destroy');
    Route::post('/vendor/restore/{KODE}', [VendorController::class, 'restore'])->name('vendor.restore');

    // account routes
    Route::post('/account/json-adv', [AccountController::class, 'dataTableAdvJson'])->name('account.jsonadv');
    Route::post('/account/json', [AccountController::class, 'dataTableJson'])->name('account.json');
    Route::get('/account/trash', [AccountController::class, 'trash'])->name('account.trash');
    Route::get('/account/get-next-id', [AccountController::class, 'getNextId'])->name('account.getNextId');
    Route::get('/account', [AccountController::class, 'indexWeb'])->name('account.indexWeb');
    Route::post('/account', [AccountController::class, 'storeWeb'])->name('account.storeWeb');
    Route::put('/account/{KODE}', [AccountController::class, 'update'])->name('account.update');
    Route::delete('/account/{KODE}', [AccountController::class, 'destroy'])->name('account.destroy');
    Route::get('/account/{KODE}', [AccountController::class, 'findByKode'])->name('account.findByKode');
    Route::post('/account/restore/{KODE}', [AccountController::class, 'restore'])->name('account.restore');

    //formula routres
    Route::post('/formula/json-adv', [FormulaController::class, 'dataTableAdvJson'])->name('formula.jsonadv');
    Route::post('/formula/json', [FormulaController::class, 'dataTableJson'])->name('formula.json');
    Route::get('/formula/trash', [FormulaController::class, 'trash'])->name('formula.trash');
    Route::get('/formula/get-next-id', [FormulaController::class, 'getNextId'])->name('formula.getNextId');
    Route::get('/formula', [FormulaController::class, 'index'])->name('formula.index');
    Route::get('/formula/{KODE}', [FormulaController::class, 'findByKode'])->name('formula.findByKode');
    Route::post('/formula', [FormulaController::class, 'store'])->name('formula.store');
    Route::put('/formula/{KODE}', [FormulaController::class, 'update'])->name('formula.update');
    Route::delete('/formula/{KODE}', [FormulaController::class, 'destroy'])->name('formula.destroy');
    Route::post('/formula/restore/{KODE}', [FormulaController::class, 'restore'])->name('formula.restore');

    //category routres
    Route::post('/category/json-adv', [CategoryController::class, 'dataTableAdvJson'])->name('category.jsonadv');
    Route::post('/category/json', [CategoryController::class, 'dataTableJson'])->name('category.json');
    Route::get('/category/trash', [CategoryController::class, 'trash'])->name('category.trash');
    Route::get('/category/get-next-id', [CategoryController::class, 'getNextId'])->name('category.getNextId');
    Route::get('/category', [CategoryController::class, 'index'])->name('category.index');
    Route::get('/category/{KODE}', [CategoryController::class, 'findByKode'])->name('category.findByKode');
    Route::post('/category', [CategoryController::class, 'store'])->name('category.store');
    Route::put('/category/{KODE}', [CategoryController::class, 'update'])->name('category.update');
    Route::delete('/category/{KODE}', [CategoryController::class, 'destroy'])->name('category.destroy');
    Route::post('/category/restore/{KODE}', [CategoryController::class, 'restore'])->name('category.restore');

    //Unit routres
    Route::post('/unit/json-adv', [UnitController::class, 'dataTableAdvJson'])->name('unit.jsonadv');
    Route::post('/unit/json', [UnitController::class, 'dataTableJson'])->name('unit.json');
    Route::get('/unit/trash', [UnitController::class, 'trash'])->name('unit.trash');
    Route::get('/unit/get-next-id', [UnitController::class, 'getNextId'])->name('unit.getNextId');
    Route::get('/unit', [UnitController::class, 'index'])->name('unit.index');
    Route::get('/unit/{KODE}', [UnitController::class, 'findByKode'])->name('unit.findByKode');
    Route::post('/unit', [UnitController::class, 'store'])->name('unit.store');
    Route::put('/unit/{KODE}', [UnitController::class, 'update'])->name('unit.update');
    Route::delete('/unit/{KODE}', [UnitController::class, 'destroy'])->name('unit.destroy');
    Route::post('/unit/restore/{KODE}', [UnitController::class, 'restore'])->name('unit.restore');
    //product routes
    Route::get('/product/dropdown', [ProductController::class, 'getProductDropdown'])->name('product.getProductDropdown');
    Route::post('/product/json-adv', [ProductController::class, 'dataTableAdvJson'])->name('product.jsonadv');
    Route::post('/product/json', [ProductController::class, 'dataTableJson'])->name('product.json');
    Route::get('/product/trash', [ProductController::class, 'trash'])->name('product.trash');
    Route::get('/product/get-next-id', [ProductController::class, 'getNextId'])->name('product.getNextId');
    Route::get('/product', [ProductController::class, 'index'])->name('product.index');
    Route::get('/product/{KODE}', [ProductController::class, 'findByKode'])->name('product.findByKode');
    Route::post('/product', [ProductController::class, 'store'])->name('product.store');
    Route::put('/product/{KODE}', [ProductController::class, 'update'])->name('product.update');
    Route::delete('/product/{KODE}', [ProductController::class, 'destroy'])->name('product.destroy');
    Route::post('/product/restore/{KODE}', [ProductController::class, 'restore'])->name('product.restore');

    // general routes
    Route::get('/general-price/trash', [GeneralPriceController::class, 'trash'])->name('general-price.trash');
    Route::get('/general-price/get-next-id', [GeneralPriceController::class, 'getNextId'])->name('general-price.getNextId');
    Route::get('/general-price', [GeneralPriceController::class, 'indexWeb'])->name('general-price.indexWeb');
    Route::post('/general-price', [GeneralPriceController::class, 'storeWeb'])->name('general-price.storeWeb');
    Route::put('/general-price/{KODE}', [GeneralPriceController::class, 'update'])->name('general-price.update');
    Route::delete('/general-price/{KODE}', [GeneralPriceController::class, 'destroy'])->name('general-price.destroy');
    Route::get('/general-price/{KODE}', [GeneralPriceController::class, 'findByKode'])->name('general-price.findByKode');
    Route::post('/general-price/restore/{KODE}', [GeneralPriceController::class, 'restore'])->name('general-price.restore');


    // special routes
    Route::get('/special-price/dropdown', [SpecialPriceController::class, 'dropdown'])->name('special-price.dropdown');

    Route::post('/special-price/json-adv', [SpecialPriceController::class, 'dataTableAdvJson'])->name('special-price.jsonadv');
    Route::post('/special-price/json', [SpecialPriceController::class, 'dataTableJson'])->name('special-price.json');
    Route::get('/special-price/trash', [SpecialPriceController::class, 'trash'])->name('special-price.trash');
    Route::get('/special-price/get-next-id', [SpecialPriceController::class, 'getNextId'])->name('special-price.getNextId');
    Route::get('/special-price', [SpecialPriceController::class, 'indexWeb'])->name('special-price.indexWeb');
    Route::post('/special-price', [SpecialPriceController::class, 'storeWeb'])->name('special-price.storeWeb');
    Route::put('/special-price/{KODE}', [SpecialPriceController::class, 'update'])->name('special-price.update');
    Route::delete('/special-price/{KODE}', [SpecialPriceController::class, 'destroy'])->name('special-price.destroy');
    Route::get('/special-price/{KODE}', [SpecialPriceController::class, 'findByKode'])->name('special-price.findByKode');
    Route::post('/special-price/restore/{KODE}', [SpecialPriceController::class, 'restore'])->name('special-price.restore');

    //ship routes
    Route::post('/ship/json-adv', [ShipController::class, 'dataTableAdvJson'])->name('ship.jsonadv');
    Route::post('/ship/json', [ShipController::class, 'dataTableJson'])->name('ship.json');
    Route::get('/ship/trash', [ShipController::class, 'trash'])->name('ship.trash');
    Route::get('/ship/get-next-id', [ShipController::class, 'getNextId'])->name('ship.getNextId');
    Route::get('/ship', [ShipController::class, 'index'])->name('ship.index');
    Route::post('/ship', [ShipController::class, 'storeWeb'])->name('ship.storeWeb');
    Route::put('/ship/{KODE}', [ShipController::class, 'update'])->name('ship.update');
    Route::delete('/ship/{KODE}', [ShipController::class, 'destroy'])->name('ship.destroy');
    Route::get('/ship/{KODE}', [ShipController::class, 'findByKode'])->name('ship.findByKode');
    Route::post('/ship/restore/{KODE}', [ShipController::class, 'restore'])->name('ship.restore');

    //thc lolo port routes
    // Route::post('/thc-lolo-port-loading/json-adv', [ThcLoloPortLoadingController::class, 'dataTableAdvJson'])->name('thc-lolo-port-loading.jsonadv');
    // Route::post('/thc-lolo-port-loading/json', [ThcLoloPortLoadingController::class, 'dataTableJson'])->name('thc-lolo-port-loading.json');
    // Route::get('/thc-lolo-port-loading/trash', [ThcLoloPortLoadingController::class, 'trash'])->name('thc-lolo-port-loading.trash');
    // Route::get('/thc-lolo-port-loading/get-next-id', [ThcLoloPortLoadingController::class, 'getNextId'])->name('thc-lolo-port-loading.getNextId');
    // Route::get('/thc-lolo-port-loading', [ThcLoloPortLoadingController::class, 'index'])->name('thc-lolo-port-loading.index');
    // Route::post('/thc-lolo-port-loading', [ThcLoloPortLoadingController::class, 'storeWeb'])->name('thc-lolo-port-loading.storeWeb');
    // Route::put('/thc-lolo-port-loading/{KODE}', [ThcLoloPortLoadingController::class, 'update'])->name('thc-lolo-port-loading.update');
    // Route::delete('/thc-lolo-port-loading/{KODE}', [ThcLoloPortLoadingController::class, 'destroy'])->name('thc-lolo-port-loading.destroy');
    // Route::get('/thc-lolo-port-loading/{KODE}', [ThcLoloPortLoadingController::class, 'findByKode'])->name('thc-lolo-port-loading.findByKode');
    // Route::post('/thc-lolo-port-loading/restore/{KODE}', [ThcLoloPortLoadingController::class, 'restore'])->name('thc-lolo-port-loading.restore');


    //thc lolo port routes
    // Route::post('/thc-lolo-port-discharge/json-adv', [ThcLoloPortDischargeController::class, 'dataTableAdvJson'])->name('thc-lolo-port-discharge.jsonadv');
    // Route::post('/thc-lolo-port-discharge/json', [ThcLoloPortDischargeController::class, 'dataTableJson'])->name('thc-lolo-port-discharge.json');
    // Route::get('/thc-lolo-port-discharge/trash', [ThcLoloPortDischargeController::class, 'trash'])->name('thc-lolo-port-discharge.trash');
    // Route::get('/thc-lolo-port-discharge/get-next-id', [ThcLoloPortDischargeController::class, 'getNextId'])->name('thc-lolo-port-discharge.getNextId');
    // Route::get('/thc-lolo-port-discharge', [ThcLoloPortDischargeController::class, 'index'])->name('thc-lolo-port-discharge.index');
    // Route::post('/thc-lolo-port-discharge', [ThcLoloPortDischargeController::class, 'storeWeb'])->name('thc-lolo-port-discharge.storeWeb');
    // Route::put('/thc-lolo-port-discharge/{KODE}', [ThcLoloPortDischargeController::class, 'update'])->name('thc-lolo-port-discharge.update');
    // Route::delete('/thc-lolo-port-discharge/{KODE}', [ThcLoloPortDischargeController::class, 'destroy'])->name('thc-lolo-port-discharge.destroy');
    // Route::get('/thc-lolo-port-discharge/{KODE}', [ThcLoloPortDischargeController::class, 'findByKode'])->name('thc-lolo-port-discharge.findByKode');
    // Route::post('/thc-lolo-port-discharge/restore/{KODE}', [ThcLoloPortDischargeController::class, 'restore'])->name('thc-lolo-port-discharge.restore');

    //thc lolo port routes
    Route::post('/thc-lolo/json-adv', [ThcLoloController::class, 'dataTableAdvJson'])->name('thc-lolo.jsonadv');
    Route::post('/thc-lolo/json', [ThcLoloController::class, 'dataTableJson'])->name('thc-lolo.json');
    Route::get('/thc-lolo/trash', [ThcLoloController::class, 'trash'])->name('thc-lolo.trash');
    Route::get('/thc-lolo/get-next-id', [ThcLoloController::class, 'getNextId'])->name('thc-lolo.getNextId');
    Route::get('/thc-lolo', [ThcLoloController::class, 'index'])->name('thc-lolo.index');
    Route::post('/thc-lolo', [ThcLoloController::class, 'storeWeb'])->name('thc-lolo.storeWeb');
    Route::put('/thc-lolo/{KODE}', [ThcLoloController::class, 'update'])->name('thc-lolo.update');
    Route::delete('/thc-lolo/{KODE}', [ThcLoloController::class, 'destroy'])->name('thc-lolo.destroy');
    Route::get('/thc-lolo/{KODE}', [ThcLoloController::class, 'findByKode'])->name('thc-lolo.findByKode');
    Route::post('/thc-lolo/restore/{KODE}', [ThcLoloController::class, 'restore'])->name('thc-lolo.restore');



    // thc lolo port routes
    Route::post('/warehouse/json-adv', [WarehouseController::class, 'dataTableAdvJson'])->name('warehouse.jsonadv');
    Route::post('/warehouse/json', [WarehouseController::class, 'dataTableJson'])->name('warehouse.json');
    Route::get('/warehouse/json', [WarehouseController::class, 'json'])->name('warehouse.json');
    Route::get('/warehouse/trash', [WarehouseController::class, 'trash'])->name('warehouse.trash');
    Route::get('/warehouse/get-next-id', [WarehouseController::class, 'getNextId'])->name('warehouse.getNextId');
    Route::get('/warehouse', [WarehouseController::class, 'index'])->name('warehouse.index');
    Route::post('/warehouse', [WarehouseController::class, 'storeWeb'])->name('warehouse.storeWeb');
    Route::put('/warehouse/{KODE}', [WarehouseController::class, 'update'])->name('warehouse.update');
    Route::delete('/warehouse/{KODE}', [WarehouseController::class, 'destroy'])->name('warehouse.destroy');
    Route::get('/warehouse/{KODE}', [WarehouseController::class, 'findByKode'])->name('warehouse.findByKode');
    Route::post('/warehouse/restore/{KODE}', [WarehouseController::class, 'restore'])->name('warehouse.restore');

    //user routes
    Route::post('/user/json-adv', [UserController::class, 'dataTableAdvJson'])->name('user.jsonadv');
    Route::post('/user/json', [UserController::class, 'dataTableJson'])->name('user.json');
    Route::post('/user/change-password', [UserController::class, 'changePassword'])->name('user.changePassword');
    Route::post('/user/reset-password', [UserController::class, 'resetPassword'])->name('user.resetPassword');
    Route::get('/user/trash', [UserController::class, 'trash'])->name('user.trash');
    Route::get('/user/get-next-id', [UserController::class, 'getNextId'])->name('user.getNextId');
    Route::get('/user', [UserController::class, 'index'])->name('user.index');
    Route::post('/user', [UserController::class, 'store'])->name('user.storeWeb');
    Route::put('/user/{KODE}', [UserController::class, 'update'])->name('user.update');
    Route::delete('/user/{KODE}', [UserController::class, 'destroy'])->name('user.destroy');
    Route::get('/user/{KODE}', [UserController::class, 'findByKode'])->name('user.findByKode');
    Route::post('/user/restore/{KODE}', [UserController::class, 'restore'])->name('user.restore');


    // HPP truck routes
    Route::post('/hpp-truck/find-one', [HppTruckController::class, 'findOne'])->name('hpp-truck.findOne');
    Route::post('/hpp-truck/json-adv', [HppTruckController::class, 'dataTableAdvJson'])->name('hpp-truck.jsonadv');
    Route::post('/hpp-truck/json', [HppTruckController::class, 'dataTableJson'])->name('hpp-truck.json');
    Route::get('/hpp-truck/trash', [HppTruckController::class, 'trash'])->name('hpp-truck.trash');
    Route::get('/hpp-truck/get-next-id', [HppTruckController::class, 'getNextId'])->name('hpp-truck.getNextId');
    // Route::get('/hpp-truck', [HppTruckController::class, 'index'])->name('hpp-truck.index');
    Route::get('/hpp-truck', [HppTruckController::class, 'indexWeb'])->name('hpp-truck.indexWeb');
    Route::post('/hpp-truck', [HppTruckController::class, 'storeWeb'])->name('hpp-truck.storeWeb');
    Route::put('/hpp-truck/{KODE}', [HppTruckController::class, 'update'])->name('hpp-truck.update');
    Route::delete('/hpp-truck/{KODE}', [HppTruckController::class, 'destroy'])->name('hpp-truck.destroy');
    Route::get('/hpp-truck/{KODE}', [HppTruckController::class, 'findByKode'])->name('hpp-truck.findByKode');
    Route::post('/hpp-truck/restore/{KODE}', [HppTruckController::class, 'restore'])->name('hpp-truck.restore');
    Route::get('/staff/jabatan/{KODE}', [StaffController::class, 'findByKodeJabatan'])->name('staff.getByKodeJabatan');

    Route::post('/staff/json-adv', [StaffController::class, 'dataTableAdvJson'])->name('staff.jsonadv');
    Route::post('/staff/json', [StaffController::class, 'dataTableJson'])->name('staff.json');
    Route::get('/staff/trash', [StaffController::class, 'trash'])->name('staff.trash');
    Route::get('/staff/get-next-id', [StaffController::class, 'getNextId'])->name('staff.getNextId');
    Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
    Route::post('/staff', [StaffController::class, 'store'])->name('staff.storeWeb');
    Route::post('/staff/{KODE}', [StaffController::class, 'update'])->name('staff.update');
    Route::delete('/staff/{KODE}', [StaffController::class, 'destroy'])->name('staff.destroy');
    Route::get('/staff/{KODE}', [StaffController::class, 'findByKode'])->name('staff.findByKode');
    Route::post('/staff/restore/{KODE}', [StaffController::class, 'restore'])->name('staff.restore');


    // offer
    Route::get('/offer/dropdown', [OfferController::class, 'dropdown'])->name('offer.dropdown');
    Route::get('/offer/viewDetail/{KODE}', [OfferController::class, 'viewDetail'])->name('offer.viewDetail');
    Route::get('/offer/trash', [OfferController::class, 'trash'])->name('offer.trash');
    Route::get('/offer/get-next-id', [OfferController::class, 'getNextId'])->name('offer.getNextId');
    Route::get('/offer', [OfferController::class, 'indexWeb'])->name('offer.indexWeb');
    Route::post('/offer', [OfferController::class, 'storeWeb'])->name('offer.storeWeb');
    Route::put('/offer/{KODE}', [OfferController::class, 'update'])->name('offer.update');
    Route::delete('/offer/{KODE}', [OfferController::class, 'destroy'])->name('offer.destroy');
    Route::get('/offer/{KODE}', [OfferController::class, 'findByKode'])->name('offer.findByKode');
    Route::post('/offer/restore/{KODE}', [OfferController::class, 'restore'])->name('offer.restore');
    Route::get('/offer/approve-marketing/{KODE}', [OfferController::class, 'approveMarketing'])->name('offer.approveMarketing');

    //pra joa
    Route::post('/pra-joa/json-adv', [PraJoaController::class, 'dataTableAdvJson'])->name('pra-joa.jsonadv');
    Route::post('/pra-joa/json', [PraJoaController::class, 'dataTableJson'])->name('pra-joa.json');
    Route::get('/pra-joa/dropdown', [PraJoaController::class, 'dropdown'])->name('pra-joa.dropdown');
    Route::get('/pra-joa/trash', [PraJoaController::class, 'trash'])->name('pra-joa.trash');
    Route::get('/pra-joa/get-next-id', [PraJoaController::class, 'getNextId'])->name('pra-joa.getNextId');
    Route::get('/pra-joa', [PraJoaController::class, 'indexWeb'])->name('pra-joa.indexWeb');
    Route::post('/pra-joa', [PraJoaController::class, 'storeWeb'])->name('pra-joa.storeWeb');
    Route::put('/pra-joa/{KODE}', [PraJoaController::class, 'update'])->name('pra-joa.update');
    Route::delete('/pra-joa/{KODE}', [PraJoaController::class, 'destroy'])->name('pra-joa.destroy');
    Route::get('/pra-joa/{KODE}', [PraJoaController::class, 'findByKode'])->name('pra-joa.findByKode');
    Route::post('/pra-joa/restore/{KODE}', [PraJoaController::class, 'restore'])->name('pra-joa.restore');
    Route::get('/pra-joa/approve-marketing/{KODE}', [PraJoaController::class, 'approveMarketing'])->name('pra-joa.approveMarketing');


    //approval
    Route::get('/approval/dropdown', [AprovalController::class, 'dropdown'])->name('approval.dropdown');
    Route::get('/approval', [AprovalController::class, 'indexWeb'])->name('approval.indexWeb');
    Route::put('/approval/{KODE}', [AprovalController::class, 'updateSudahJadiJoa'])->name('approval.update');
    Route::put('/approval/unapprove/{KODE}', [AprovalController::class, 'updateUnApproved'])->name('approval.unapprove');
    Route::get('/approval/approved', [AprovalController::class, 'approved'])->name('approval.approved');


    // ACC customer
    Route::get('/acccustomer/dropdown', [AccCustomerController::class, 'dropdown'])->name('acccustomer.dropdown');
    Route::get('/acccustomer', [AccCustomerController::class, 'indexWeb'])->name('acccustomer.indexWeb');
    Route::put('/acccustomer/{KODE}', [AccCustomerController::class, 'updateSudahJadiJoa'])->name('acccustomer.update');
    Route::put('/acccustomer/unapprove/{KODE}', [AccCustomerController::class, 'updateUnApproved'])->name('acccustomer.unapprove');
    Route::get('/acccustomer/approved', [AccCustomerController::class, 'approved'])->name('acccustomer.approved');

    //joa
    Route::get('/joa/dropdown', [PraJoaController::class, 'dropdown'])->name('joa.dropdown');
    Route::get('/joa/trash', [JoaController::class, 'trash'])->name('joa.trash');
    Route::get('/joa/get-next-id', [JoaController::class, 'getNextId'])->name('joa.getNextId');
    Route::get('/joa', [JoaController::class, 'indexWeb'])->name('joa.indexWeb');
    Route::put('/joa/{KODE}', [JoaController::class, 'update'])->name('joa.update');
    Route::delete('/joa/{KODE}', [JoaController::class, 'destroy'])->name('joa.destroy');
    Route::post('/joa/restore/{KODE}', [JoaController::class, 'restore'])->name('joa.restore');
    Route::get('/joa/{KODE}', [JoaController::class, 'findByKode'])->name('joa.findByKode');


    ///pra joa other costs
    // Route::get('/pra-joa-other-costs/trash', [PraJoaOtherCostController::class, 'trash'])->name('pra-joa-other-costs.trash');
    // Route::get('/pra-joa-other-costs/get-next-id', [PraJoaOtherCostController::class, 'getNextId'])->name('pra-joa-other-costs.getNextId');
    // Route::get('/pra-joa-other-costs', [PraJoaOtherCostController::class, 'indexWeb'])->name('pra-joa-other-costs.indexWeb');
    // Route::post('/pra-joa-other-costs', [PraJoaOtherCostController::class, 'storeWeb'])->name('pra-joa-other-costs.storeWeb');
    // Route::put('/pra-joa-other-costs/{KODE}', [PraJoaOtherCostController::class, 'update'])->name('pra-joa-other-costs.update');
    // Route::delete('/pra-joa-other-costs/{KODE}', [PraJoaOtherCostController::class, 'destroy'])->name('pra-joa-other-costs.destroy');
    // Route::get('/pra-joa-other-costs/{KODE}', [PraJoaOtherCostController::class, 'findByKode'])->name('pra-joa-other-costs.findByKode');
    // Route::post('/pra-joa-other-costs/restore/{KODE}', [PraJoaOtherCostController::class, 'restore'])->name('pra-joa-other-costs.restore');
});