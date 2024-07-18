<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

/* Route::get('/', function () {
    return view('welcome');
}); */

// TaalController
use App\Http\Controllers\TaalController;
Route::controller(TaalController::class)->group(function() {
    Route::get('/taal/{taal?}', 'zetTaal');
});

/* // AppController
use App\Http\Controllers\AppController;
Route::controller(AppController::class)->group(function() {
    Route::get('/', 'index');
    Route::get('/home', 'index');
}); */



// TankbeurtController
use App\Http\Controllers\TankbeurtController;
Route::controller(TankbeurtController::class)->group(function() {
    Route::get('/tankbeurt/{voertuigID?}/{pagina?}', 'index');
    Route::post('/jxTankbeurtenLijst', 'jxTankbeurtenLijst');
    Route::post('/jxTankbeurtenGet', 'jxTankbeurtenGet');
    Route::post('/jxTankbeurtBewaar', 'jxTankbeurtBewaar');     
});


// VoertuigController
use App\Http\Controllers\VoertuigController;
Route::controller(VoertuigController::class)->group(function() {
    Route::get('/', 'index');
    Route::get('/home', 'index');
    Route::get('/voertuig', 'index');
    Route::post('/jxVoertuigenLijst', 'jxVoertuigenLijst');
    Route::post('/jxVoertuigenGet', 'jxVoertuigenGet');
    Route::post('/jxVoertuigBewaar', 'jxVoertuigBewaar');    
});


// GebruikersController
use App\Http\Controllers\GebruikersController;
Route::controller(GebruikersController::class)->group(function() {
    Route::get('/gebruikers', 'index');
    Route::post('/jxGebruikersLijst', 'jxGebruikersLijst');
    Route::post('/jxGebruikersGet', 'jxGebruikersGet');
    Route::post('/jxGebruikersBewaar', 'jxGebruikersBewaar');
    Route::post('/jxGebruikersResetInfo', 'jxGebruikersResetInfo');
    Route::post('/jxGebruikersResetMail', 'jxGebruikersResetMail');
});

Auth::routes();

//Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
