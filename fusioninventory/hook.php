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
   @copyright Copyright (c) 2010-2012 FusionInventory team
   @license   AGPL License 3.0 or (at your option) any later version
              http://www.gnu.org/licenses/agpl-3.0-standalone.html
   @link      http://www.fusioninventory.org/
   @link      http://forge.fusioninventory.org/projects/fusioninventory-for-glpi/
   @since     2010

   ------------------------------------------------------------------------
 */

function plugin_fusioninventory_getAddSearchOptions($itemtype) {

   $sopt = array();
   if ($itemtype == 'Computer') {

         $sopt[5150]['table']     = 'glpi_plugin_fusioninventory_inventorycomputerlibserialization';
         $sopt[5150]['field']     = 'last_fusioninventory_update';
         $sopt[5150]['linkfield'] = '';
         $sopt[5150]['name']      = __('FusInv')." - ".
            __('Last inventory');

         $sopt[5150]['datatype']  = 'datetime';
         $sopt[5150]['itemlink_type'] = 'PluginFusioninventoryInventoryComputerLib';

         $sopt[5151]['table']     = 'glpi_plugin_fusioninventory_inventorycomputerantivirus';
         $sopt[5151]['field']     = 'name';
         $sopt[5151]['linkfield'] = '';
         $sopt[5151]['name']      = 'Antivirus name';
         $sopt[5151]['datatype']  = 'text';

         $sopt[5152]['table']     = 'glpi_plugin_fusioninventory_inventorycomputerantivirus';
         $sopt[5152]['field']     = 'version';
         $sopt[5152]['linkfield'] = '';
         $sopt[5152]['name']      = 'Antivirus version';
         $sopt[5152]['datatype']  = 'text';

         $sopt[5153]['table']     = 'glpi_plugin_fusioninventory_inventorycomputerantivirus';
         $sopt[5153]['field']     = 'is_active';
         $sopt[5153]['linkfield'] = '';
         $sopt[5153]['name']      = 'Antivirus activé';
         $sopt[5153]['datatype']  = 'bool';

         $sopt[5154]['table']     = 'glpi_plugin_fusioninventory_inventorycomputerantivirus';
         $sopt[5154]['field']     = 'uptodate';
         $sopt[5154]['linkfield'] = '';
         $sopt[5154]['name']      = 'Antivirus à jour';
         $sopt[5154]['datatype']  = 'bool';

         $sopt[5155]['table']     = 'glpi_plugin_fusioninventory_inventorycomputercomputers';
         $sopt[5155]['field']     = 'bios_date';
         $sopt[5155]['linkfield'] = '';
         $sopt[5155]['name']      = __('BIOS')."-".__('Date');

         $sopt[5155]['datatype']  = 'date';

         $sopt[5156]['table']     = 'glpi_plugin_fusioninventory_inventorycomputercomputers';
         $sopt[5156]['field']     = 'bios_version';
         $sopt[5156]['linkfield'] = '';
         $sopt[5156]['name']      = __('BIOS')."-".__('Version');


         $sopt[5157]['table']     = 'glpi_plugin_fusioninventory_inventorycomputercomputers';
         $sopt[5157]['field']     = 'operatingsystem_installationdate';
         $sopt[5157]['linkfield'] = '';
         $sopt[5157]['name']      = __('Operating system')." - ".__('Installation')." (".strtolower(__('Date')).")";
         $sopt[5157]['datatype']  = 'date';

         $sopt[5158]['table']     = 'glpi_plugin_fusioninventory_inventorycomputercomputers';
         $sopt[5158]['field']     = 'winowner';
         $sopt[5158]['linkfield'] = '';
         $sopt[5158]['name']      = __('Owner');


         $sopt[5159]['table']     = 'glpi_plugin_fusioninventory_inventorycomputercomputers';
         $sopt[5159]['field']     = 'wincompany';
         $sopt[5159]['linkfield'] = '';
         $sopt[5159]['name']      = __('Company');


   }
   return $sopt;
}



