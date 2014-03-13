<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\StreamOutput;

class ConfigureCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'syntax:configure';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Set up Laravel configs.';

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

		$this->remoteDetails   = new stdClass();
		$this->databaseDetails = new stdClass();
		$this->stream          = fopen('php://output', 'w');
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$this->comment('Starting Laravel configurations...');

		// Set up the configs
		$this->setUpRemote();
		$this->setUpDatabase();
		$this->setUpApp();

		$this->comment('Laravel configurations complete!');
	}

	/********************************************************************
	 * Unique Methods
	 *******************************************************************/
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
