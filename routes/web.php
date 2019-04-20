<?php

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

Route::get('login', ['as' => 'login', 'uses' => 'Auth\LoginController@showLoginForm' ]);
Route::post('login', [   'as' => '', 'uses' => 'Auth\LoginController@login']);

// PASSWORD RESET
Route::post('password/email', [ 'as' => 'password.email', 'uses' => 'Auth\ForgotPasswordController@sendResetLinkEmail']);
Route::get('password/reset', ['as' => 'password.request', 'uses' => 'Auth\ForgotPasswordController@showLinkRequestForm']);
Route::post('password/reset', ['as' => '', 'uses' => 'Auth\ResetPasswordController@reset']);
Route::get('password/reset/{token}', ['as' => 'password.reset', 'uses' => 'Auth\ResetPasswordController@showResetForm']);

Route::middleware(['auth'])->group(function () {
  Route::post('logout', [ 'as' => 'logout', 'uses' => 'Auth\LoginController@logout' ]);
  Route::get('/', ['as' => 'dashboard', 'uses' => 'DashboardController@index' ]);

  // PROFILE
  Route::get('/profile/edit', ['as' => 'profile.edit', 'uses' => 'ProfileController@edit' ]);
  Route::put('/profile/edit', ['as' => 'profile.update', 'uses' => 'ProfileController@update' ]);

  // USER
  Route::get('user/data', [ 'as' => 'user.data', 'uses' => 'UserController@data']);
  Route::resource('user', 'UserController');

  // FORM UPLOAD
  Route::post('upload', [ 'as' => 'upload.upload', 'uses' => 'UploadController@upload']);
  Route::get('upload', [ 'as' => 'upload.index', 'uses' => 'UploadController@index']);

  // TRANSACTION
  Route::get('hincomecal/data', [ 'as' => 'hincomecal.data', 'uses' => 'HIncomecalController@data']);
  Route::get('hincomecal', [ 'as' => 'hincomecal.index', 'uses' => 'HIncomecalController@index']);
  Route::delete('hincomecal/{hincomecal}', [ 'as' => 'hincomecal.destroy', 'uses' => 'HIncomecalController@destroy']);
  Route::post('hincomecal', [ 'as' => 'hincomecal.store', 'uses' => 'HIncomecalController@store']);

  // REPORT
  Route::get('report', [ 'as' => 'report.index', 'uses' => 'ReportController@index']);
  Route::post('report', [ 'as' => 'report.report', 'uses' => 'ReportController@report']);
  Route::post('report/download', [ 'as' => 'report.download', 'uses' => 'ReportController@download']);

  // DRIVER
  Route::get('driver', [ 'as' => 'driver.search', 'uses' => 'DriverController@search']);
  Route::get('driver-name', [ 'as' => 'driver.searchname', 'uses' => 'DriverController@searchName']);
});