function plugin_fusioninventory_giveItem($type,$id,$data,$num) {

   $searchopt = &Search::getOptions($type);
   $table = $searchopt[$id]["table"];
   $field = $searchopt[$id]["field"];

   switch ($table.'.'.$field) {

      case "glpi_plugin_fusioninventory_tasks.id" :
         // Get progression bar
         return "";
         break;

      case "glpi_plugin_fusioninventory_taskjobs.status":
         $pfTaskjobstate = new PluginFusioninventoryTaskjobstate();
         return $pfTaskjobstate->stateTaskjob($data['id'], '200', 'htmlvar', 'simple');
         break;

      case "glpi_plugin_fusioninventory_agents.version":
         $array = importArrayFromDB($data['ITEM_'.$num]);
         $input = "";
         foreach ($array as $name=>$version){
            $input .= "<strong>".$name."</strong> : ".$version."<br/>";
         }
         $input .= "*";
         $input = str_replace("<br/>*", "", $input);
         return $input;
         break;

      case "glpi_plugin_fusioninventory_credentials.itemtype":
        if ($label = PluginFusioninventoryCredential::getLabelByItemtype($data['ITEM_'.$num])) {
           return $label;
        } else {
           return '';
        }
        break;

     case 'glpi_plugin_fusioninventory_taskjoblogs.state':
        $pfTaskjoblog = new PluginFusioninventoryTaskjoblog();
        return $pfTaskjoblog->getDivState($data['ITEM_'.$num]);
        break;

      case 'glpi_plugin_fusioninventory_taskjoblogs.comment':
         $comment = $data['ITEM_'.$num];
         $matches = array();
         // Search for replace [[itemtype::items_id]] by link
         preg_match_all("/\[\[(.*)\:\:(.*)\]\]/", $comment, $matches);
         foreach($matches[0] as $num=>$commentvalue) {
            $classname = $matches[1][$num];
            if ($classname != '') {
               $Class = new $classname;
               $Class->getFromDB($matches[2][$num]);
               $comment = str_replace($commentvalue, $Class->getLink(), $comment);
            }
         }
         $comment = str_replace(",[", "<br/>[", $comment);
         return $comment;
         break;

   }

   if ($table == "glpi_plugin_fusioninventory_agentmodules") {
      $pfAgentmodule = new PluginFusioninventoryAgentmodule();
      $a_modules = $pfAgentmodule->find();
      foreach ($a_modules as $data2) {
         if ($table.".".$field == "glpi_plugin_fusioninventory_agentmodules.".$data2['modulename']) {
            if (strstr($data["ITEM_".$num."_0"], '"'.$data['id'].'"')) {
               if ($data['ITEM_'.$num] == '0') {
                  return Dropdown::getYesNo('1');
               } else {
                  return Dropdown::getYesNo('0');
               }

            }
            return Dropdown::getYesNo($data['ITEM_'.$num]);
         }
      }
   }

   return "";
}



function plugin_fusioninventory_searchOptionsValues($item) {
   global $CFG_GLPI,$DB;

   if ($item['searchoption']['table'] == 'glpi_plugin_fusioninventory_taskjoblogs'
           AND $item['searchoption']['field'] == 'state') {
      $pfTaskjoblog = new PluginFusioninventoryTaskjoblog();
      $elements = $pfTaskjoblog->dropdownStateValues();
      Dropdown::showFromArray($item['name'], $elements, array('value'=>$item['value']));
      return true;
   } else if ($item['searchoption']['table'] == 'glpi_plugin_fusioninventory_taskjobstates'
           AND $item['searchoption']['field'] == 'uniqid') {
      $elements = array();
      $query = "SELECT * FROM `".$item['searchoption']['table']."`
      GROUP BY `uniqid`
      ORDER BY `uniqid`";
      $result=$DB->query($query);
      while ($data=$DB->fetch_array($result)) {
         $elements[$data['uniqid']] = $data['uniqid'];
      }
      Dropdown::showFromArray($item['name'], $elements, array('value'=>$item['value']));
      return true;
   }
}



// Define Dropdown tables to be manage in GLPI :
function plugin_fusioninventory_getDropdown() {
   return array ();
}

/* Cron */
function cron_plugin_fusioninventory() {
//   TODO :Disable for the moment (may be check if functions is good or not
//   $ptud = new PluginFusioninventoryUnknownDevice;
//   $ptud->CleanOrphelinsConnections();
//   $ptud->FusionUnknownKnownDevice();
//   TODO : regarder les 2 lignes juste en dessous !!!!!
//   #Clean server script processes history
//   $pfisnmph = new PluginFusioninventoryNetworkPortLog;
//   $pfisnmph->cronCleanHistory();

   return 1;
}



function plugin_fusioninventory_install() {
   global $DB;

   require_once (GLPI_ROOT . "/plugins/fusioninventory/install/update.php");
   $version_detected = pluginFusioninventoryGetCurrentVersion(PLUGIN_FUSIONINVENTORY_VERSION);

   if ((isset($version_detected))
      AND ($version_detected != PLUGIN_FUSIONINVENTORY_VERSION)
        AND $version_detected!='0') {
      pluginFusioninventoryUpdate($version_detected);
   } else if ((isset($version_detected)) AND ($version_detected == PLUGIN_FUSIONINVENTORY_VERSION)) {

   } else {
      require_once (GLPI_ROOT . "/plugins/fusioninventory/install/install.php");
      pluginFusioninventoryInstall(PLUGIN_FUSIONINVENTORY_VERSION);
   }

   return true;
}



// Uninstall process for plugin : need to return true if succeeded
function plugin_fusioninventory_uninstall() {
   require_once(GLPI_ROOT . "/plugins/fusioninventory/inc/setup.class.php");
   return PluginFusioninventorySetup::uninstall();
}



