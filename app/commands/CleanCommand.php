<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\StreamOutput;

class CleanCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'syntax:clean';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Run the final commands to set up the site.';

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
	 * An object containing the core syntax config details
	 *
	 * @var string[]
	 */
	protected $syntaxCoreDetails;

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

		$this->syntaxCoreDetails = new stdClass();
		$this->stream            = fopen('php://output', 'w');
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$this->comment('Starting final steps...');

		// Run the installation
		$this->runArtisan();

		// Run gulp commands
		$this->runGulp();

		// Clean up
		$this->cleanUp();
	}

	/********************************************************************
	 * Unique Methods
	 *******************************************************************/
	protected function runArtisan()
	{
		$this->comment('Running artisan commands...');
		Artisan::call('key:generate', array(), new StreamOutput($this->stream));
		Artisan::call('migrate:install', array(), new StreamOutput($this->stream));
		Artisan::call('syntax:database', array(), new StreamOutput($this->stream));
		Artisan::call('syntax:gulp', array(), new StreamOutput($this->stream));
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
