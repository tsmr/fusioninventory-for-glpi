<?php

/*
   ------------------------------------------------------------------------
   FusionInventory
   Copyright (C) 2010-2012 by the FusionInventory Development Team.

   http://www.fusioninventory.org/   http://forge.fusioninventory.org/
   ------------------------------------------------------------------------

   LICENSE

   This file is part of FusionInventory project.

   FusionInventory is free software: you can redistribute it and/or modify
   it under the terms of the GNU Affero General Public License as published by
   the Free Software Foundation, either version 3 of the License, or
   (at your option) any later version.

   FusionInventory is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
   GNU Affero General Public License for more details.

   You should have received a copy of the GNU Affero General Public License
   along with Behaviors. If not, see <http://www.gnu.org/licenses/>.

   ------------------------------------------------------------------------

   @package   FusionInventory
   @author    David Durieux
   @co-author
   @comment   Not translate this file, use https://www.transifex.net/projects/p/FusionInventory/
   @copyright Copyright (c) 2010-2012 FusionInventory team
   @license   AGPL License 3.0 or (at your option) any later version
              http://www.gnu.org/licenses/agpl-3.0-standalone.html
   @link      http://www.fusioninventory.org/
   @link      http://forge.fusioninventory.org/projects/fusioninventory-for-glpi/
   @since     2010

   ------------------------------------------------------------------------
 */


$LANG['plugin_fusioninventory']['agents'][15]="Agent state";
$LANG['plugin_fusioninventory']['agents'][17]="The agent is running";
$LANG['plugin_fusioninventory']['agents'][22]="Waiting";
$LANG['plugin_fusioninventory']['agents'][23]="Computer link";
$LANG['plugin_fusioninventory']['agents'][24]="Token";
$LANG['plugin_fusioninventory']['agents'][25]="Versiyon";
$LANG['plugin_fusioninventory']['agents'][27]="Agents modules";
$LANG['plugin_fusioninventory']['agents'][28]="Agent";
$LANG['plugin_fusioninventory']['agents'][30]="Impossible to communicate with agent!";
$LANG['plugin_fusioninventory']['agents'][31]="Force inventory";
$LANG['plugin_fusioninventory']['agents'][32]="Auto managenement dynamic of agents";
$LANG['plugin_fusioninventory']['agents'][33]="Auto managenement dynamic of agents (same subnet)";
$LANG['plugin_fusioninventory']['agents'][34]="Activation (by default)";
$LANG['plugin_fusioninventory']['agents'][35]="Device_id";
$LANG['plugin_fusioninventory']['agents'][36]="Agent modules";
$LANG['plugin_fusioninventory']['agents'][37]="locked";
$LANG['plugin_fusioninventory']['agents'][38]="Available";
$LANG['plugin_fusioninventory']['agents'][40]="Computer without known IP";
$LANG['plugin_fusioninventory']['agents'][41]="Service URL";
$LANG['plugin_fusioninventory']['agents'][42]="Useragent";
$LANG['plugin_fusioninventory']['agents'][43]="Informations for agent configuration";
$LANG['plugin_fusioninventory']['agents'][44]="Communication url of the server";
$LANG['plugin_fusioninventory']['agents'][45]="Exceptions";
$LANG['plugin_fusioninventory']['agents'][4]="Last contact";
$LANG['plugin_fusioninventory']['agents'][6]="disable";

$LANG['plugin_fusioninventory']['choice'][0]="Hayır";
$LANG['plugin_fusioninventory']['choice'][1]="Evet";
$LANG['plugin_fusioninventory']['choice'][2]="veya";
$LANG['plugin_fusioninventory']['choice'][3]="ve";

$LANG['plugin_fusioninventory']['codetasklog'][1]="Bad token, impossible to start agent";
$LANG['plugin_fusioninventory']['codetasklog'][2]="Agent stopped/crashed";
$LANG['plugin_fusioninventory']['codetasklog'][3]="Import denied";

$LANG['plugin_fusioninventory']['config'][0]="Inventory frequency (in hours)";

