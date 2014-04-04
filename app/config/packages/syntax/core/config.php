<?php

return array(

	/*
	|--------------------------------------------------------------------------
	| Current version
	|--------------------------------------------------------------------------
	|
	| The current version of Syntax\Core the site is using
	*/
	'version' => '1.0.0',

	/*
	|--------------------------------------------------------------------------
	| Control-Room Site detail
	|--------------------------------------------------------------------------
	|
	| Set this to the site's control room data.  Get this from stygian or riddles
	*/
	'controlRoomDetail' => 'GET_THIS_FROM_CONTROL',

	/*
	|--------------------------------------------------------------------------
	| Site Details
	|--------------------------------------------------------------------------
	|
	| The name of your site and the icon it should use.  This will show up in 
	| twitter menues.  Set the siteIcon to null for no icon.
	|
	*/

	'siteName' => 'YOUR_SITE',
	'siteIcon' => null,

	/*
	|--------------------------------------------------------------------------
	| Github repo
	|--------------------------------------------------------------------------
	|
	| If your site uses a github repo, set it's name here. This will 
	| allow the application to give it priority in certain areas
	| over other repos that may be shown.
	|
	*/

	'primaryRepo' => 'Core Package',

	/*
	|--------------------------------------------------------------------------
	| All github repos
	|--------------------------------------------------------------------------
	|
	| The full list of repo names you want displayed in the site.
	|
	*/

	'allRepos' => array(
		'stygiansabyss' => array(
			'Anima'        => 'Anima'
		),
		'syntaxerrors' => array(
			'core'         => 'Core Package',
		),
		'riddles8888' => array(
			'control-room' => 'Control-Room',
			'stygianvault' => 'StygianVault',
			'AHScoreboard' => 'AH Scoreboard',
			'dev-toolbox'  => 'Dev-Toolbox',
			'core'         => 'Core',
			'LaravelBase'  => 'Laravel Base',
		),
	),

	/*
	|--------------------------------------------------------------------------
	| Application Menu
	|--------------------------------------------------------------------------
	|
	| This variable is used to determine if the site uses the default twitter nav
	| bar or any form of custom menu.  Set this value to the name of the blade
	| located in views/layouts/menus that you wish to use.
	| Options: twitter, utopian
	|
	*/
	'menu' => 'utopian',

	/*
	|--------------------------------------------------------------------------
	| Application Menu
	|--------------------------------------------------------------------------
	|
	| Use the following array to stop core from setting certain classes and keep
	| the laravel defaults.  A common use for thie would be 'User'.
	|
	*/
	'nonCoreAliases' => array(
	),

);