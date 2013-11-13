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

minecraft_memory.php: Returns RRDTool-parsable data about the memory status of a Minecraft server.

DEPENDS ON THE LAGMETER PLUGIN FOR THE CRAFTBUKKIT MINECRAFT SERVER DISTRIBUTION.  CraftBukkit can be downloaded
at <http://bukkit.org>, and LagMeter can be downloaded at <http://dev.bukkit.org/bukkit-plugins/lagmeter/>.
*/

require_once("busybody.php");
$data = dataPlz();

$res = array();

if($argv[1] == "config") {
	echo "graph_title " . ((empty($_ENV['customTitle'])) ? "Minecraft Free Memory" : $_ENV['customTitle'] . " Free Memory") . "\n";
	echo "graph_vlabel Megabytes (MiB)\n";
	echo "graph_category minecraft\n";
	echo "free.label Free memory\n";
	echo "total.label Total allocated memory\n";
	die();
}

if(preg_match('/\[[#_]+\] ([0-9,.]+)MB\/([0-9,.]+)MB \([0-9.%]+\) free/', $data, $matches)) {
	$freemem = str_replace(',', '', $matches[1]);
	$totalmem = str_replace(',', '', $matches[2]);

	echo "free.value {$freemem}\n";
	echo "total.value {$totalmem}\n";
} else {
	echo "Error reading RCON data!\n";
	exit(1);
}
?>