// Define headings added by the plugin //
function plugin_get_headings_fusioninventory($item,$withtemplate) {

   switch (get_class($item)) {
      case 'Computer' :
         $ong = array();
         if ($withtemplate) { // new object / template case
            return array();
         } else { // Non template case / editing an existing object

//            if (PluginFusioninventoryProfile::haveRight("fusioninventory", "task","r")) {
//               $array[3] = __('FusInv')." ".__('Tasks');

//            }

            return $ong;
         }
         break;


      case 'NetworkEquipment' :
         if ($withtemplate) { // new object / template case
            return array();
         } else { // Non template case / editing an existing object
            $array = array ();
            if (PluginFusioninventoryProfile::haveRight("fusioninventory", "task","r")) {
               $array[2] = __('FusInv')." ".__('Tasks');

            }
            return $array;
         }
         break;

      case 'Printer' :
         if ($withtemplate) { // new object / template case
            return array();
         } else { // Non template case / editing an existing object
            $array = array ();
            if (PluginFusioninventoryProfile::haveRight("fusioninventory", "task","r")) {
               $array[2] = __('FusInv')." ".__('Tasks');

            }
            return $array;
         }
         break;

      case 'Profile' :
         if ($withtemplate) { // new object / template case
            return array();
         } else { // Non template case / editing an existing object
            $array = array ();
            if (PluginFusioninventoryModule::getModuleId("fusioninventory")) {
               $array[1] = __('FusionInventory');

            }
            return $array;
         }
         break;

      case 'PluginFusioninventoryUnknownDevice' :
         $array = array ();
         if ($_GET['id'] > 0) {
            if (PluginFusioninventoryProfile::haveRight("fusioninventory", "unknowndevice","r")) {
               $array[1] = __('FusInv')." ".__('XML');

            }
         }
         return $array;
         break;

      case 'PluginFusioninventoryCredentialIp':
         $array = array();
         if (PluginFusioninventoryProfile::haveRight("fusioninventory", "task","r")) {
            $array[1] = __('FusInv')." ".__('Tasks');

         }
         return $array;
         break;

   }
   return false;
}

// Define headings actions added by the plugin
//function plugin_headings_actions_fusioninventory($type) {
function plugin_headings_actions_fusioninventory($item) {
//   switch ($type) {
   switch (get_class($item)) {
      case 'Computer' :
         $array = array();
         $array[3] = "plugin_headings_fusioninventory_tasks";
         return $array;
         break;

      case 'Printer' :
         $array = array();

         $array[2] = "plugin_headings_fusioninventory_tasks";
         return $array;
         break;

      case 'NetworkEquipment' :
         $array = array();

         $array[2] = "plugin_headings_fusioninventory_tasks";
         return $array;
         break;

      case 'Profile' :
         $array = array();
         $array[1] = "plugin_headings_fusioninventory";
         return $array;
         break;

      case 'PluginFusioninventoryUnknownDevice' :
         $array = array ();
         $array[1] = "plugin_headings_fusioninventory_xml";
         return $array;
         break;

      case 'PluginFusioninventoryCredentialIp':
         $array = array();
         $array[1] = "plugin_headings_fusioninventory_tasks";
         return $array;
         break;
   }
   return false;
}



function plugin_headings_fusioninventory_tasks($item, $itemtype='', $items_id=0) {
   if ($itemtype == '') {
      $itemtype = get_Class($item);
      $items_id = $item->getField('id');
   }
   if ($itemtype == 'Computer') {
      // Possibility to remote agent
      $allowed = PluginFusioninventoryTaskjob::getAllowurlfopen(1);
      if ($allowed) {
         $pfAgent = new PluginFusioninventoryAgent();
         $pfAgent->forceRemoteAgent();
      }
   }

   $pfTaskjob = new PluginFusioninventoryTaskjob();
   $pfTaskjob->manageTasksByObject($itemtype, $items_id);

}




function plugin_headings_fusioninventory($item, $withtemplate=0) {
   global $CFG_GLPI;

   switch (get_class($item)) {
      case 'Profile' :

         $pfProfile = new PluginFusioninventoryProfile();
//         if (!$prof->GetfromDB($id)) {
//            PluginFusioninventoryDb::createaccess($id);
//         }
         $pfProfile->showProfileForm($item->getField('id'), $CFG_GLPI['root_doc']."/plugins/fusioninventory/front/profile.php");
      break;
   }
}



function plugin_headings_fusioninventory_xml($item) {

   $id = $_POST['id'];

   $folder = substr($id,0,-1);
   if (empty($folder)) {
      $folder = '0';
   }
   if (file_exists(GLPI_PLUGIN_DOC_DIR."/fusioninventory/xml/PluginFusioninventoryUnknownDevice/".$folder."/".$id)) {
      $xml = file_get_contents(GLPI_PLUGIN_DOC_DIR."/fusioninventory/xml/PluginFusioninventoryUnknownDevice/".$folder."/".$id);
      $xml = str_replace("<", "&lt;", $xml);
      $xml = str_replace(">", "&gt;", $xml);
      $xml = str_replace("\n", "<br/>", $xml);
      echo "<table class='tab_cadre_fixe' cellpadding='1'>";
      echo "<tr>";
      echo "<th>".__('XML');

      echo " (".__('Last inventory')."&nbsp;: " . Html::convDateTime(date("Y-m-d H:i:s", filemtime(GLPI_PLUGIN_DOC_DIR."/fusioninventory/xml/PluginFusioninventoryUnknownDevice/".$folder."/".$id))).")";
      echo "</th>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td width='130'>";
      echo "<pre width='130'>".$xml."</pre>";
      echo "</td>";
      echo "</tr>";
      echo "</table>";
   }
}



