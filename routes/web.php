<?php

use App\Http\Controllers\Backend\Auth\LoginController;
use App\Http\Controllers\Backend\CategoryController;
use App\Http\Controllers\Backend\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Backend\ManufacturerController;
use App\Http\Controllers\Backend\ProductController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

Auth::routes();

Route::get('/', [HomeController::class, 'redirectAdmin'])->name('index');
Route::get('/home', [HomeController::class, 'index'])->name('home');

/**
 * Admin routes
 */
Route::group(['prefix' => 'admin'], function () {
    // Login Routes
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login/submit', [LoginController::class, 'login'])->name('admin.login.submit');
    Route::group(['middleware' => 'auth:admin'], function () {
        Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');

        Route::resource('roles', 'Backend\RolesController', ['names' => 'admin.roles']);
        Route::resource('users', 'Backend\UsersController', ['names' => 'admin.users']);
        Route::resource('admins', 'Backend\AdminsController', ['names' => 'admin.admins']);
        // Logout Routes
        Route::post('/logout/submit', [LoginController::class, 'logout'])->name('admin.logout.submit');

        // Forget Password Routes
        Route::get('/password/reset', 'Backend\Auth\ForgetPasswordController@showLinkRequestForm')->name('admin.password.request');
        Route::post('/password/reset/submit', 'Backend\Auth\ForgetPasswordController@reset')->name('admin.password.update');

        // manufacturers routes
        // Route::get('/manufacturers', [ManufacturerController::class, 'index'])->name('admin.manufacturers.index');
        // Route::get('/manufacturers/create', [ManufacturerController::class, 'create'])->name('admin.manufacturers.create');
        // Route::get('/manufacturers/getAjaxData', [ManufacturerController::class, 'getAjaxData'])->name('admin.manufacturers.getData');
        // Route::post('/manufacturers/store', [ManufacturerController::class, 'store'])->name('admin.manufacturers.store');
        // Route::patch('/api/manufacturers/{id}', [ManufacturerController::class, 'update'])->name('admin.manufacturers.update');
        // Route::get('/admin/manufacturers/{id}/edit', [ManufacturerController::class, 'edit'])->name('admin.manufacturers.edit');
        // Route::delete('/api/manufacturers/{id}', [ManufacturerController::class, 'destroy'])->name('admin.manufacturers.delete');
        // Route::post('/manufacturers/swSearch', [ManufacturerController::class, 'swSearch'])->name('sw.manufacturers.search');
        // Route::post('/category/swSearch', [CategoryController::class, 'swSearch'])->name('sw.category.search');
        // Route::post('/category/createCategory', [CategoryController::class, 'createCategory'])->name('sw.create.category');

        Route::get('/manufacturers', [ManufacturerController::class, 'index'])->name('admin.manufacturers.index')->middleware('can:manufacture.view');
        Route::get('/manufacturers/create', [ManufacturerController::class, 'create'])->name('admin.manufacturers.create')->middleware('can:manufacture.create');
        Route::get('/manufacturers/getAjaxData', [ManufacturerController::class, 'getAjaxData'])->name('admin.manufacturers.getData')->middleware('can:manufacture.view');
        Route::post('/manufacturers/store', [ManufacturerController::class, 'store'])->name('admin.manufacturers.store')->middleware('can:manufacture.create');
        Route::patch('/api/manufacturers/{id}', [ManufacturerController::class, 'update'])->name('admin.manufacturers.update')->middleware('can:manufacture.edit');
        Route::get('/admin/manufacturers/{id}/edit', [ManufacturerController::class, 'edit'])->name('admin.manufacturers.edit')->middleware('can:manufacture.edit');
        Route::delete('/api/manufacturers/{id}', [ManufacturerController::class, 'destroy'])->name('admin.manufacturers.delete')->middleware('can:manufacture.delete');
        Route::post('/manufacturers/swSearch', [ManufacturerController::class, 'swSearch'])->name('sw.manufacturers.search')->middleware('can:manufacture.view');
        Route::post('/category/swSearch', [CategoryController::class, 'swSearch'])->name('sw.category.search')->middleware('can:manufacture.view');
        Route::post('/category/createCategory', [CategoryController::class, 'createCategory'])->name('sw.create.category')->middleware('can:manufacture.create');

        // Product Module
        // Route::get('/product', [ProductController::class, 'index'])->name('admin.product.index');
        // Route::get('/product/list', [ProductController::class, 'list'])->name('admin.product.list');
        // Route::get('/product/getData', [ProductController::class, 'getAjaxData'])->name('admin.product.getData');
        // Route::post('/product/search', [ProductController::class, 'search'])->name('product.search');
        // Route::delete('/product/delete/{id}', [ProductController::class, 'destroy'])->name('admin.product.delete');
        // Route::get('/product/edit/{id}', [ProductController::class, 'edit'])->name('product.edit');
        // Route::put('/product/update/{id}', [ProductController::class, 'update'])->name('product.update');
        // Route::get('/product/create', [ProductController::class, 'create'])->name('product.create');
        // Route::post('/product/savedata', [ProductController::class, 'SaveData'])->name('product.saveData');
        // Route::post('/product/SaveBolData', [ProductController::class, 'SaveBolData'])->name('product.SaveBolData');
        // Route::post('/manufacturer/search', [ProductController::class, 'manufacturerSearch'])->name('product.manufacturerSearch');
        // Route::post('/sales-channel/search', [ProductController::class, 'searchSalesChannel'])->name('product.salesChannelSearch');
        // Route::post('/sales-channel/search', [ProductController::class, 'searchSalesChannel'])->name('product.salesChannelSearch');
        // Route::post('/category', [ProductController::class, 'categorySearch'])->name('product.categorySearch');
        // Route::post('/fetch-tax-providers', [ProductController::class, 'fetchTaxProviders'])->name('product.fetchTax');
        // Route::post('/product/update-stock', [ProductController::class, 'updateStock'])->name('product.update_stock');
        // Route::post('/product/propertyGroupSearch', [ProductController::class, 'propertyGroupSearch'])->name('product.propertyGroupSearch');
        // Route::post('/product/propertyGroupOptionSearch', [ProductController::class, 'propertyGroupOption'])->name('product.propertyGroupOptionSearch');
        // Route::post('/product/propertySave', [ProductController::class, 'savePropertyOption'])->name('product.savePropertyOption');
        // Route::post('/product/variantProduct', [ProductController::class, 'saveVariantProduct'])->name('product.saveVariantProduct');
        // Route::post('/media/upload', [ProductController::class, 'uploadMedia'])->name('media.upload');

        Route::get('/product', [ProductController::class, 'index'])->name('admin.product.index')->middleware(['can:product.create']);
        Route::get('/product/list', [ProductController::class, 'list'])->name('admin.product.list')->middleware('can:product.create');
        Route::get('/product/getData', [ProductController::class, 'getAjaxData'])->name('admin.product.getData')->middleware('can:product.create');
        Route::post('/product/search', [ProductController::class, 'search'])->name('product.search')->middleware('can:product.create');
        Route::delete('/product/delete/{id}', [ProductController::class, 'destroy'])->name('admin.product.delete')->middleware('can:product.create');
        Route::get('/product/edit/{id}', [ProductController::class, 'edit'])->name('product.edit')->middleware('can:product.create');
        Route::put('/product/update/{id}', [ProductController::class, 'update'])->name('product.update')->middleware('can:product.create');
        Route::get('/product/create', [ProductController::class, 'create'])->name('product.create')->middleware('can:product.create');
        Route::post('/product/savedata', [ProductController::class, 'SaveData'])->name('product.saveData')->middleware('can:product.create');
        Route::post('/product/SaveBolData', [ProductController::class, 'SaveBolData'])->name('product.SaveBolData')->middleware('can:product.create');
        Route::post('/manufacturer/search', [ProductController::class, 'manufacturerSearch'])->name('product.manufacturerSearch')->middleware('can:product.create');
        Route::post('/sales-channel/search', [ProductController::class, 'searchSalesChannel'])->name('product.salesChannelSearch')->middleware('can:product.create');
        Route::post('/sales-channel/search', [ProductController::class, 'searchSalesChannel'])->name('product.salesChannelSearch')->middleware('can:product.create');
        Route::post('/category', [ProductController::class, 'categorySearch'])->name('product.categorySearch')->middleware('can:product.create');
        Route::post('/fetch-tax-providers', [ProductController::class, 'fetchTaxProviders'])->name('product.fetchTax')->middleware('can:product.create');
        Route::post('/product/update-stock', [ProductController::class, 'updateStock'])->name('product.update_stock')->middleware('can:product.create');
        Route::post('/product/propertyGroupSearch', [ProductController::class, 'propertyGroupSearch'])->name('product.propertyGroupSearch')->middleware('can:product.create');
        Route::post('/product/propertyGroupOptionSearch', [ProductController::class, 'propertyGroupOption'])->name('product.propertyGroupOptionSearch')->middleware('can:product.create');
        Route::post('/product/propertySave', [ProductController::class, 'savePropertyOption'])->name('product.savePropertyOption')->middleware('can:product.create');
        Route::post('/product/variantProduct', [ProductController::class, 'saveVariantProduct'])->name('product.saveVariantProduct')->middleware('can:product.create');
        Route::post('/media/upload', [ProductController::class, 'uploadMedia'])->name('media.upload')->middleware('can:product.create');
        Route::post('/product/update', [ProductController::class, 'updateProduct'])->name('product.updateProduct')->middleware('can:product.create');

        Route::get('/product/get-custom-fields', [ProductController::class, 'getCustomFieldData'])->name('product.getCustomFieldData')->middleware('can:product.create');

        Route::post('/warehouse', [ProductController::class, 'warehouseSearch'])->name('product.warehouseSearch')->middleware('can:product.create');
        Route::post('/bin-location', [ProductController::class, 'binLocationSearch'])->name('product.binLocationSearch')->middleware('can:product.create');


    });
});
