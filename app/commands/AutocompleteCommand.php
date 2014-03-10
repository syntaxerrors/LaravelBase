<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class AutocompleteCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'autocomplete';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Sets up artisian autocomplete';

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
		$user = Config::get('remote.connections.default.username');
		// Set up the alias
		SSH::into('default')->run(array(
			'cd ~'. $user,
			'echo "alias artisan=\'php artisan\'" >> .bash_profile',
			'echo ". artisanBashCompletion/artisan" >> .bash_profile',
		));

		if (!File::exists('/home/'. $user .'/artisanBashCompletion')) {
			SSH::into('default')->run(array(
				'cd ~'. $user,
				'git clone git@github.com:janka/artisanBashCompletion.git',
			));
		}

		if (!File::exists('/etc/bash_completion.d/artisian')) {
			File::copy('/home/'. $user .'/artisanBashCompletion/artisan', '/etc/bash_completion.d/artisian');
		}
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