function plugin_fusioninventory_MassiveActions($type) {

   switch ($type) {

      case "Computer":
         return array (
            "plugin_fusioninventory_manage_locks" => __('Locks')

         );
         break;

      case "NetworkEquipment":
         return array (
            "plugin_fusioninventory_manage_locks" => __('Locks')

         );
         break;

      case "Printer":
         return array (
            "plugin_fusioninventory_manage_locks" => __('Locks')

         );
         break;

      case 'PluginFusioninventoryAgent';
         $pfAgentmodule = new PluginFusioninventoryAgentmodule();
         $a_modules = $pfAgentmodule->find();
         $array = array();
         foreach ($a_modules as $data) {
            $array["plugin_fusioninventory_agentmodule".$data["modulename"]] = __('Module')." - ".$data['modulename'];
         }
         $array['plugin_fusioninventory_transfert'] = __('Transfer');

         return $array;
         break;

      case "PluginFusioninventoryUnknownDevice";
         return array (
            "plugin_fusioninventory_unknown_import" => __('Import')

         );
         break;

      case "PluginFusioninventoryTask";
         return array (
            'plugin_fusioninventory_transfert' => __('Transfer')

         );
         break;

      case 'PluginFusioninventoryTaskjob':
         return array(
            'plugin_fusioninventory_task_forceend' =>
               __('Force the end')

         );
         break;

   }
   return array ();
}

function plugin_fusioninventory_MassiveActionsFieldsDisplay($options=array()) {

   $table = $options['options']['table'];
   $field = $options['options']['field'];
   $linkfield = $options['options']['linkfield'];


   switch ($table.".".$field) {

      case "glpi_plugin_fusioninventory_unknowndevices.item_type":
         $type_list = array();
         $type_list[] = 'Computer';
         $type_list[] = 'NetworkEquipment';
         $type_list[] = 'Printer';
         $type_list[] = 'Peripheral';
         $type_list[] = 'Phone';
         Dropdown::dropdownTypes($linkfield,0,$type_list);
         return true;
         break;

   }

//   switch ($table) {
//
//      case 'glpi_plugin_fusioninventory_agentmodules':
//         $pfAgentmodule = new PluginFusioninventoryAgentmodule();
//         $a_modules = $pfAgentmodule->find();
//         foreach ($a_modules as $data) {
//            if ($table.".".$field == "glpi_plugin_fusioninventory_agentmodules.".$data['modulename']) {
//               Dropdown::showYesNo($field);
//               return true;
//            }
//         }
//         break;
//
//    }
   return false;
}



function plugin_fusioninventory_MassiveActionsDisplay($options=array()) {

   switch ($options['itemtype']) {
      case "Computer":
         switch ($options['action']) {
            case "plugin_fusioninventory_manage_locks" :
               $pfil = new PluginFusioninventoryLock;
               $pfil->showForm($_SERVER["PHP_SELF"], "Computer");
               break;
         }
         break;

      case "NetworkEquipment":
         switch ($options['action']) {
            case "plugin_fusioninventory_manage_locks" :
               $pfil = new PluginFusioninventoryLock;
               $pfil->showForm($_SERVER["PHP_SELF"], "NetworkEquipment");
               break;
         }
         break;

      case "Printer":
         switch ($options['action']) {
            case "plugin_fusioninventory_manage_locks" :
               $pfil = new PluginFusioninventoryLock();
               $pfil->showForm($_SERVER["PHP_SELF"], "Printer");
               break;
         }
         break;

      case 'PluginFusioninventoryAgent':
         if (strstr($options['action'], 'plugin_fusioninventory_agentmodule')) {
            $pfAgentmodule = new PluginFusioninventoryAgentmodule();
            $a_modules = $pfAgentmodule->find();
            foreach ($a_modules as $data) {
               if ($options['action'] == "plugin_fusioninventory_agentmodule".$data['modulename']) {
                  Dropdown::showYesNo($options['action']);
                  echo "&nbsp;<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"" . __('Post') . "\" >";
               }
            }
            break;
         }
         if ($options['action'] == "plugin_fusioninventory_transfert") {
               Dropdown::show('Entity');
               echo "&nbsp;<input type='submit' name='massiveaction' class='submit' ".
                     "value='".__('Post')."'>";
               break;
         }
         break;

      case "PluginFusioninventoryUnknownDevice";
         if ($options['action'] == "plugin_fusioninventory_unknown_import") {
            if (PluginFusioninventoryProfile::haveRight("fusioninventory", "unknowndevice","w")) {
               echo "<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"" . __('Post') . "\" >";
            }
         }
         break;

     case 'PluginFusioninventoryTask':
         if ($options['action'] == "plugin_fusioninventory_transfert") {
               Dropdown::show('Entity');
               echo "&nbsp;<input type='submit' name='massiveaction' class='submit' ".
                     "value='".__('Post')."'>";
               break;
         }
         break;

      case 'PluginFusioninventoryTaskjob':
         echo "&nbsp;<input type='submit' name='massiveaction' class='submit' ".
               "value='".__('Post')."'>";
         break;

   }
   return "";
}

