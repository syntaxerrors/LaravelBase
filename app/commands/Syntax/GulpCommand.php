<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\StreamOutput;

class GulpCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'syntax:gulp';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Set up everything needed for gulp.js to work.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		// Update the ignore file
		$ignoredFiles = file(base_path('.gitignore'));

		if (!in_array('/node_modules', $ignoredFiles)) {
			$this->comment('Adding node_modules directory to .gitignore');
			File::append(base_path('.gitignore'), "/node_modules");
		}

		$this->comment('Adding all the gulp plugins...');
		$rootDirectory = Config::get('remote.connections.default.root');

		$commands = [
			'cd '. $rootDirectory,
			'npm install --save-dev gulp gulp-autoprefixer gulp-util gulp-notify gulp-minify-css gulp-uglify gulp-less gulp-rename gulp-concat'
		];

		SSH::run($commands, function($line) {
			echo $line.PHP_EOL;
		});

		$this->comment('Finished adding gulp plugins.');
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
