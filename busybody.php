<?php
/*
LIZARDNET FASTLIZARD4/MUNIN-PLUGINS/MINECRAFT <https://fastlizard4.org/wiki/Download:Minecraft_Munin_plugins>
by FastLizard4 and the LizardNet Munin Plugins Development Team <https://gerrit.fastlizard4.org/r/#/admin/groups/17,members>

Copyright (C) 2013 by FastLizard4 and the LizardNet Munin Plugins Development Team.  Some rights reserved.

License GPLv3+: GNU General Public License version 3 or later (at your choice): <http://gnu.org/licenses/gpl.html>.
This is free software: you are free to change and redistribute it at your will provided that your redistribution,
with or without modifications, is also licensed under the GNU GPL.

There is NO WARRANTY FOR THIS SOFTWARE to the extent permitted by law.

This is an open source project.  The source Git repositories, which you are welcome to contribute to, can be
found here: <https://gerrit.fastlizard4.org/r/gitweb?p=munin-plugins/minecraft.git;a=summary>

Gerrit Code Review for the project: <https://gerrit.fastlizard4.org/r/#/q/project:munin-plugins/minecraft,n,z>

=====

busybody.php: Defines the dataPlz() function, which connects to the Minecraft server by RCON, issues a command,
and returns the output.

By default, runs the /lmp command provided by CraftBukkit::LagMeter, but dataPlz takes a single optional string parameter
to change the command run.

dataPlz() is called by all Munin plugins in this repository.
*/

require_once('minecraftRcon.class.php');

function dataPlz($command = "lmp") {

	$host = ((empty($_ENV['host'])) ? "localhost" : $_ENV['host']);
	$port = ((empty($_ENV['port'])) ? 25575 : $_ENV['port']);
	$pwd = ((empty($_ENV['password'])) ? "" : $_ENV['password']);

	$r = new minecraftRcon;

	try {
		$r->connect($host, $port, $pwd, 10);

		$data = $r->command($command);
	} catch(minecraftRconException $e) {
		echo "ERROR: Caught excepction!  Details: \"{$e->getMessage()}\".\n";
		exit(1);
	}

	$r->Disconnect();

	$data = preg_replace('/§[0-9a-f]/i', '', $data);
	$data = preg_replace('/\[LagMeter\][\W]/i', '', $data);
	return trim($data);
}
?>
