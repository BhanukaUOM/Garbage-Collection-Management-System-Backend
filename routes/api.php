<?php
use Illuminate\Http\Request;


Route::group([
    'prefix' => 'auth'
], function () {
    Route::post('login', 'AuthController@login');
    Route::post('signup', 'AuthController@signup');
    Route::get('signup/activate/{token}', 'AuthController@signupActivate');

    Route::group([
      'middleware' => 'auth:api'
    ], function() {
        Route::get('logout', 'AuthController@logout');
        Route::get('user', 'AuthController@user');
    });
});

Route::group([
    'namespace' => 'Auth',
    'middleware' => 'api',
    'prefix' => 'password'
], function () {
    Route::post('create', 'PasswordResetController@create');
    Route::get('find/{token}', 'PasswordResetController@find');
    Route::post('reset', 'PasswordResetController@reset');
});

Route::group([
    'middleware' => 'auth:api'
], function () {
    Route::post('users/{id}', 'UsersController@update');
    Route::get('users/profile', 'UsersController@profile');
    Route::resource('users', 'UsersController', ['only' => ['index', 'show', 'store', 'update', 'destroy']]);
    Route::post('users/pause', 'UsersController@pause');
});

Route::group([
    'middleware' => 'auth:api'
], function () {
    Route::resource('roles', 'RolesController', ['only' => ['index', 'show', 'store', 'update', 'destroy']]);
    Route::get('role', 'RolesController@allRoles');
});

Route::group([
    'middleware' => 'auth:api'
], function () {
    Route::resource('permissions', 'PermissionsController', ['only' => ['index', 'show', 'store', 'update', 'destroy']]);
    Route::get('permission', 'PermissionsController@allPermissions');
});

Route::group([
    'middleware' => 'auth:api'
], function () {
    
    Route::get('location', 'LocationController@getCurrent');
    Route::get('locations', 'LocationController@index');
    Route::get('location/allcollectors', 'LocationController@allCollectors');
    Route::get('location/allcustomers', 'LocationController@allCustomers');
    Route::get('location/{id}', 'LocationController@get');
    Route::post('location', 'LocationController@add');
});
Route::group([
    'middleware' => 'auth:api'
], function () {
    Route::post('requests/pickup', 'RequestsController@pickup');
    Route::get('requests/unapprovedRequests', 'RequestsController@unapprovedRequests');
    Route::post('requests/approve', 'RequestsController@approve');
    Route::get('requests/currentUser', 'RequestsController@currentUser');
    Route::resource('requests', 'RequestsController', ['only' => ['index', 'show', 'store', 'update', 'destroy']]);
    
});

Route::group([
    'middleware' => 'auth:api'
], function () {
    Route::resource('points', 'PointsController', ['only' => ['index', 'show', 'store', 'update', 'destroy']]);
});

Route::group([
    'middleware' => 'auth:api'
], function () {
    Route::get('point/leaderboard', 'PointController@leaderboard');
    Route::resource('point', 'PointController', ['only' => ['index', 'show', 'store', 'update', 'destroy']]);
});