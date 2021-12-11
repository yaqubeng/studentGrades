<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;

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

Route::post("upload" , [HomeController::class,'upload']);
Route::post("allfiles" , [HomeController::class,'allfiles']);
Route::post("allindexs" , [HomeController::class,'allindexs']);
Route::get("/searchKey/{searchKey}" , [HomeController::class,'ingest_processor_searching']);
Route::get("/download/{file}" , [HomeController::class,'download']);
Route::get("/delete/{file}" , [HomeController::class,'deletePdf']);
Route::get("/" , [HomeController::class,'ingest_processor_indexing']);
Route::get("/mapping" , [HomeController::class,'ingest_processor_mapping']);

// Route::get("/map" , [HomeController::class,'ingest_processor_mapping']);
// Route::get("/index" , [HomeController::class,'ingest_processor_indexing']);
// Route::get("/search" , [HomeController::class,'ingest_processor_searching']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
