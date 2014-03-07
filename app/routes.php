<?php

// Let them logout
Route::get('logout', function()
{
	Auth::logout();
	return Redirect::to('/')->with('message', 'You have successfully logged out.');
});

// Secure routes
/********************************************************************
 * General
 *******************************************************************/
Route::group(array('before' => 'auth'), function()
{
	Route::controller('user'	, 'Core_UserController');
	Route::controller('messages', 'Core_MessageController');
	Route::controller('github'	, 'Core_GithubController');
});

/********************************************************************
 * Access to the dev panel
 *******************************************************************/
Route::group(array('before' => 'auth|permission:SITE_ADMIN'), function()
{
	Route::controller('admin', 'Core_AdminController');
});

// Landing page
Route::controller('/', 'HomeController');

require_once('start/local.php');
