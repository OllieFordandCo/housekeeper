<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('login/twitter', array('as' => 'housekeeper-twitter', 'uses' => 'OllieFordandCo\Housekeeper\HousekeeperController@loginwithTwitter'));
Route::get('login/facebook', array('as' => 'housekeeper-facebook', 'uses' => 'OllieFordandCo\Housekeeper\HousekeeperController@loginwithFacebook'));
Route::get('login/freshbooks', array('as' => 'housekeeper-freshbooks', 'uses' => 'OllieFordandCo\Housekeeper\HousekeeperController@loginwithFreshbooks'));