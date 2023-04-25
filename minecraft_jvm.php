#!/usr/bin/php
<?php
/*
LIZARDNET FASTLIZARD4/MUNIN-PLUGINS/MINECRAFT <https://fastlizard4.org/wiki/Download:Minecraft_Munin_plugins>
by FastLizard4 and the LizardNet Munin Plugins Development Team <https://gerrit.fastlizard4.org/r/#/admin/groups/17,members>

Copyright (C) 2023 by FastLizard4 and the LizardNet Munin Plugins Development Team.  Some rights reserved.

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

/*
 * This one isn't really Minecraft specific since it's just examining JVM heap information, but it doesn't seem like the
 * output of `jcmd` is standardized so this is likely system- and configuration-specific.
 */

/* An example of the jcmd output we're working with:
6698:
 garbage-first heap   total 6291456K, used 1410621K [0x0000000680000000, 0x0000000800000000)
  region size 8192K, 71 young (581632K), 1 survivors (8192K)
 Metaspace       used 143774K, committed 145792K, reserved 1179648K
  class space    used 20375K, committed 21184K, reserved 1048576K
 */

if ($argv[1] == "config") {
    echo "graph_args --base 1024 -l 0\n";
    echo "graph_title " . ((empty($_ENV['customTitle'])) ? "Minecraft JVM Heap Info" : $_ENV['customTitle'] . " JVM Heap Info") . "\n";
    echo "graph_vlabel Bytes (B)\n";
    echo "graph_category minecraft\n";

    // Order of the graph elements is important as that determines how things are stacked!
    echo "metaspace_class_used.label Class space used\n";
    echo "metaspace_class_used.draw AREASTACK\n";
    echo "metaspace_class_used.info Portion of metaspace used for classes\n";
    echo "metaspace_used.label Metaspace used total\n";
    echo "metaspace_used.draw AREASTACK\n";
    echo "metaspace_used.info Total metaspace used, including class space\n";
    echo "metaspace_class_committed.label Class space committed\n";
    echo "metaspace_class_committed.draw AREASTACK\n";
    echo "metaspace_class_committed.info Portion of metaspace committed for classes\n";
    echo "metaspace_committed.label Metaspace committed total\n";
    echo "metaspace_committed.draw AREASTACK\n";
    echo "metaspace_committed.info Total metaspace committed, including class space\n";
    echo "metaspace_class_reserved.label Class space reserved\n";
    echo "metaspace_class_reserved.draw AREASTACK\n";
    echo "metaspace_class_reserved.info Portion of metaspace reserved for classes\n";
    echo "metaspace_reserved.label Metaspace reserved total\n";
    echo "metaspace_reserved.draw AREASTACK\n";
    echo "metaspace_reserved.info Total metaspace reserved, including class space\n";
    echo "heap_young.label Young heap\n";
    echo "heap_young.draw AREASTACK\n";
    echo "heap_young.info Young generation region of the heap\n";
    echo "heap_survivor.label Survivor heap\n";
    echo "heap_survivor.draw AREASTACK\n";
    echo "heap_survivor.info Survivor generation region of the heap\n";
    echo "heap_used.label Total used heap\n";
    echo "heap_used.draw AREASTACK\n";
    echo "heap_used.info Total used heap memory, including the young and survivor regions\n";
    echo "heap_free.label Free heap\n";
    echo "heap_free.draw AREASTACK\n";
    echo "heap_free.info Unused but allocated heap memory\n";
    die();
}

// First, get the PID of the Minecraft server. It's stored in the file /home/minecraft/SID.running, where SID is the
// server ID from the serverId environment variable.
$pid = file_get_contents("/home/minecraft/" . $_ENV['serverId'] . ".running");
$pid = trim($pid);

// Next, get the output of jcmd for the PID we just got.
$jcmd = shell_exec("/opt/java/jdk-17.0.6+10/bin/jcmd $pid GC.heap_info");

// Parse out the contents of the output.
preg_match("/class\s+space\s+used\s+(\d+)K,\s+committed\s+(\d+)K,\s+reserved\s+(\d+)K/", $jcmd, $matches);
$metaspace_class_used = $matches[1] * 1024;
$metaspace_class_committed = $matches[2] * 1024;
$metaspace_class_reserved = $matches[3] * 1024;

preg_match("/Metaspace\s+used\s+(\d+)K,\s+committed\s+(\d+)K,\s+reserved\s+(\d+)K/", $jcmd, $matches);
$metaspace_used = $matches[1] * 1024;
$metaspace_committed = $matches[2] * 1024;
$metaspace_reserved = $matches[3] * 1024;

preg_match("/region\s+size\s+(\d+)K,\s+(\d+)\s+young\s+\((\d+)K\),\s+(\d+)\s+survivors\s+\((\d+)K\)/", $jcmd, $matches);
$region_size = $matches[1] * 1024;
$heap_young = $matches[3] * 1024;
$heap_survivor = $matches[5] * 1024;

preg_match("/garbage-first\s+heap\s+total\s+(\d+)K,\s+used\s+(\d+)K/", $jcmd, $matches);
$heap_total = $matches[1] * 1024;
$heap_used = $matches[2] * 1024;

// Some of these values need some additional processing. For example, class space is a subset of metaspace, so if we
// want to accurately report total metaspace usage, we need to subtract class space values from their corresponding
// metaspace values. Likewise, the total heap usage includes the young and survivor regions, so we need to subtract
// those from the total heap usage to get the actual heap usage broken down accurately in the graph.
$heap_free = $heap_total - $heap_used;
$heap_used_other = $heap_used - $heap_young - $heap_survivor;

$metaspace_class_reserved -= $metaspace_class_committed;
$metaspace_class_committed -= $metaspace_class_used;

$metaspace_reserved -= ($metaspace_committed + $metaspace_class_reserved);
$metaspace_committed -= ($metaspace_used + $metaspace_class_committed);
$metaspace_used -= $metaspace_class_used;

// Finally, output the data in the order we defined graph elements above.
echo "metaspace_class_used.value $metaspace_class_used\n";
echo "metaspace_used.value $metaspace_used\n";
echo "metaspace_class_committed.value $metaspace_class_committed\n";
echo "metaspace_committed.value $metaspace_committed\n";
echo "metaspace_class_reserved.value $metaspace_class_reserved\n";
echo "metaspace_reserved.value $metaspace_reserved\n";
echo "heap_young.value $heap_young\n";
echo "heap_survivor.value $heap_survivor\n";
echo "heap_used.value $heap_used_other\n";
echo "heap_free.value $heap_free\n";
