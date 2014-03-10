<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\StreamOutput;

class MigrationCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'migrate:syntax';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Run the syntax seeds.';

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
		// Set up the variables
		$stream             = fopen('php://output', 'w');
		$syntaxDirectories  = File::directories(base_path() .'/vendor/syntax');
		$migrationDirectory = '/src/database/migrations';
		$seedDirectory      = '/src/database/seeds';

		foreach ($syntaxDirectories as $syntaxDirectory) {
			$package = explode('/', $syntaxDirectory);
			$package = end($package);

			// Handle the migrations
			if (File::exists($syntaxDirectory . $migrationDirectory)) {
				$this->info('Running '. $package .' migrations...');
				Artisan::call('migrate', array('--path' => $syntaxDirectory . $migrationDirectory), new StreamOutput($stream));
				$this->info(ucwords($package) .' migrations complete!');
			}

			// Handle the seeds
			if (File::exists($syntaxDirectory . $seedDirectory)) {
				$seeds = File::files($syntaxDirectory . $seedDirectory);

				if (count($seeds) > 0) {
					$this->info('Running '. $package .' seeds...');
					foreach ($seeds as $seed) {
						$seeder = explode('/', $seed);
						$seeder = str_replace('.php', '', end($seeder));

						// Do not run for any DatabaseSeeder files
						if (strpos($seeder, 'DatabaseSeeder') === false) {
							// Only run if the seed is not already in the database
							if (Seed::whereName($seeder)->first() != null) continue;

							Artisan::call('db:seed', array('--class' => $seeder), new StreamOutput($stream));

							// Add the seed to the table
							$newSeed       = new Seed;
							$newSeed->name = $seeder;
							$newSeed->save();

							$this->info(ucwords($package) .' '. $seeder .' seeded!');
						}
					}
					$this->info(ucwords($package) .' seeds complete!');
				}
			}
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
