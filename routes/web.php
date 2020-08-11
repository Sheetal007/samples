<?php

use Illuminate\Support\Facades\Route;

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

/* Auth Routes*/
Route::get('/', 'Auth\LoginController@index')->name('login');
Route::post('/login', 'Auth\LoginController@login');

/* All routes will be accessible if logged in*/
Route::middleware(['auth'])->group(function () {
    Route::get('/agency-management/downline/', 'AgencyManagementController@downline')->name('agency-management-downline');
    Route::get('/agency-management/create/', 'AgencyManagementController@create')->name('agency-management-create-user');
    Route::post('/agency-management/create/', 'AgencyManagementController@store');
    Route::any('/agency-management/downline-subusers/', 'AgencyManagementController@getSubUsers');
    Route::get('/agent-listings', 'AgentListingsController@index')->name('agent-listings');
    Route::any('/logout', 'Auth\LoginController@logout')->name('logout');
});
