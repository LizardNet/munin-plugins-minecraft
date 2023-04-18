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

minecraft_users.php: Returns RRDTool-parsable data about the number of users currently logged in on a Minecraft server,
as well as the current user limit.

This plugin only requires vanilla Minecraft; neither CraftBukkit nor any plugins are needed to use this Munin plugin!
*/

require_once("busybody.php");
$data = dataPlz("list");

$res = array();

if ($argv[1] == "config") {
	echo "graph_title " . ((empty($_ENV['customTitle'])) ? "Minecraft Users" : $_ENV['customTitle'] . " Users") . "\n";
	echo "graph_vlabel Users\n";
	echo "graph_category minecraft\n";
	echo "users.label Logged-in users\n";
	echo "maxusers.label Maximum users allowed\n";
	die();
}

if (preg_match('/There are ([0-9]+)\/([0-9]+) players online:/', $data, $matches)) {
	$users = str_replace(',', '', $matches[1]);
	$maxusers = str_replace(',', '', $matches[2]);

	echo "users.value {$users}\n";
	echo "maxusers.value {$maxusers}\n";
} elseif (preg_match('/There are ([0-9]+) of a max of ([0-9]+) players online:/', $data, $matches)) {
	$users = str_replace(',', '', $matches[1]);
	$maxusers = str_replace(',', '', $matches[2]);

	echo "users.value {$users}\n";
	echo "maxusers.value {$maxusers}\n";
} else {
	echo "Error reading RCON data!\n";
	exit(1);
}
?>
