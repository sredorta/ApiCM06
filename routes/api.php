<?php

use Illuminate\Http\Request;

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

///////////////////////////////////////////////////////////////////////////////
// AUTH PART
//////////////////////////////////////////////////////////////////////////////
//Registered or not
Route::group(['middleware' => ['any']], function ($router) {
    Route::get('auth/user', 'AccountController@getAuthUser');
    Route::get('auth/lang/any', 'AccountController@language');
    Route::get('brands', 'BrandController@getAll');
    Route::get('models', 'ModeleController@getAll');
    Route::get('products', 'ProductController@getAll');
    Route::post('product', 'ProductController@get');
    Route::get('config', 'ConfigurationController@get');            //Get the config
    Route::post('cart/check', 'OrderController@checkCart');         //Checks cart and returns all data to update gui
    Route::post('order/check', 'OrderController@check');            //Checks order and return data
    Route::post('order/create', 'OrderController@create');          //Creates preliminary order

});

//Only if we are not loggedIn
Route::group(['middleware' => ['unregistered']], function ($router) {
    Route::post('auth/login', 'AccountController@login');
    Route::post('auth/signup', 'AccountController@signup'); 
    Route::get('auth/emailvalidate', 'AccountController@emailValidate');   
    Route::post('auth/resetpassword', 'AccountController@resetPassword');   //Resets password
    Route::get('auth/lang/unregistered', 'AccountController@language');
});

//Only if we are registerd with any access
Route::group(['middleware' => ['registered']], function ($router) {
    Route::get('auth/lang/registered', 'AccountController@language');
    Route::post('auth/logout', 'AccountController@logout');
    Route::post('auth/update', 'AccountController@update'); 
    Route::delete('auth/delete', 'AccountController@delete'); 
    Route::delete('auth/deleteAuth', 'AccountController@deleteAuth');   //Deletes auth user

    //Notifications part
//    Route::post('notifications/delete', 'NotificationController@delete');
//    Route::post('notifications/markread', 'NotificationController@markAsRead');
//    Route::get('notifications', 'NotificationController@getAll');
    //  auth/delete
    //  all notifications, messages, imageables, attachables

    //Document handling
//    Route::post('attachment/create', 'AttachmentController@create');
});

//Only if we are admin
Route::group(['middleware' => ['registered','admin']], function ($router) {
    Route::get('auth/lang/admin', 'AccountController@language');
    Route::post('auth/account/create', 'AccountController@addAccount');         //Adds accounts to user
    Route::post('auth/account/delete', 'AccountController@deleteAccount');    //Removes account from user
    Route::post('auth/user/delete', 'AccountController@deleteUser');
    Route::get('users', 'AccountController@getAll');
    Route::post('config', 'ConfigurationController@set');           //Get the config

    //Route::post('auth/account/toggle', 'AccountController@toggleAccount');      //toggles Pré-inscrit to Membre
//    Route::delete('attachment/delete', 'AttachmentController@delete'); //Deletes a attachment by id
    //PAGE HANDLING
//    Route::delete('pages/delete', 'PageController@delete');
//    Route::post('pages/create', 'PageController@create');
    //Route::get('pages/attachments', 'PageController@getAttachments'); //MOVE ME TO ANY !!!!
    //Route::post('pages/attachments/create', 'PageController@addAttachment');
    Route::post('brands/create', 'BrandController@create');
    Route::post('brands/update', 'BrandController@update');
    Route::post('brands/delete', 'BrandController@delete');
    Route::post('models/create', 'ModeleController@create');
    Route::post('models/update', 'ModeleController@update');
    Route::post('models/delete', 'ModeleController@delete');
    Route::post('products/create', 'ProductController@create');
    Route::post('products/update', 'ProductController@update');
    Route::post('products/delete', 'ProductController@delete');
});





Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
