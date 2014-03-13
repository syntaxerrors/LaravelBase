<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\StreamOutput;

class InstallCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'syntax:install';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Run the everything needed to get a syntax site up and running.';

	/**
	 * An array of available syntax packages
	 *
	 * @var string[]
	 */
	protected $syntaxPackages = ['chat', 'forum'];

	/**
	 * An array of packages that will need a config loaded
	 *
	 * @var string[]
	 */
	protected $syntaxPackagesWithConfig = ['chat'];

	/**
	 * An object containing the remote config details
	 *
	 * @var string[]
	 */
	protected $remoteDetails;

	/**
	 * An object containing the database config details
	 *
	 * @var string[]
	 */
	protected $databaseDetails;

	/**
	 * An object containing the core syntax config details
	 *
	 * @var string[]
	 */
	protected $syntaxCoreDetails;

	/**
	 * The URL for the this site
	 *
	 * @var string
	 */
	protected $siteUrl;

	/**
	 * The output stream for any artisan commands
	 *
	 * @var string
	 */
	protected $stream;

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();

		$this->remoteDetails     = new stdClass();
		$this->databaseDetails   = new stdClass();
		$this->syntaxCoreDetails = new stdClass();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		// Set up the variables
		$this->stream = fopen('php://output', 'w');

		// Set up the configs
		$this->setUpRemote();
		$this->setUpDatabase();
		$this->setUpSyntaxCore();
		$this->setUpApp();

		// Get out syntax packages
		$this->updateSyntax();

		// Run the installation
		$this->runArtisan();

		// Run gulp commands
		$this->runGulp();

		// Clean up
		$this->cleanUp();

		$this->comment('Installation complete!');
	}

	/********************************************************************
	 * Unique Methods
	 *******************************************************************/
	protected function runArtisan()
	{
		$this->comment('Running artisan commands...');
		Artisan::call('key:generate', null, new StreamOutput($this->stream));
		Artisan::call('migrate:install', null, new StreamOutput($this->stream));
		Artisan::call('syntax:database', null, new StreamOutput($this->stream));
		Artisan::call('syntax:gulp', null, new StreamOutput($this->stream));
	}

	protected function runGulp()
	{
		$this->comment('Running gulp commands...');
		$commands = [
			'cd '. base_path(),
			'gulp install'
		];

		SSH::run($commands, function ($line) {
			echo $line.PHP_EOL;
		});
	}

	protected function cleanUp()
	{
		$this->comment('Running clean up commands...');
		$commands = [
			'cd '. base_path(),
			'chmod 755 public',
			'chmod 755 public/index'
		];

		SSH::run($commands, function ($line) {
			echo $line.PHP_EOL;
		});
	}

	protected function confirmConfig($type, $configDetails = null)
	{
		$this->line('Your '. $type .' configuration will be set to the following.');

		switch ($type) {
			case 'remote':
				$this->line(print_r($this->remoteDetails, 1));
				if (!$this->confirm('Do you want to keep this configuration? [yes|no]')) {
					return $this->setUpRemote();
				} else {
					return $this->configureRemote();
				}
			break;
			case 'database':
				$this->line(print_r($this->databaseDetails, 1));
				if (!$this->confirm('Do you want to keep this configuration? [yes|no]')) {
					return $this->setUpDatabase();
				} else {
					return $this->configureDatabase();
				}
			break;
			case 'core':
				$this->line(print_r($this->syntaxCoreDetails, 1));
				if (!$this->confirm('Do you want to keep this configuration? [yes|no]')) {
					return $this->setUpSyntaxCore();
				} else {
					return $this->configureSyntaxCore();
				}
			break;
			default:
				$this->line(print_r($configDetails, 1));
				if (!$this->confirm('Do you want to keep this configuration? [yes|no]')) {
					return $this->setUpSyntaxCore();
				} else {
					return $this->configureSyntaxCore();
				}
			break;
		}
	}

	protected function updateSyntax()
	{
		foreach ($this->syntaxPackages as $package) {
			if ($this->confirm('Do you wish to install syntax\\'. $package .'? [yes|no]')) {
				$this->comment('Installing syntax\\'. $package .'...');

				$commands = [
					'cd '. base_path(),
					'composer require syntax/'. $package .':dev-master',
				];

				if (in_array($package, $this->syntaxPackagesWithConfig) && !File::exists(app_path('config/packages/syntax/chat'))) {
					$commands[] = 'php artisan config:publish syntax/'. $package;
				}

				SSH::run($commands, function ($line) {
					echo $line.PHP_EOL;
				});

				$this->setUpSyntax($package);
			}
		}
	}

	/********************************************************************
	 * Set Up Methods
	 *******************************************************************/
	protected function setUpRemote()
	{
		// Set up our remote config
		$this->comment('Setting up remote config options...');
		$this->remoteDetails->host      = $this->ask('What is your remote host? (for ports add :<PORT> to the end)');
		$this->remoteDetails->username  = $this->ask('What is your remote username?');
		$this->remoteDetails->password  = $this->secret('What is your remote password?');
		$this->remoteDetails->key       = $this->ask('Where is your remote rsa key?');
		$this->remoteDetails->keyphrase = $this->ask('What is the passphrase?');
		$this->remoteDetails->root      = $this->ask('Where is your remote root?');

		$this->confirmConfig('remote');
	}

	protected function setUpDatabase()
	{
		// Set up our database config
		$this->comment('Setting up datatabase details...');
		$this->databaseDetails->driver    = $this->ask('What is your database driver?  [Hit enter to use mysql driver]', 'mysql');
		$this->databaseDetails->host      = $this->ask('What is your database host?');
		$this->databaseDetails->database  = $this->ask('What is the database name?');
		$this->databaseDetails->username  = $this->ask('What is your database username?');
		$this->databaseDetails->password  = $this->secret('What is your database password?');
		$this->databaseDetails->charset   = $this->ask('What is your database charset?  [Hit enter to use utf8]', 'utf8');
		$this->databaseDetails->collation = $this->ask('What is your database collation?  [Hit enter to use utf8_unicode_ci]', 'utf8_unicode_ci');
		$this->databaseDetails->prefix    = $this->ask('What is your database prefix?');

		$this->confirmConfig('database');
	}

	protected function setUpSyntaxCore()
	{
		// Set up our syntax config
		$this->comment('Setting up syntax details...');
		$this->syntaxCoreDetails->controlRoomDetail = $this->ask('What is this site\'s control room name?');
		$this->syntaxCoreDetails->siteName          = $this->ask('What is this name to display for this site?');
		$this->syntaxCoreDetails->siteIcon          = $this->ask('What is this icon to display for this site? (Use tha last part of the font-awesome icon class)');
		$this->syntaxCoreDetails->menu              = $this->ask('What is menu style should this site default to? (utopian or twitter)');

		$this->confirmConfig('core');
	}

	protected function setUpSyntax($package)
	{
		switch ($package) {
			case 'chat':
				return $this->setUpSyntaxChat();
			break;
		}
	}

	protected function setUpSyntaxChat()
	{
		// Set up our syntax config
		$this->comment('Setting up syntax details...');
		$chatDetails                    = new stdClass();
		$chatDetails->debug             = $this->confirm('Should the chats show debug info?  [Hit enter to leave as true]', true) ? true : false;
		$chatDetails->port              = $this->ask('What is the chat port?  [Hit enter to leave as 1337]', 1337);
		$chatDetails->backLog           = $this->ask('How much back log should the chats get?  [Hit enter to leave as 100]', 100);
		$chatDetails->backFill          = $this->ask('How much should the chats backfil on connect?  [Hit enter to leave as 30]', 30);
		$chatDetails->apiEndPoint       = $this->ask('What is the chat url?  [Hit enter to leave as '. $this->siteUrl .']', $this->siteUrl);
		$chatDetails->connectionMessage = $this->confirm('Should the chats show a connection message?  [Hit enter to leave as true]', true) ? true : false;

		$chatConfig = json_encode($chatDetails, JSON_PRETTY_PRINT);

		$this->line('Your chat configuration will be set to the following.');
		$this->line($chatConfig ."\n");
		if (!$this->confirm('Do you want to keep this configuration? [yes|no]')) {
			return $this->setUpSyntaxChat();
		} else {
			return $this->configureSyntaxChat($chatConfig);
		}
	}

	protected function setUpApp()
	{
		// Set up our app config
		$this->comment('Setting up app details...');
		$this->siteUrl = $this->ask('What is this site\'s url?');

		list($path, $contents) = $this->getConfig('app');

		$contents = str_replace($this->laravel['config']['app.url'], $this->siteUrl, $contents);

		File::put($path, $contents);
	}

	/********************************************************************
	 * Configuration Methods
	 *******************************************************************/
	protected function configureRemote()
	{
		list($path, $contents) = $this->getConfig('remote');

		foreach ($this->remoteDetails as $key => $value) {
			$contents = str_replace($this->laravel['config']['remote.connections.default.'. $key], $value, $contents);
		}

		File::put($path, $contents);
	}

	protected function configureDatabase()
	{
		list($path, $contents) = $this->getConfig('database');

		foreach ($this->databaseDetails as $key => $value) {
			$contents = str_replace($this->laravel['config']['database.connections.mysql.'. $key], $value, $contents);
		}

		File::put($path, $contents);
	}

	protected function configureSyntaxCore()
	{
		list($path, $contents) = $this->getConfig('packages/syntax/core/config');

		foreach ($this->syntaxCoreDetails as $key => $value) {
			$contents = str_replace($this->laravel['config']['core::'. $key], $value, $contents);
		}

		File::put($path, $contents);
	}

	protected function configureSyntaxChat($config)
	{
		$path = $this->laravel['path'].'/config/packages/syntax/chat/chatConfig.json';
		File::put($path, $config);
	}

	/********************************************************************
	 * Extra Methods
	 *******************************************************************/
	protected function getConfig($file)
	{
		$path = $this->laravel['path'].'/config/'. $file .'.php';

		$contents = File::get($path);

		return array($path, $contents);
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			// array('example', InputArgument::REQUIRED, 'An example argument.'),
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
			// array('example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null),
		);
	}

}
