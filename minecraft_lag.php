#!/usr/bin/php
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

minecraft_lag.php: Returns RRDTool-parsable data regarding the current server lag (TPS) on a Minecraft server.

DEPENDS ON THE LAGMETER PLUGIN FOR THE CRAFTBUKKIT MINECRAFT SERVER DISTRIBUTION.  CraftBukkit can be downloaded
at <http://bukkit.org>, and LagMeter can be downloaded at <http://dev.bukkit.org/bukkit-plugins/lagmeter/>.

Note that the value for normal.value is hardcoded at 20.00 since this will probably never change in Minecraft as
the target TPS of a server.
*/

require_once("busybody.php");

if($argv[1] == "config") {
	echo "graph_title " . ((empty($_ENV['customTitle'])) ? "Minecraft Server Lag" : $_ENV['customTitle'] . " Server Lag") . "\n";
	echo "graph_vlabel Ticks per second (TPS)\n";
	echo "graph_category minecraft\n";
	echo "tps.label Actual TPS (1-minute average)\n";
	echo "tps.colour ff0000\n";
	echo "normal.label Normal TPS\n";
	echo "normal.colour 0fc20f\n";
	die();
}

$data = dataPlz();

if (preg_match('/\[[#_]+] ([0-9,.]+) TPS/', $data, $matches)) {
	$tps = str_replace(',', '', $matches[1]);

	echo "tps.value {$tps}\n";
	echo "normal.value 20.00\n";
} else {
	$data = dataPlz("tps");
	$data = preg_replace('/§./', '', $data);

	if (preg_match('/TPS from last 1m, 5m, 15m: ([0-9.]+), ([0-9.]+), ([0-9.]+)/', $data, $matches)) {
		$tps = str_replace(',', '', $matches[1]);

		echo "tps.value {$tps}\n";
		echo "normal.value 20.00\n";
	} else {
		echo "Error reading RCON data!\n";
		exit(1);
	}
}
?>
