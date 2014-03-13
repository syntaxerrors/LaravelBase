<?php

/*
|--------------------------------------------------------------------------
| Register The Artisan Commands
|--------------------------------------------------------------------------
|
| Each available Artisan command must be registered with the console so
| that it is available to be called. We'll register every command so
| the console gets access to each of the command object instances.
|
*/

Artisan::add(new InstallCommand);
Artisan::resolve('InstallCommand');

Artisan::add(new ConfigureCommand);
Artisan::resolve('ConfigureCommand');

Artisan::add(new SetupCommand);
Artisan::resolve('SetupCommand');

Artisan::add(new CleanCommand);
Artisan::resolve('CleanCommand');

Artisan::add(new DatabaseCommand);
Artisan::resolve('DatabaseCommand');

Artisan::add(new GulpCommand);
Artisan::resolve('GulpCommand');