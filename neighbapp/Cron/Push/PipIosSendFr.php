#!/usr/bin/php
<?php
putenv("APP=AP-IOS");
putenv("PLATFORM=cron");
require_once realpath(dirname(__FILE__)).'/PipIos.php';
set_time_limit(0);

$cron = new Cron_Push_PipIos();

echo "\n\n\n------------------[ Start Push Deamon at ".date("d/m/Y H:i:s")." ]--------------\n\n\n";

$endlessloop = 1;

// fork
$pid = pcntl_fork();

// echec du fork
if ($pid == -1)
{
	exit;
}
else if ($pid) {// parent

	exit;
}
if (!posix_setsid())  // Fait du processus courant un chef de session, detach from the controlling terminal
{
	// Could not detach from terminal
	exit;
}
else // le processus est chef de session
{

	// Start endless loop
	while ($endlessloop++)
	{
	    $cron->sendMessages();
            // Sleep for 1 second and loop!
            sleep(1);
	}
}