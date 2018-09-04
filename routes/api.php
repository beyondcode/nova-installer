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
Route::get('/tools', 'InstallerController@tools');

Route::get('/packages/search', 'NovaPackagesController@search');
Route::get('/packages/recent', 'NovaPackagesController@recent');