#!/usr/bin/php
<?php

NEEDS MOAR DRAMAH
awefawefHI I'M A SYNTAX ERROR!!!:!!:p!p!+o!p

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

minecraft_chunks.php: Returns RRDtool-parsable data about the chunks currently loaded on a Minecraft server.

DEPENDS ON THE LAGMETER PLUGIN FOR THE CRAFTBUKKIT MINECRAFT SERVER DISTRIBUTION.  CraftBukkit can be downloaded
at <http://bukkit.org>, and LagMeter can be downloaded at <http://dev.bukkit.org/bukkit-plugins/lagmeter/>.
*/
require_once("busybody.php");
$data = dataPlz();
$configMode = false;

$res = array();

if($argv[1] == "config") {
	$configMode = true;
	echo "graph_title " . ((empty($_ENV['customTitle'])) ? "Minecraft Chunks" : $_ENV['customTitle'] . " Chunks") . "\n";
	echo "graph_vlabel Loaded chunks\n";
	echo "graph_category minecraft\n";
}

if(preg_match_all('/Chunks in world ".*": [0-9]+/', $data, $matches)) {
	$data = $matches[0];

	preg_match('/Chunks in world "(.*)": [0-9]+/', $data[0], $worldName);
	$worldName = trim($worldName[1]);

	foreach($data as $part) {
		preg_match('/Chunks in world "' . $worldName . '(.*)": ([0-9]+)/', $part, $mtc);
		$dimension = $mtc[1];
		$chunks = $mtc[2];

		if(empty($dimension))
			$dimension = "_overworld";

		$res[$dimension] = $chunks;
	}

	$dimensions = array("_overworld", "_nether", "_the_end");
	$dimensionsFormal = array("Overworld", "Nether", "The End");
	$total = 0;
	foreach($dimensions as $did => $dimension) {
		if(empty($res[$dimension]))
			$res[$dimension] = 0;

		$outputLine = preg_replace('/^_/', '', $dimensions[$did]);
		if($configMode)
			$outputLine .= ".label {$dimensionsFormal[$did]}\n";
		else
			$outputLine .= ".value {$res[$dimension]}\n";

		echo $outputLine;
		$total += $res[$dimension];
	}
	$outputLine = "total";
	if($configMode)
		$outputLine .= ".label Total\n";
	else
		$outputLine .= ".value {$total}\n";

	echo $outputLine;
} else {
	echo "Error reading RCON data!\n";
	exit(1);
}
?>