$LANG['plugin_fusioninventory']['credential'][1]="Credential for remote inventory";
$LANG['plugin_fusioninventory']['credential'][2]="Remote device inventory";
$LANG['plugin_fusioninventory']['credential'][3]="Credentials";
$LANG['plugin_fusioninventory']['credential'][4]="Credential";

$LANG['plugin_fusioninventory']['discovery'][5]="Number of imported devices";
$LANG['plugin_fusioninventory']['discovery'][9]="Number of devices not imported because type not defined";

$LANG['plugin_fusioninventory']['errors'][1]="PHP allow_url_fopen is off, can't wake agent to do inventory";
$LANG['plugin_fusioninventory']['errors'][22]="Unattended element in";
$LANG['plugin_fusioninventory']['errors'][2]="PHP allow_url_fopen is off, push mode can't work";
$LANG['plugin_fusioninventory']['errors'][50]="Your GLPI version not compatible, require 0.83.3";

$LANG['plugin_fusioninventory']['functionalities'][0]="Features";
$LANG['plugin_fusioninventory']['functionalities'][16]="SNMP authentication";
$LANG['plugin_fusioninventory']['functionalities'][17]="Database";
$LANG['plugin_fusioninventory']['functionalities'][18]="Dosyalar";
$LANG['plugin_fusioninventory']['functionalities'][19]="Please configure the SNMP authentication in the setup of the plugin";
$LANG['plugin_fusioninventory']['functionalities'][27]="SSL-only for agent";
$LANG['plugin_fusioninventory']['functionalities'][29]="List of fields to history";
$LANG['plugin_fusioninventory']['functionalities'][2]="General configuration";
$LANG['plugin_fusioninventory']['functionalities'][32]="Delete tasks after";
$LANG['plugin_fusioninventory']['functionalities'][60]="Clean history";
$LANG['plugin_fusioninventory']['functionalities'][6]="Legend";
$LANG['plugin_fusioninventory']['functionalities'][73]="Fields";
$LANG['plugin_fusioninventory']['functionalities'][74]="Values";
$LANG['plugin_fusioninventory']['functionalities'][75]="Locks";
$LANG['plugin_fusioninventory']['functionalities'][76]="Extra-debug";
$LANG['plugin_fusioninventory']['functionalities'][8]="Agent port";
$LANG['plugin_fusioninventory']['functionalities'][9]="Retention in days";

$LANG['plugin_fusioninventory']['iprange'][0]="Start of IP range";
$LANG['plugin_fusioninventory']['iprange'][1]="End of IP range";
$LANG['plugin_fusioninventory']['iprange'][2]="IP Ranges";
$LANG['plugin_fusioninventory']['iprange'][3]="Query";
$LANG['plugin_fusioninventory']['iprange'][7]="Bad IP";

$LANG['plugin_fusioninventory']['locks'][0]="Delete locks";
$LANG['plugin_fusioninventory']['locks'][1]="Add locks";

$LANG['plugin_fusioninventory']['menu'][1]="Agents management";
$LANG['plugin_fusioninventory']['menu'][2]="IP range configuration";
$LANG['plugin_fusioninventory']['menu'][3]="Menü";
$LANG['plugin_fusioninventory']['menu'][4]="Unknown device";
$LANG['plugin_fusioninventory']['menu'][5]="Authentication for remote devices (VMware)";
$LANG['plugin_fusioninventory']['menu'][6]="Remote devices to inventory (VMware)";
$LANG['plugin_fusioninventory']['menu'][7]="Running jobs";

$LANG['plugin_fusioninventory']['processes'][1]="PID";
$LANG['plugin_fusioninventory']['processes'][38]="Process number";

$LANG['plugin_fusioninventory']['profile'][0]="Rights management";
$LANG['plugin_fusioninventory']['profile'][2]="Agents";
$LANG['plugin_fusioninventory']['profile'][3]="Agent remote control";
$LANG['plugin_fusioninventory']['profile'][4]="Configuration";
$LANG['plugin_fusioninventory']['profile'][5]="Wake On LAN";
$LANG['plugin_fusioninventory']['profile'][6]="Unknown devices";