function plugin_fusioninventory_MassiveActionsProcess($data) {

   switch ($data['action']) {
      case "plugin_fusioninventory_manage_locks" :
         if (($data['itemtype'] == "NetworkEquipment")
                 OR ($data['itemtype'] == "Printer")
                 OR ($data['itemtype'] == "Computer")) {

            foreach ($data['item'] as $key => $val) {
               if ($val == 1) {
                  if (isset($data["lockfield_fusioninventory"])&&count($data["lockfield_fusioninventory"])){
                     $tab=PluginFusioninventoryLock::exportChecksToArray($data["lockfield_fusioninventory"]);
                        PluginFusioninventoryLock::setLockArray($data['type'], $key, $tab, $data['actionlock']);
                  }
               }
            }
         }
         break;

      case "plugin_fusioninventory_unknown_import" :
         if (PluginFusioninventoryProfile::haveRight("fusioninventory", "unknowndevice","w")) {
            $Import = 0;
            $NoImport = 0;
            $pfUnknownDevice = new PluginFusioninventoryUnknownDevice();
            foreach ($data['item'] as $key => $val) {
               if ($val == 1) {
                  list($Import, $NoImport) = $pfUnknownDevice->import($key,$Import,$NoImport);
               }
            }
             Session::addMessageAfterRedirect(__('Number of imported devices')." : ".$Import);
             Session::addMessageAfterRedirect(__('Number of devices not imported because type not defined')." : ".$NoImport);
         }
         break;

      case "plugin_fusioninventory_transfert" :
         if ($data['itemtype'] == 'PluginFusioninventoryAgent') {
            foreach ($data["item"] as $key => $val) {
               if ($val == 1) {

                  $pfAgent = new PluginFusioninventoryAgent();
                  if ($pfAgent->getFromDB($key)) {
                     $input = array();
                     $input['id'] = $key;
                     $input['entities_id'] = $data['entities_id'];
                     $pfAgent->update($input);
                  }
               }
            }
         } else if ($data['itemtype'] == 'PluginFusioninventoryTask') {
            $pfTask = new PluginFusioninventoryTask();
            $pfTaskjob = new PluginFusioninventoryTaskjob();
            foreach ($data["item"] as $key => $val) {
               if ($val == 1) {
                  if ($pfTask->getFromDB($key)) {
                     $a_taskjobs = $pfTaskjob->find("`plugin_fusioninventory_tasks_id`='".$key."'");
                     foreach ($a_taskjobs as $data1) {
                        $input = array();
                        $input['id'] = $data1['id'];
                        $input['entities_id'] = $data['entities_id'];
                        $pfTaskjob->update($input);
                     }
                     $input = array();
                     $input['id'] = $key;
                     $input['entities_id'] = $data['entities_id'];
                     $pfTask->update($input);
                  }
               }
            }

         }
         break;
      case 'plugin_fusioninventory_task_forceend':

         $pluginFusioninventoryTaskjob = new PluginFusioninventoryTaskjob();
         foreach( $data["item"] as $key => $val) {
            $pluginFusioninventoryTaskjob->getFromDB($key);
            $pluginFusioninventoryTaskjob->forceEnd();
         }
         break;
   }

   if (strstr($data['action'], 'plugin_fusioninventory_agentmodule')) {
      $pfAgentmodule = new PluginFusioninventoryAgentmodule();
      $a_modules = $pfAgentmodule->find();
      foreach ($a_modules as $data2) {
         if ($data['action'] == "plugin_fusioninventory_agentmodule".$data2['modulename']) {
            foreach ($data['item'] as $items_id => $val) {
               if ($data["plugin_fusioninventory_agentmodule".$data2['modulename']] == $data2['is_active']) {
                  // Remove from exceptions
                  $a_exceptions = importArrayFromDB($data2['exceptions']);
                  if (in_array($items_id, $a_exceptions)) {
                     foreach ($a_exceptions as $key=>$value) {
                        if ($value == $items_id) {
                           unset($a_exceptions[$key]);
                        }
                     }
                  }
                  $data2['exceptions'] = exportArrayToDB($a_exceptions);
               } else {
                  // Add to exceptions
                  $a_exceptions = importArrayFromDB($data2['exceptions']);
                  if (!in_array($items_id, $a_exceptions)) {
                     $a_exceptions[] = (string)$items_id;
                  }
                  $data2['exceptions'] = exportArrayToDB($a_exceptions);
               }
            }
            $pfAgentmodule->update($data2);
         }
      }
   }

}

