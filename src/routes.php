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

Route::get('login/twitter', array('as' => 'social-login-twitter', 'uses' => 'OllieFordandCo\Social-Login\SocialLoginController@loginwithTwitter'));
Route::get('login/facebook', array('as' => 'social-login-facebook', 'uses' => 'OllieFordandCo\Social-Login\SocialLoginController@loginwithFacebook'));
Route::get('login/freshbooks', array('as' => 'social-login-freshbooks', 'uses' => 'OllieFordandCo\Social-Login\SocialLoginController@loginwithFreshbooks'));