<?php
use Illuminate\Support\Facades\Route;

/* Middleware Group*/
Route::group(['namespace' => 'Api'], function () {
    /* Resource route with all route types */
    Route::get('imports/', 'ImportController@index');
    Route::post('imports/', 'ImportController@store');
    Route::delete('imports/{id}', 'ImportController@destroy');
    Route::post('imports/{id}/preview', 'ImportController@preview');
    Route::post('imports/{id}/import', 'ImportController@import');
});
?>