// How to display specific update fields ?
// Massive Action functions
//function plugin_fusioninventory_MassiveActionsFieldsDisplay($type,$table,$field,$linkfield) {
//   // Table fields
//   //echo $table.".".$field."<br/>";
//   switch ($table.".".$field) {
//      case 'glpi_plugin_fusioninventory_agents.id' :
//         Dropdown::show("PluginFusioninventoryAgent",
//                        array('name' => $linkfield,
//                              'comment' => false));
//         return true;
//         break;
//
//      case 'glpi_plugin_fusioninventory_agents.nb_process_query' :
//         Dropdown::showInteger("nb_process_query", $linkfield,1,200);
//         return true;
//         break;
//
//      case 'glpi_plugin_fusioninventory_agents.nb_process_discovery' :
//         Dropdown::showInteger("nb_process_discovery", $linkfield,1,400);
//         return true;
//         break;
//
//      case 'glpi_plugin_fusioninventory_agents.logs' :
//         $ArrayValues = array();
//         $ArrayValues[]= __('No');

//         $ArrayValues[]= __('Yes');

//         $ArrayValues[]= __('Debug');

//         Dropdown::showFromArray('logs', $ArrayValues,
//                                 array('value'=>$linkfield));
//         return true;
//         break;
//
//      case 'glpi_plugin_fusioninventory_agents.core_discovery' :
//         Dropdown::showInteger("core_discovery", $linkfield,1,32);
//         return true;
//         break;
//
//      case 'glpi_plugin_fusioninventory_agents.core_query' :
//         Dropdown::showInteger("core_query", $linkfield,1,32);
//         return true;
//         break;
//
//      case 'glpi_plugin_fusioninventory_agents.threads_discovery' :
//         Dropdown::showInteger("threads_discovery", $linkfield,1,400);
//         return true;
//         break;
//
//      case 'glpi_plugin_fusioninventory_agents.threads_query' :
//         Dropdown::showInteger("threads_query", $linkfield,1,400);
//         return true;
//         break;
//
//      case 'glpi_entities.name' :
//         if (Session::isMultiEntitiesMode()) {
//            Dropdown::show("Entities",
//                           array('name' => "entities_id",
//                           'value' => $_SESSION["glpiactive_entity"]));
//         }
//         return true;
//         break;
//   }
//   return false;
//}



function plugin_fusioninventory_addSelect($type,$id,$num) {

   $searchopt = &Search::getOptions($type);
   $table = $searchopt[$id]["table"];
   $field = $searchopt[$id]["field"];

   switch ($type) {

      case 'PluginFusioninventoryAgent':

         $pfAgentmodule = new PluginFusioninventoryAgentmodule();
         $a_modules = $pfAgentmodule->find();
         foreach ($a_modules as $data) {
            if ($table.".".$field == "glpi_plugin_fusioninventory_agentmodules.".$data['modulename']) {
               return " `FUSION_".$data['modulename']."`.`is_active` AS ITEM_$num, `FUSION_".$data['modulename']."`.`exceptions`  AS ITEM_".$num."_0,";
            }
         }
         break;

   }


   return "";
}


function plugin_fusioninventory_forceGroupBy($type) {
    return false;
}


function plugin_fusioninventory_addLeftJoin($itemtype,$ref_table,$new_table,$linkfield,&$already_link_tables) {

   switch ($itemtype) {

      case 'PluginFusioninventoryAgent':
         $pfAgentmodule = new PluginFusioninventoryAgentmodule();
         $a_modules = $pfAgentmodule->find();
         foreach ($a_modules as $data) {
            if ($new_table.".".$linkfield == "glpi_plugin_fusioninventory_agentmodules.".$data['modulename']) {
               return " LEFT JOIN `glpi_plugin_fusioninventory_agentmodules` AS FUSION_".$data['modulename']."
                  ON FUSION_".$data['modulename'].".`modulename`='".$data['modulename']."' ";
            }
         }
         break;


      case 'Computer' :
         return " LEFT JOIN `$new_table` ON (`$ref_table`.`id` = `$new_table`.`computers_id`) ";
         break;

      case 'PluginFusioninventoryTaskjoblog':
//         echo $new_table.".".$linkfield."<br/>";
         $taskjob = 0;
         $already_link_tables_tmp = $already_link_tables;
         array_pop($already_link_tables_tmp);
         foreach ($already_link_tables_tmp AS $tmp_table) {
            if ($tmp_table == "glpi_plugin_fusioninventory_tasks"
                    OR $tmp_table == "glpi_plugin_fusioninventory_taskjobs"
                    OR $tmp_table == "glpi_plugin_fusioninventory_taskjobstates") {
               $taskjob = 1;
            }
         }

         switch ($new_table.".".$linkfield) {

            case 'glpi_plugin_fusioninventory_tasks.plugin_fusioninventory_tasks_id':
               $ret = '';
               if ($taskjob == '0') {
                  $ret = ' LEFT JOIN `glpi_plugin_fusioninventory_taskjobstates` ON
                     (`plugin_fusioninventory_taskjobstates_id` = `glpi_plugin_fusioninventory_taskjobstates`.`id` )
                  LEFT JOIN `glpi_plugin_fusioninventory_taskjobs` ON
                     (`plugin_fusioninventory_taskjobs_id` = `glpi_plugin_fusioninventory_taskjobs`.`id` ) ';
               }
               $ret .= ' LEFT JOIN `glpi_plugin_fusioninventory_tasks` ON
                  (`plugin_fusioninventory_tasks_id` = `glpi_plugin_fusioninventory_tasks`.`id` ) ';
               return $ret;
               break;

            case 'glpi_plugin_fusioninventory_taskjobs.plugin_fusioninventory_taskjobs_id':
            case 'glpi_plugin_fusioninventory_taskjobstates.plugin_fusioninventory_taskjobstates_id':
               if ($taskjob == '0') {
                  return ' LEFT JOIN `glpi_plugin_fusioninventory_taskjobstates` ON
                     (`plugin_fusioninventory_taskjobstates_id` = `glpi_plugin_fusioninventory_taskjobstates`.`id` )
                  LEFT JOIN `glpi_plugin_fusioninventory_taskjobs` ON
                     (`plugin_fusioninventory_taskjobs_id` = `glpi_plugin_fusioninventory_taskjobs`.`id` ) ';
               }
               return ' ';
               break;

         }
         break;

   }
   return "";
}



