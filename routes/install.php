<?php

/*
|--------------------------------------------------------------------------
| Web installer routes
|--------------------------------------------------------------------------
*/
Route::get('/', 'InstallController@welcome')->name('welcome');
Route::get('requirements', 'InstallController@requirements')->name('requirements');
Route::get('permissions', 'InstallController@permissions')->name('permissions');
Route::get('environment/wizard', 'InstallController@environment')->name('environment');
Route::post('environment/saveWizard', 'InstallController@saveEnvironment')->name('environment.save');
Route::get('database', 'InstallController@database')->name('database');
Route::get('final', 'InstallController@finish')->name('final');