$LANG['plugin_fusioninventory']['rules'][10]="not exist";
$LANG['plugin_fusioninventory']['rules'][13]="Hard disk serial number";
$LANG['plugin_fusioninventory']['rules'][14]="Partition serial number";
$LANG['plugin_fusioninventory']['rules'][15]="UUID";
$LANG['plugin_fusioninventory']['rules'][16]="FusionInventory tag";
$LANG['plugin_fusioninventory']['rules'][18]="Device created";
$LANG['plugin_fusioninventory']['rules'][19]="Ignored import devices";
$LANG['plugin_fusioninventory']['rules'][20]="Rule import logs";
$LANG['plugin_fusioninventory']['rules'][21]="Import informations";
$LANG['plugin_fusioninventory']['rules'][2]="Equipment import and link rules";
$LANG['plugin_fusioninventory']['rules'][3]="Search GLPI equipment with the status";
$LANG['plugin_fusioninventory']['rules'][4]="Destination of equipment entity";
$LANG['plugin_fusioninventory']['rules'][5]="FusionInventory link";
$LANG['plugin_fusioninventory']['rules'][6]="Link if possible, else import denied";
$LANG['plugin_fusioninventory']['rules'][7]="Link if possible, else create device";
$LANG['plugin_fusioninventory']['rules'][8]="Send";
$LANG['plugin_fusioninventory']['rules'][9]="exist";

$LANG['plugin_fusioninventory']['rulesengine'][152]="Assets to import";

$LANG['plugin_fusioninventory']['setup'][16]="Documentation";
$LANG['plugin_fusioninventory']['setup'][17]="Other FusionInventory plugins (fusinv...) must be uninstalled before removing the FusionInventory plugin";

$LANG['plugin_fusioninventory']['task'][0]="Task";
$LANG['plugin_fusioninventory']['task'][14]="Scheduled date";
$LANG['plugin_fusioninventory']['task'][15]="Yeni eylem";
$LANG['plugin_fusioninventory']['task'][17]="Periodicity";
$LANG['plugin_fusioninventory']['task'][18]="Görevler";
$LANG['plugin_fusioninventory']['task'][19]="Running tasks";
$LANG['plugin_fusioninventory']['task'][1]="Task management";
$LANG['plugin_fusioninventory']['task'][20]="Finished tasks";
$LANG['plugin_fusioninventory']['task'][21]="Action on this device";
$LANG['plugin_fusioninventory']['task'][22]="Only scheduled tasks";
$LANG['plugin_fusioninventory']['task'][24]="Number of trials";
$LANG['plugin_fusioninventory']['task'][25]="Time between 2 trials (in minutes)";
$LANG['plugin_fusioninventory']['task'][26]="Modül";
$LANG['plugin_fusioninventory']['task'][27]="Definition";
$LANG['plugin_fusioninventory']['task'][28]="Eylem";
$LANG['plugin_fusioninventory']['task'][29]="Tip";
$LANG['plugin_fusioninventory']['task'][2]="Job";
$LANG['plugin_fusioninventory']['task'][30]="Selection";
$LANG['plugin_fusioninventory']['task'][31]="Time between task start and start this action";
$LANG['plugin_fusioninventory']['task'][32]="Force the end";
$LANG['plugin_fusioninventory']['task'][33]="Communication type";
$LANG['plugin_fusioninventory']['task'][35]="dakika";
$LANG['plugin_fusioninventory']['task'][36]="saat";
$LANG['plugin_fusioninventory']['task'][37]="gün";
$LANG['plugin_fusioninventory']['task'][38]="ay";
$LANG['plugin_fusioninventory']['task'][39]="Unable to run task because some jobs is running yet!";
$LANG['plugin_fusioninventory']['task'][40]="Force start";
$LANG['plugin_fusioninventory']['task'][41]="Server contacts the agent (push)";
$LANG['plugin_fusioninventory']['task'][42]="Agent contacts the server (pull)";
$LANG['plugin_fusioninventory']['task'][43]="Communication mode";
$LANG['plugin_fusioninventory']['task'][44]="See all informations of task";
$LANG['plugin_fusioninventory']['task'][45]="Advanced options";
$LANG['plugin_fusioninventory']['task'][46]="Reinitialization";
$LANG['plugin_fusioninventory']['task'][47]="Unique id";
$LANG['plugin_fusioninventory']['task'][48]="To inventory";
$LANG['plugin_fusioninventory']['task'][49]="Advanced mode";
$LANG['plugin_fusioninventory']['task'][50]="End Task";
$LANG['plugin_fusioninventory']['task'][51]="Action after finish running task";
$LANG['plugin_fusioninventory']['task'][52]="Delete this task and finish";
$LANG['plugin_fusioninventory']['task'][53]="Finish";
$LANG['plugin_fusioninventory']['task'][54]="Run again this task";
$LANG['plugin_fusioninventory']['task'][55]="No agent allowed to run ESX inventory";
$LANG['plugin_fusioninventory']['task'][56]="Planned for running";
$LANG['plugin_fusioninventory']['task'][58]="In error";

