<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\StreamOutput;

class SetupCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'syntax:setup';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Install and configure syntax packages.';

	/**
	 * An array of available syntax packages
	 *
	 * @var string[]
	 */
	protected $packages = ['chat', 'forum'];

	/**
	 * An array of packages that will need a config loaded
	 *
	 * @var string[]
	 */
	protected $packagesWithConfig = ['chat'];

	/**
	 * An object containing the core config details
	 *
	 * @var string[]
	 */
	protected $coreDetails;

	/**
	 * An object containing the chat config details
	 *
	 * @var string[]
	 */
	protected $chatDetails;

	/**
	 * The JSON object for the chat config
	 *
	 * @var string
	 */
	protected $chatConfig;

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

		$this->coreDetails = new stdClass();
		$this->chatDetails = new stdClass();
		$this->stream      = fopen('php://output', 'w');
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$this->comment('Starting Syntax configuration...');

		// Set up the configs
		$this->setUpCore();

		// Get out syntax packages
		$this->updateSyntax();

		$this->comment('Syntax configuration complete!');
	}

	/********************************************************************
	 * Unique Methods
	 *******************************************************************/
	protected function confirmConfig($type)
	{
		$this->line('Your '. $type .' configuration will be set to the following.');

		switch ($type) {
			case 'core':
				$this->line(print_r($this->coreDetails, 1));
				if (!$this->confirm('Do you want to keep this configuration? [yes|no]')) {
					return $this->setUpCore();
				} else {
					return $this->configureCore();
				}
			break;
			case 'chat':
				$this->line($this->chatConfig ."\n");
				if (!$this->confirm('Do you want to keep this configuration? [yes|no]')) {
					return $this->setUpChat();
				} else {
					return $this->configureChat();
				}
			break;
		}
	}

	protected function updateSyntax()
	{
		$this->comment('Starting Syntax package options...');
		foreach ($this->packages as $package) {
			if ($this->confirm('Do you wish to install syntax\\'. $package .'? [yes|no]')) {
				$this->comment('Installing syntax\\'. $package .'...');

				$commands = [
					'cd '. base_path(),
					'composer require syntax/'. $package .':dev-master',
					'php artisan config:publish syntax/'. $package
				];

				SSH::run($commands, function ($line) {
					echo $line.PHP_EOL;
				});

				$this->setUpSyntax($package);

				$this->comment('Install of syntax\\'. $package .' complete!');
			}
		}
	}

	/********************************************************************
	 * Set Up Methods
	 *******************************************************************/

	protected function setUpSyntax($package)
	{
		switch ($package) {
			case 'chat':
				return $this->setUpChat();
			break;
		}
	}

	protected function setUpCore()
	{
		// Set up our syntax config
		$this->comment('Setting up syntax details...');
		$this->coreDetails->controlRoomDetail = $this->ask('What is this site\'s control room name?');
		$this->coreDetails->siteName          = $this->ask('What is this name to display for this site?');
		$this->coreDetails->siteIcon          = $this->ask('What is this icon to display for this site? (Use tha last part of the font-awesome icon class)');
		$this->coreDetails->menu              = $this->ask('What is menu style should this site default to? (utopian or twitter)');

		$this->confirmConfig('core');
	}

	protected function setUpChat()
	{
		// Set up our syntax config
		$this->comment('Setting up syntax details...');
		$this->chatDetails->debug             = $this->confirm('Should the chats show debug info?  [Hit enter to leave as true]', true) ? true : false;
		$this->chatDetails->port              = $this->ask('What is the chat port?  [Hit enter to leave as 1337]', 1337);
		$this->chatDetails->backLog           = $this->ask('How much back log should the chats get?  [Hit enter to leave as 100]', 100);
		$this->chatDetails->backFill          = $this->ask('How much should the chats backfil on connect?  [Hit enter to leave as 30]', 30);
		$this->chatDetails->apiEndPoint       = $this->ask('What is the chat url?');
		$this->chatDetails->connectionMessage = $this->confirm('Should the chats show a connection message?  [Hit enter to leave as true]', true) ? true : false;

		$this->chatConfig = json_encode($this->chatDetails, JSON_PRETTY_PRINT);

		$this->confirmConfig('chat');
	}

	/********************************************************************
	 * Configuration Methods
	 *******************************************************************/

	protected function configureCore()
	{
		list($path, $contents) = $this->getConfig('packages/syntax/core/config.php');

		foreach ($this->coreDetails as $key => $value) {
			$contents = str_replace($this->laravel['config']['core::'. $key], $value, $contents);
		}

		File::put($path, $contents);
	}

	protected function configureChat()
	{
		list($path, $contents) = $this->getConfig('packages/syntax/chat/chatConfig.json');
		File::put($path, $this->chatConfig);
	}

	/********************************************************************
	 * Extra Methods
	 *******************************************************************/
	protected function getConfig($file)
	{
		$path = $this->laravel['path'].'/config/'. $file;

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