function plugin_fusioninventory_addOrderBy($type,$id,$order,$key=0) {
   return "";
}


function plugin_fusioninventory_addDefaultWhere($type) {
   if ($type == 'PluginFusioninventoryTaskjob') {
      return " ( select count(*) FROM `glpi_plugin_fusioninventory_taskjobstates`
         WHERE plugin_fusioninventory_taskjobs_id= `glpi_plugin_fusioninventory_taskjobs`.`id`
         AND `state`!='3' )";
   }
}


function plugin_fusioninventory_addWhere($link,$nott,$type,$id,$val) {

   $searchopt = &Search::getOptions($type);
   $table = $searchopt[$id]["table"];
   $field = $searchopt[$id]["field"];

   switch ($type) {

      case 'PluginFusioninventoryAgent':
         $pfAgentmodule = new PluginFusioninventoryAgentmodule();
         $a_modules = $pfAgentmodule->find();
         foreach ($a_modules as $data) {
            if ($table.".".$field == "glpi_plugin_fusioninventory_agentmodules.".$data['modulename']) {
               if (($data['exceptions'] != "[]") AND ($data['exceptions'] != "")) {
                  $a_exceptions = importArrayFromDB($data['exceptions']);
                  $current_id = current($a_exceptions);
                  $in = "(";
                  foreach($a_exceptions as $agent_id) {
                     $in .= $agent_id.", ";
                  }
                  $in .= ")";
                  $in = str_replace(", )", ")", $in);

                  if ($val != $data['is_active']) {
                     return $link." (FUSION_".$data['modulename'].".`exceptions` LIKE '%\"".$current_id."\"%' ) AND `glpi_plugin_fusioninventory_agents`.`id` IN ".$in." ";
                  } else {
                     return $link." `glpi_plugin_fusioninventory_agents`.`id` NOT IN ".$in." ";
                  }
               } else {
                  if ($val != $data['is_active']) {
                     return $link." (FUSION_".$data['modulename'].".`is_active`!='".$data['is_active']."') ";
                  } else {
                     return $link." (FUSION_".$data['modulename'].".`is_active`='".$data['is_active']."') ";
                  }
               }
            }
         }
         break;

      case 'PluginFusioninventoryTaskjoblog':
         if ($field == 'uniqid') {
            return $link." (`".$table."`.`uniqid`='".$val."') ";
         }
         break;

   }


   return "";
}

function plugin_pre_item_update_fusioninventory($parm) {
   if ($parm->fields['directory'] == 'fusioninventory') {
      $plugin = new Plugin();

      $a_plugins = PluginFusioninventoryModule::getAll();
      foreach($a_plugins as $datas) {
         $plugin->unactivate($datas['id']);
      }
   }
}

function plugin_pre_item_purge_fusioninventory($parm) {
   global $DB;

   switch (get_class($parm)) {

      case 'Computer':
         // Delete link between computer and agent fusion
         $query = "UPDATE `glpi_plugin_fusioninventory_agents`
                     SET `items_id` = '0'
                        AND `itemtype` = '0'
                     WHERE `items_id` = '".$parm["id"]."'
                        AND `itemtype` = '1' ";
         $DB->query($query);

         $PluginFusinvinventoryLib = new PluginFusioninventoryInventoryComputerLib();
         $PluginFusinvinventoryLib->removeExternalid($parm->getField('id'));
         // Remove antivirus if set
         PluginFusioninventoryInventoryComputerAntivirus::cleanComputer($parm->getField('id'));
         break;

   }
   return $parm;
}



function plugin_pre_item_delete_fusioninventory($parm) {
   return $parm;
}



/**
 * Hook after updates
 *
 * @param $parm
 * @return nothing
 *
**/
function plugin_item_update_fusioninventory($parm) {
   if (isset($_SESSION["glpiID"]) AND $_SESSION["glpiID"]!='') { // manual task
      $plugin = new Plugin;
      if ($plugin->isActivated('fusioninventory')) {
         // lock fields which have been updated
         $type=get_class($parm);
         $id=$parm->getField('id');
         $fieldsToLock=$parm->updates;
         if (!isset($_SESSION["plugin_fusioninventory_disablelocks"])) {
            PluginFusioninventoryLock::addLocks($type, $id, $fieldsToLock);
         }
      }
   }
}


