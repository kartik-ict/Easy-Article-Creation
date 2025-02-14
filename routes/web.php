<?php

use App\Http\Controllers\Backend\CategoryController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Backend\ManufacturerController;
use App\Http\Controllers\Backend\ProductController;

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

Route::get('/', 'HomeController@redirectAdmin')->name('index');
Route::get('/home', 'HomeController@index')->name('home');

/**
 * Admin routes
 */
Route::group(['prefix' => 'admin'], function () {
    Route::get('/', 'Backend\DashboardController@index')->name('admin.dashboard');
    Route::resource('roles', 'Backend\RolesController', ['names' => 'admin.roles']);
    Route::resource('users', 'Backend\UsersController', ['names' => 'admin.users']);
    Route::resource('admins', 'Backend\AdminsController', ['names' => 'admin.admins']);


    // Login Routes
    Route::get('/login', 'Backend\Auth\LoginController@showLoginForm')->name('admin.login');
    Route::post('/login/submit', 'Backend\Auth\LoginController@login')->name('admin.login.submit');

    // Logout Routes
    Route::post('/logout/submit', 'Backend\Auth\LoginController@logout')->name('admin.logout.submit');

    // Forget Password Routes
    Route::get('/password/reset', 'Backend\Auth\ForgetPasswordController@showLinkRequestForm')->name('admin.password.request');
    Route::post('/password/reset/submit', 'Backend\Auth\ForgetPasswordController@reset')->name('admin.password.update');
    Route::get('/manufacturers', [ManufacturerController::class, 'index'])->name('admin.manufacturers.index');
    Route::get('/manufacturers/create', [ManufacturerController::class, 'create'])->name('admin.manufacturers.create');
    Route::get('/manufacturers/getAjaxData', [ManufacturerController::class, 'getAjaxData'])->name('admin.manufacturers.getData');
    Route::post('/manufacturers/store', [ManufacturerController::class, 'store'])->name('admin.manufacturers.store');
    Route::patch('/api/manufacturers/{id}', [ManufacturerController::class, 'update'])->name('admin.manufacturers.update');
    Route::get('/admin/manufacturers/{id}/edit', [ManufacturerController::class, 'edit'])->name('admin.manufacturers.edit');
    Route::delete('/api/manufacturers/{id}', [ManufacturerController::class, 'destroy'])->name('admin.manufacturers.delete');
    Route::post('/manufacturers/swSearch', [ManufacturerController::class, 'swSearch'])->name('sw.manufacturers.search');
    Route::post('/category/swSearch', [CategoryController::class, 'swSearch'])->name('sw.category.search');
    Route::post('/category/createCategory', [CategoryController::class, 'createCategory'])->name('sw.create.category');

    // Product Module

    Route::get('/product', [ProductController::class, 'index'])->name('admin.product.index');
    Route::post('/product/search', [ProductController::class, 'search'])->name('product.search');
    Route::get('/product/edit/{id}', [ProductController::class, 'edit'])->name('product.edit');
    Route::put('/product/update/{id}', [ProductController::class, 'update'])->name('product.update');
    Route::get('/product/create', [ProductController::class, 'create'])->name('product.create');
    Route::post('/product/savedata', [ProductController::class, 'SaveData'])->name('product.saveData');
    Route::post('/product/SaveBolData', [ProductController::class, 'SaveBolData'])->name('product.SaveBolData');
    Route::post('/manufacturer/search', [ProductController::class, 'manufacturerSearch'])->name('product.manufacturerSearch');
    Route::post('/sales-channel/search', [ProductController::class, 'searchSalesChannel'])->name('product.salesChannelSearch');
    Route::post('/sales-channel/search', [ProductController::class, 'searchSalesChannel'])->name('product.salesChannelSearch');
    Route::post('/category', [ProductController::class, 'categorySearch'])->name('product.categorySearch');
    Route::post('/fetch-tax-providers', [ProductController::class, 'fetchTaxProviders'])->name('product.fetchTax');
    Route::post('/product/update-stock', [ProductController::class, 'updateStock'])->name('product.update_stock');
    Route::post('/product/propertyGroupSearch', [ProductController::class, 'propertyGroupSearch'])->name('product.propertyGroupSearch');
    Route::post('/product/propertyGroupOptionSearch', [ProductController::class, 'propertyGroupOption'])->name('product.propertyGroupOptionSearch');
    Route::post('/product/propertySave', [ProductController::class, 'savePropertyOption'])->name('product.savePropertyOption');
    Route::post('/product/variantProduct', [ProductController::class, 'saveVariantProduct'])->name('product.saveVariantProduct');
    Route::post('/media/upload', [ProductController::class, 'uploadMedia'])->name('media.upload');
});