$LANG['plugin_fusioninventory']['taskjoblog'][1]="Started";
$LANG['plugin_fusioninventory']['taskjoblog'][2]="Tamam";
$LANG['plugin_fusioninventory']['taskjoblog'][3]="Error / rescheduled";
$LANG['plugin_fusioninventory']['taskjoblog'][4]="Hata";
$LANG['plugin_fusioninventory']['taskjoblog'][5]="bilinmeyen";
$LANG['plugin_fusioninventory']['taskjoblog'][6]="Running";
$LANG['plugin_fusioninventory']['taskjoblog'][7]="Prepared";
$LANG['plugin_fusioninventory']['taskjoblog'][8]="See all executions";
$LANG['plugin_fusioninventory']['taskjoblog'][9]="View logs of this execution";

$LANG['plugin_fusioninventory']['title'][0]="FusionInventory";
$LANG['plugin_fusioninventory']['title'][1]="FusInv";

$LANG['plugin_fusioninventory']['unknown'][2]="Approved devices";
$LANG['plugin_fusioninventory']['unknown'][4]="Network hub";
$LANG['plugin_fusioninventory']['unknown'][5]="Import unknown device into asset";

$LANG['plugin_fusioninventory']['wizard'][0]="Action choice";
$LANG['plugin_fusioninventory']['wizard'][10]="Choice (netdiscovery or inventory)";
$LANG['plugin_fusioninventory']['wizard'][11]="Breadcrumb";
$LANG['plugin_fusioninventory']['wizard'][12]="Discover the network";
$LANG['plugin_fusioninventory']['wizard'][13]="Inventory devices";
$LANG['plugin_fusioninventory']['wizard'][14]="Welcome in FusionInventory. Begin configuration?";
$LANG['plugin_fusioninventory']['wizard'][15]="Computers and peripherals";
$LANG['plugin_fusioninventory']['wizard'][16]="ESX servers";
$LANG['plugin_fusioninventory']['wizard'][17]="Network devices and printers";
$LANG['plugin_fusioninventory']['wizard'][18]="Wizard";
$LANG['plugin_fusioninventory']['wizard'][1]="Type of device to inventory";
$LANG['plugin_fusioninventory']['wizard'][2]="Import options";
$LANG['plugin_fusioninventory']['wizard'][3]="Entity rules";
$LANG['plugin_fusioninventory']['wizard'][4]="Agent configuration";
$LANG['plugin_fusioninventory']['wizard'][5]="Authentication management";
$LANG['plugin_fusioninventory']['wizard'][6]="ESX servers management";
$LANG['plugin_fusioninventory']['wizard'][7]="Run tasks";
$LANG['plugin_fusioninventory']['wizard'][8]="Tasks running result";

$LANG['plugin_fusioninventory']['xml'][0]="XML";


$LANG['plugin_fusioninventory']['antivirus'][0]="Antivirüs";
$LANG['plugin_fusioninventory']['antivirus'][1]="No antivirus on this computer";
$LANG['plugin_fusioninventory']['antivirus'][2]="Versiyon";
$LANG['plugin_fusioninventory']['antivirus'][3]="Up to date";

$LANG['plugin_fusioninventory']['bios'][0]="BIOS";

$LANG['plugin_fusioninventory']['blacklist'][0]="blacklisted value";
$LANG['plugin_fusioninventory']['blacklist'][1]="New value to blacklist";