function plugin_item_add_fusioninventory($parm) {
}


function plugin_item_purge_fusioninventory($parm) {

   switch (get_class($parm)) {

      case 'NetworkPort_NetworkPort':
         // If remove connection of a hub port (unknown device), we must delete this port too
         $NetworkPort = new NetworkPort();
         $NetworkPort_Vlan = new NetworkPort_Vlan();
         $pfUnknownDevice = new PluginFusioninventoryUnknownDevice();
         $networkPort_NetworkPort = new NetworkPort_NetworkPort();

         $a_hubs = array();

         $port_id = $NetworkPort->getContact($parm->getField('networkports_id_1'));
         $NetworkPort->getFromDB($parm->getField('networkports_id_1'));
         if ($NetworkPort->fields['itemtype'] == 'PluginFusioninventoryUnknownDevice') {
            $pfUnknownDevice->getFromDB($NetworkPort->fields['items_id']);
            if ($pfUnknownDevice->fields['hub'] == '1') {
               $a_hubs[$NetworkPort->fields['items_id']] = 1;
               $NetworkPort->delete($NetworkPort->fields);
            }
         }
         $NetworkPort->getFromDB($port_id);
         if ($port_id) {
            if ($NetworkPort->fields['itemtype'] == 'PluginFusioninventoryUnknownDevice') {
               $pfUnknownDevice->getFromDB($NetworkPort->fields['items_id']);
               if ($pfUnknownDevice->fields['hub'] == '1') {
                  $a_hubs[$NetworkPort->fields['items_id']] = 1;
               }
            }
         }
         $port_id = $NetworkPort->getContact($parm->getField('networkports_id_2'));
         $NetworkPort->getFromDB($parm->getField('networkports_id_2'));
         if ($NetworkPort->fields['itemtype'] == 'PluginFusioninventoryUnknownDevice') {
            if ($pfUnknownDevice->getFromDB($NetworkPort->fields['items_id'])) {
               if ($pfUnknownDevice->fields['hub'] == '1') {
                  $a_vlans = $NetworkPort_Vlan->getVlansForNetworkPort($NetworkPort->fields['id']);
                  foreach ($a_vlans as $vlan_id) {
                     $NetworkPort_Vlan->unassignVlan($NetworkPort->fields['id'], $vlan_id);
                  }
                  $a_hubs[$NetworkPort->fields['items_id']] = 1;
                  $NetworkPort->delete($NetworkPort->fields);
               }
            }
         }
         if ($port_id) {
            $NetworkPort->getFromDB($port_id);
            if ($NetworkPort->fields['itemtype'] == 'PluginFusioninventoryUnknownDevice') {
               $pfUnknownDevice->getFromDB($NetworkPort->fields['items_id']);
               if ($pfUnknownDevice->fields['hub'] == '1') {
                  $a_hubs[$NetworkPort->fields['items_id']] = 1;
               }
            }
         }

         // If hub have no port, delete it
         foreach ($a_hubs as $unkowndevice_id=>$num) {
            $a_networkports = $NetworkPort->find("`itemtype`='PluginFusioninventoryUnknownDevice'
               AND `items_id`='".$unkowndevice_id."' ");
            if (count($a_networkports) < 2) {
               $pfUnknownDevice->delete(array('id'=>$unkowndevice_id), 1);
            } else if (count($a_networkports) == '2') {
               $switchPorts_id = 0;
               $otherPorts_id  = 0;
               foreach ($a_networkports as $data) {
                  if ($data['name'] == 'Link') {
                     $switchPorts_id = $NetworkPort->getContact($data['id']);
                  } else if ($otherPorts_id == '0') {
                     $otherPorts_id = $NetworkPort->getContact($data['id']);
                  } else {
                     $switchPorts_id = $NetworkPort->getContact($data['id']);
                  }
               }

               $pfUnknownDevice->disconnectDB($switchPorts_id); // disconnect this port
               $pfUnknownDevice->disconnectDB($otherPorts_id);     // disconnect destination port

               $networkPort_NetworkPort->add(array('networkports_id_1'=> $switchPorts_id,
                                                   'networkports_id_2' => $otherPorts_id));
            }
         }

         break;

   }
   return $parm;
}


function plugin_item_transfer_fusioninventory($parm) {
   switch ($parm['type']) {

      case 'Computer':
         $pfAgent = new PluginFusioninventoryAgent();

         if ($agent_id = $pfAgent->getAgentWithComputerid($parm['id'])) {
            $input = array();
            $input['id'] = $agent_id;
            $input['entities_id'] = $parm->fields['entities_id'];
            $pfAgent->update($input);
         }

         break;
   }
   return false;
}



function plugin_fusioninventory_registerMethods() {
   global $WEBSERVICES_METHOD;

   $WEBSERVICES_METHOD['fusioninventory.test'] = array('PluginFusioninventoryInventoryComputerWebservice',
                                                       'methodTest');
}

?>