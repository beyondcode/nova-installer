<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tool API Routes
|--------------------------------------------------------------------------
|
| Here is where you may register API routes for your tool. These routes
| are loaded by the ServiceProvider of your tool. They are protected
| by your tool's "Authorize" middleware by default. Now, go build!
|
*/

Route::any('/install', 'InstallerController@install');
Route::any('/remove', 'InstallerController@remove');
Route::any('/update', 'InstallerController@update');

Route::get('/tools', 'InstallerController@tools');

Route::get('/composer-status', 'InstallationStatusController@show');
Route::get('/composer-status-reset', 'InstallationStatusController@reset');

Route::get('/packages/search', 'NovaPackagesController@search');
Route::get('/packages/recent', 'NovaPackagesController@recent');
Route::get('/packages/popular', 'NovaPackagesController@popular');
Route::get('/packages/installed', 'NovaPackagesController@installed');