$LANG['plugin_fusioninventory']['computer'][0]="Last inventory";
$LANG['plugin_fusioninventory']['computer'][1]="Owner";
$LANG['plugin_fusioninventory']['computer'][2]="Company";

$LANG['plugin_fusioninventory']['importxml'][0]="Import XML file from an Agent";
$LANG['plugin_fusioninventory']['importxml'][1]="Computer injected into GLPI";
$LANG['plugin_fusioninventory']['importxml'][2]="No file to import!";
$LANG['plugin_fusioninventory']['importxml'][3]="XML file not valid!";

$LANG['plugin_fusioninventory']['integrity'][0]="Only in GLPI (check to delete)";
$LANG['plugin_fusioninventory']['integrity'][1]="Only in last inventory (check to import)";

$LANG['plugin_fusioninventory']['menu'][8]="Import agent XML file";
$LANG['plugin_fusioninventory']['menu'][9]="Criteria rules";
$LANG['plugin_fusioninventory']['menu'][10]="BlackList";
$LANG['plugin_fusioninventory']['menu'][11]="Data integrity check";

$LANG['plugin_fusioninventory']['profile'][7]="Existance criteria";
$LANG['plugin_fusioninventory']['profile'][8]="XML file manual import";
$LANG['plugin_fusioninventory']['profile'][9]="Fields blacklist";

$LANG['plugin_fusioninventory']['rule'][20]="Computer existent criteria rules";
$LANG['plugin_fusioninventory']['rule'][100]="Entity rules";
$LANG['plugin_fusioninventory']['rule'][102]="Ignore in FusionInventory import";
$LANG['plugin_fusioninventory']['rule'][21]="Existant criterium";
$LANG['plugin_fusioninventory']['rule'][22]="Serial Number";
$LANG['plugin_fusioninventory']['rule'][30]="Import in asset";
$LANG['plugin_fusioninventory']['rule'][31]="Import in unknown devices";
$LANG['plugin_fusioninventory']['rule'][23]="MAC address";
$LANG['plugin_fusioninventory']['rule'][24]="Microsoft product key";
$LANG['plugin_fusioninventory']['rule'][25]="Computer model";
$LANG['plugin_fusioninventory']['rule'][26]="Hard disk serial number";
$LANG['plugin_fusioninventory']['rule'][27]="Partitions serial number";
$LANG['plugin_fusioninventory']['rule'][28]="Tag";

$LANG['plugin_fusioninventory']['setup'][17]="Plugin FusionInventory INVENTORY need plugin FusionInventory activated before activation.";
$LANG['plugin_fusioninventory']['setup'][18]="Plugin FusionInventory INVENTORY need plugin FusionInventory activated before uninstall.";
$LANG['plugin_fusioninventory']['setup'][20]="Import options";
$LANG['plugin_fusioninventory']['setup'][21]="Components";
$LANG['plugin_fusioninventory']['setup'][22]="Global import";
$LANG['plugin_fusioninventory']['setup'][23]="No import";
$LANG['plugin_fusioninventory']['setup'][24]="Unique import";
$LANG['plugin_fusioninventory']['setup'][25]="Registry";
$LANG['plugin_fusioninventory']['setup'][26]="Processes";
$LANG['plugin_fusioninventory']['setup'][27]="Unique import on serial number";
$LANG['plugin_fusioninventory']['setup'][28]="Automatic computers transfer";
$LANG['plugin_fusioninventory']['setup'][29]="Model for automatic computers transfer in an other entity";
$LANG['plugin_fusioninventory']['setup'][30]="Network drives";
$LANG['plugin_fusioninventory']['setup'][31]="Virtual network card";
$LANG['plugin_fusioninventory']['setup'][32]="This option will not import this item";
$LANG['plugin_fusioninventory']['setup'][33]="This option will merge items with same name to reduce number of items if this management isn't important";
$LANG['plugin_fusioninventory']['setup'][34]="This option will create one item for each item found";
$LANG['plugin_fusioninventory']['setup'][35]="This option will create one item for each item have serial number";
$LANG['plugin_fusioninventory']['setup'][36]="Default status";

$LANG['plugin_fusioninventory']['title'][2]="VMware host remote inventory";

$LANG['plugin_fusioninventory']['vmwareesx'][0]="VMware host";

?>