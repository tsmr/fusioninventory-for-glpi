<?php

/*
   ------------------------------------------------------------------------
   FusionInventory
   Copyright (C) 2010-2013 by the FusionInventory Development Team.

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
   along with FusionInventory. If not, see <http://www.gnu.org/licenses/>.

   ------------------------------------------------------------------------

   @package   FusionInventory
   @author    David Durieux
   @co-author
   @copyright Copyright (c) 2010-2013 FusionInventory team
   @license   AGPL License 3.0 or (at your option) any later version
              http://www.gnu.org/licenses/agpl-3.0-standalone.html
   @link      http://www.fusioninventory.org/
   @link      http://forge.fusioninventory.org/projects/fusioninventory-for-glpi/
   @since     2010

   ------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginFusioninventoryInventoryComputerLib extends CommonDBTM {

   var $table = "glpi_plugin_fusioninventory_inventorycomputerlibserialization";
   var $softList = array();
   var $softVersionList = array();
   var $log_add = array();
   
   
   function __construct() {
      $this->software                  = new Software();
      $this->softwareVersion           = new SoftwareVersion();
      $this->computer_SoftwareVersion  = new Computer_SoftwareVersion();
      $this->softcatrule               = new RuleSoftwareCategoryCollection();
   }

   
   
   /**
    * Update computer data
    * 
    * @global type $DB
    * 
    * @param php array $a_computerinventory all data from the agent
    * @param integer $computers_id id of the computer
    * @param boolean $no_history set tru if not want history
    * 
    * @return nothing
    */
   function updateComputer($a_computerinventory, $computers_id, $no_history, $setdynamic=0) {
      global $DB;
      Toolbox::logInFile("COMP", "1");
      $computer                     = new Computer();
      $pfInventoryComputerComputer  = new PluginFusioninventoryInventoryComputerComputer();
      $item_DeviceProcessor         = new Item_DeviceProcessor();
      $item_DeviceMemory            = new Item_DeviceMemory();
      $computerVirtualmachine       = new ComputerVirtualMachine();
      $computerDisk                 = new ComputerDisk();
      $item_DeviceControl           = new Item_DeviceControl();
      $item_DeviceHardDrive         = new Item_DeviceHardDrive();
      $item_DeviceGraphicCard       = new Item_DeviceGraphicCard();
      $item_DeviceSoundCard         = new Item_DeviceSoundCard();
      $networkPort                  = new NetworkPort();
      $networkName                  = new NetworkName();
      $iPAddress                    = new IPAddress();
      $pfInventoryComputerAntivirus = new PluginFusioninventoryInventoryComputerAntivirus();
      $pfConfig                     = new PluginFusioninventoryConfig();
//      $pfInventoryComputerStorage   = new PluginFusioninventoryInventoryComputerStorage();
//      $pfInventoryComputerStorage_Storage = 
//             new PluginFusioninventoryInventoryComputerStorage_Storage();
            
      $computer->getFromDB($computers_id);
      
      $a_lockable = PluginFusioninventoryLock::getLockFields('glpi_computers', $computers_id);
      
      // * Computer
         $db_computer = array();
         $db_computer = $computer->fields;
         $a_ret = PluginFusioninventoryToolbox::checkLock($a_computerinventory['computer'], 
                                                          $db_computer, $a_lockable);
         $a_computerinventory['computer'] = $a_ret[0];
         
         $input = $a_computerinventory['computer'];
         
         $input['id'] = $computers_id;         
         if (isset($input['comment'])) {
            unset($input['comment']);
         }
         $history = TRUE;
         if ($no_history) {
            $history = FALSE;
         }
         $computer->update($input, $history);
         
         if (isset($input['comment'])) {
            $inputcomment = array();
            $inputcomment['comment'] = $input['comment'];
            $inputcomment['id'] = $computers_id; 
            $inputcomment['_no_history'] = $no_history;
            $computer->update($inputcomment);
         }
      
      // * Computer fusion (ext)
         $db_computer = array();
         if ($no_history === FALSE) {
            $query = "SELECT * FROM `glpi_plugin_fusioninventory_inventorycomputercomputers`
                WHERE `computers_id` = '$computers_id'
                LIMIT 1";
            $result = $DB->query($query);         
            while ($data = $DB->fetch_assoc($result)) {            
               foreach($data as $key=>$value) {
                  $data[$key] = Toolbox::addslashes_deep($value);
               }
               $db_computer = $data;
            }
         }
         if (count($db_computer) == '0') { // Add
            $a_computerinventory['fusioninventorycomputer']['computers_id'] = $computers_id;
            $pfInventoryComputerComputer->add($a_computerinventory['fusioninventorycomputer'], 
                                              array(), FALSE);
         } else { // Update
            if (!empty($db_computer['serialized_inventory'])) {
               $setdynamic = 0;
            }
            $idtmp = $db_computer['id'];
            unset($db_computer['id']);
            unset($db_computer['computers_id']);
            $a_ret = PluginFusioninventoryToolbox::checkLock(
                                          $a_computerinventory['fusioninventorycomputer'], 
                                          $db_computer);
            $a_computerinventory['fusioninventorycomputer'] = $a_ret[0];
            $db_computer = $a_ret[1];
            $input = $a_computerinventory['fusioninventorycomputer'];
            $input['id'] = $idtmp;
            $input['_no_history'] = $no_history;
            $pfInventoryComputerComputer->update($input);
         }
         
      // Put all link item dynamic (in case of update computer not yet inventoried with fusion)
         if ($setdynamic == 1) {
            $this->setDynamicLinkItems($computers_id);
         }
         
      // * Processors
         if ($pfConfig->getValue("component_processor") != 0) {
            $db_processors = array();
            if ($no_history === FALSE) {
               $query = "SELECT `glpi_items_deviceprocessors`.`id`, `designation`, 
                     `frequency`, `serial`, `manufacturers_id`
                  FROM `glpi_items_deviceprocessors`
                  LEFT JOIN `glpi_deviceprocessors` 
                     ON `deviceprocessors_id`=`glpi_deviceprocessors`.`id`
                  WHERE `items_id` = '$computers_id'
                     AND `itemtype`='Computer'
                     AND `is_dynamic`='1'";
               $result = $DB->query($query);         
               while (($data = $DB->fetch_assoc($result))) {
                  $idtmp = $data['id'];
                  unset($data['id']);            
                  $data1 = Toolbox::addslashes_deep($data);
                  $data2 = array_map('strtolower', $data1);
                  $db_processors[$idtmp] = $data2;
               }
            }
            if (count($db_processors) == 0) {
               foreach ($a_computerinventory['processor'] as $a_processor) {
                  $this->addProcessor($a_processor, $computers_id, $no_history);
               }
            } else {

               // Check all fields from source: 'designation', 'serial', 'manufacturers_id', 
               // 'frequence'
               foreach ($a_computerinventory['processor'] as $key => $arrays) {
                  $arrayslower = array_map('strtolower', $arrays);
                  foreach ($db_processors as $keydb => $arraydb) {
                     if ($arrayslower == $arraydb) {
                        unset($a_computerinventory['processor'][$key]);
                        unset($db_processors[$keydb]);
                        break;
                     }
                  }
               }

               if (count($a_computerinventory['processor']) == 0
                  AND count($db_processors) == 0) {
                  // Nothing to do
               } else {
                  if (count($db_processors) != 0) {
                     // Delete processor in DB
                     foreach ($db_processors as $idtmp => $data) {
                        $item_DeviceProcessor->delete(array('id'=>$idtmp), 1);
                     }
                  }
                  if (count($a_computerinventory['processor']) != 0) {
                     foreach($a_computerinventory['processor'] as $a_processor) {
                        $this->addProcessor($a_processor, $computers_id, $no_history);
                     }
                  }
               }
            }
         }
         
      // * Memories
         if ($pfConfig->getValue("component_memory") != 0) {
            $db_memories = array();
            if ($no_history === FALSE) {
               $query = "SELECT `glpi_items_devicememories`.`id`, `designation`, `size`, 
                     `frequence`, `serial`, `devicememorytypes_id` FROM `glpi_items_devicememories`
                  LEFT JOIN `glpi_devicememories` ON `devicememories_id`=`glpi_devicememories`.`id`
                  WHERE `items_id` = '$computers_id'
                     AND `itemtype`='Computer'
                     AND `is_dynamic`='1'";
               $result = $DB->query($query);         
               while ($data = $DB->fetch_assoc($result)) {
                  $idtmp = $data['id'];
                  unset($data['id']);            
                  $data1 = Toolbox::addslashes_deep($data);
                  $db_memories[$idtmp] = $data1;
               }
            }

            if (count($db_memories) == 0) {
               foreach ($a_computerinventory['memory'] as $a_memory) {
                  $this->addMemory($a_memory, $computers_id, $no_history);
               }
            } else {
               // Check all fields from source: 'designation', 'serial', 'size', 
               // 'devicememorytypes_id', 'frequence'
               foreach ($a_computerinventory['memory'] as $key => $arrays) {
                  foreach ($db_memories as $keydb => $arraydb) {
                     if ($arrays == $arraydb) {
                        unset($a_computerinventory['memory'][$key]);
                        unset($db_memories[$keydb]);
                        break;
                     }
                  }
               }

               if (count($a_computerinventory['memory']) == 0
                  AND count($db_memories) == 0) {
                  // Nothing to do
               } else {
                  if (count($db_memories) != 0) {
                     // Delete processor in DB
                     foreach ($db_memories as $idtmp => $data) {
                        $item_DeviceMemory->delete(array('id'=>$idtmp), 1);
                     }
                  }
                  if (count($a_computerinventory['memory']) != 0) {
                     foreach($a_computerinventory['memory'] as $a_memory) {
                        $this->addMemory($a_memory, $computers_id, $no_history);
                     }
                  }
               }
            }
         }

      // * Hard drive
         if ($pfConfig->getValue("component_harddrive") != 0) {
            $db_harddrives = array();
            if ($no_history === FALSE) {
               $query = "SELECT `glpi_items_deviceharddrives`.`id`, `serial`
                     FROM `glpi_items_deviceharddrives`
                  WHERE `items_id` = '$computers_id'
                     AND `itemtype`='Computer'
                     AND `is_dynamic`='1'";
               $result = $DB->query($query);         
               while ($data = $DB->fetch_assoc($result)) {
                  $idtmp = $data['id'];
                  unset($data['id']);            
                  $data1 = Toolbox::addslashes_deep($data);
                  $data2 = array_map('strtolower', $data1);
                  $db_harddrives[$idtmp] = $data2;
               }
            }

            if (count($db_harddrives) == 0) {
               foreach ($a_computerinventory['harddrive'] as $a_harddrive) {
                  $this->addHardDisk($a_harddrive, $computers_id, $no_history);
               }
            } else {
               foreach ($a_computerinventory['harddrive'] as $key => $arrays) {
                  $arrayslower = array_map('strtolower', $arrays);
                  foreach ($db_harddrives as $keydb => $arraydb) {
                     if ($arrayslower['serial'] == $arraydb['serial']) {
                        unset($a_computerinventory['harddrive'][$key]);
                        unset($db_harddrives[$keydb]);
                        break;
                     }
                  }
               }

               if (count($a_computerinventory['harddrive']) == 0
                  AND count($db_harddrives) == 0) {
                  // Nothing to do
               } else {
                  if (count($db_harddrives) != 0) {
                     // Delete hard drive in DB
                     foreach ($db_harddrives as $idtmp => $data) {
                        $item_DeviceHardDrive->delete(array('id'=>$idtmp), 1);
                     }
                  }
                  if (count($a_computerinventory['harddrive']) != 0) {
                     foreach($a_computerinventory['harddrive'] as $a_harddrive) {
                        $this->addHardDisk($a_harddrive, $computers_id, $no_history);
                     }
                  }
               }
            }
         }

         
      // * Graphiccard
         if ($pfConfig->getValue("component_graphiccard") != 0) {
            $db_graphiccards = array();
            if ($no_history === FALSE) {
               $query = "SELECT `glpi_items_devicegraphiccards`.`id`, `designation`, `memory` 
                     FROM `glpi_items_devicegraphiccards`
                  LEFT JOIN `glpi_devicegraphiccards` 
                     ON `devicegraphiccards_id`=`glpi_devicegraphiccards`.`id`
                  WHERE `items_id` = '$computers_id'
                     AND `itemtype`='Computer'
                     AND `is_dynamic`='1'";
               $result = $DB->query($query);         
               while ($data = $DB->fetch_assoc($result)) {
                  $idtmp = $data['id'];
                  unset($data['id']);            
                  if (preg_match("/[^a-zA-Z0-9 \-_\(\)]+/", $data['designation'])) {
                     $data['designation'] = Toolbox::addslashes_deep($data['designation']);
                  }
                  $data['designation'] = trim(strtolower($data['designation']));
                  $db_graphiccards[$idtmp] = $data;
               }
            }

            if (count($db_graphiccards) == 0) {
               foreach ($a_computerinventory['graphiccard'] as $a_graphiccard) {
                  $this->addGraphicCard($a_graphiccard, $computers_id, $no_history);
               }
            } else {
               // Check all fields from source: 'designation', 'memory'
               foreach ($a_computerinventory['graphiccard'] as $key => $arrays) {
                  $arrays['designation'] = strtolower($arrays['designation']);
                  foreach ($db_graphiccards as $keydb => $arraydb) {
                     if ($arrays == $arraydb) {
                        unset($a_computerinventory['graphiccard'][$key]);
                        unset($db_graphiccards[$keydb]);
                        break;
                     }
                  }
               }

               if (count($a_computerinventory['graphiccard']) == 0
                  AND count($db_graphiccards) == 0) {
                  // Nothing to do
               } else {
                  if (count($db_graphiccards) != 0) {
                     // Delete graphiccard in DB
                     foreach ($db_graphiccards as $idtmp => $data) {
                        $item_DeviceGraphicCard->delete(array('id'=>$idtmp), 1);
                     }
                  }
                  if (count($a_computerinventory['graphiccard']) != 0) {
                     foreach($a_computerinventory['graphiccard'] as $a_graphiccard) {
                        $this->addGraphicCard($a_graphiccard, $computers_id, $no_history);
                     }
                  }
               }
            }
         }
         
         
      // * Sound         
         if ($pfConfig->getValue("component_soundcard") != 0) {
            $db_soundcards = array();
            if ($no_history === FALSE) {
               $query = "SELECT `glpi_items_devicesoundcards`.`id`, `designation`, `comment`,
                     `manufacturers_id` FROM `glpi_items_devicesoundcards`
                  LEFT JOIN `glpi_devicesoundcards` 
                     ON `devicesoundcards_id`=`glpi_devicesoundcards`.`id`
                  WHERE `items_id` = '$computers_id'
                     AND `itemtype`='Computer'
                     AND `is_dynamic`='1'";
               $result = $DB->query($query);         
               while ($data = $DB->fetch_assoc($result)) {
                  $idtmp = $data['id'];
                  unset($data['id']);            
                  $data1 = Toolbox::addslashes_deep($data);
                  $db_soundcards[$idtmp] = $data1;
               }
            }

            if (count($db_soundcards) == 0) {
               foreach ($a_computerinventory['soundcard'] as $a_soundcard) {
                  $this->addSoundCard($a_soundcard, $computers_id, $no_history);
               }
            } else {
               // Check all fields from source: 'designation', 'memory', 'manufacturers_id'
               foreach ($a_computerinventory['soundcard'] as $key => $arrays) {
   //               $arrayslower = array_map('strtolower', $arrays);
                  $arrayslower = $arrays;
                  foreach ($db_soundcards as $keydb => $arraydb) {
                     if ($arrayslower == $arraydb) {
                        unset($a_computerinventory['soundcard'][$key]);
                        unset($db_soundcards[$keydb]);
                        break;
                     }
                  }
               }

               if (count($a_computerinventory['soundcard']) == 0
                  AND count($db_soundcards) == 0) {
                  // Nothing to do
               } else {
                  if (count($db_soundcards) != 0) {
                     // Delete soundcard in DB
                     foreach ($db_soundcards as $idtmp => $data) {
                        $item_DeviceSoundCard->delete(array('id'=>$idtmp), 1);
                     }
                  }
                  if (count($a_computerinventory['soundcard']) != 0) {
                     foreach($a_computerinventory['soundcard'] as $a_soundcard) {
                        $this->addSoundCard($a_soundcard, $computers_id, $no_history);
                     }
                  }
               }
            }
         }
         
      // * Controllers
         if ($pfConfig->getValue("component_control") != 0) {
            $db_controls = array();
            if ($no_history === FALSE) {
               $query = "SELECT `glpi_items_devicecontrols`.`id`, `interfacetypes_id`,
                     `manufacturers_id`, `designation` FROM `glpi_items_devicecontrols`
                  LEFT JOIN `glpi_devicecontrols` ON `devicecontrols_id`=`glpi_devicecontrols`.`id`
                  WHERE `items_id` = '$computers_id'
                     AND `itemtype`='Computer'
                     AND `is_dynamic`='1'";
               $result = $DB->query($query);         
               while ($data = $DB->fetch_assoc($result)) {
                  $idtmp = $data['id'];
                  unset($data['id']);            
                  $data1 = Toolbox::addslashes_deep($data);
                  $data2 = array_map('strtolower', $data1);
                  $db_controls[$idtmp] = $data2;
               }
            }

            if (count($db_controls) == 0) {
               foreach ($a_computerinventory['controller'] as $a_control) {
                  $this->addControl($a_control, $computers_id, $no_history);
               }
            } else {
               // Check all fields from source: 
               foreach ($a_computerinventory['controller'] as $key => $arrays) {
                  $arrayslower = array_map('strtolower', $arrays);
                  foreach ($db_controls as $keydb => $arraydb) {
                     if ($arrayslower == $arraydb) {
                        unset($a_computerinventory['controller'][$key]);
                        unset($db_controls[$keydb]);
                        break;
                     }
                  }
               }

               if (count($a_computerinventory['controller']) == 0
                  AND count($db_controls) == 0) {
                  // Nothing to do
               } else {
                  if (count($db_controls) != 0) {
                     // Delete controller in DB
                     foreach ($db_controls as $idtmp => $data) {
                        $item_DeviceControl->delete(array('id'=>$idtmp), 1);
                     }
                  }
                  if (count($a_computerinventory['controller']) != 0) {
                     foreach($a_computerinventory['controller'] as $a_control) {
                        $this->addControl($a_control, $computers_id, $no_history);
                     }
                  }
               }
            }
         }
         
      // * Software
         if ($pfConfig->getValue("import_software") != 0) {
            
            $entities_id = 0;
            if (count($a_computerinventory['software']) > 0) {
               $a_softfirst = current($a_computerinventory['software']);
               if (isset($a_softfirst['entities_id'])) {
                  $entities_id = $a_softfirst['entities_id'];
         }
            }
            $db_software = array();
            if ($no_history === FALSE) {
               $query = "SELECT `glpi_computers_softwareversions`.`id` as sid,
                          `glpi_softwares`.`name`,
                          `glpi_softwareversions`.`name` AS version,
                          `glpi_softwares`.`manufacturers_id`,
                          `glpi_softwareversions`.`entities_id`,
                          `glpi_computers_softwareversions`.`is_template_computer`,
                          `glpi_computers_softwareversions`.`is_deleted_computer`
                   FROM `glpi_computers_softwareversions`
                   LEFT JOIN `glpi_softwareversions`
                        ON (`glpi_computers_softwareversions`.`softwareversions_id`
                              = `glpi_softwareversions`.`id`)
                   LEFT JOIN `glpi_softwares`
                        ON (`glpi_softwareversions`.`softwares_id` = `glpi_softwares`.`id`)
                   WHERE `glpi_computers_softwareversions`.`computers_id` = '$computers_id'
                     AND `glpi_computers_softwareversions`.`is_dynamic`='1'";
               $result = $DB->query($query);
               while ($data = $DB->fetch_assoc($result)) {
                  $idtmp = $data['sid'];
                  unset($data['sid']);
                  if (preg_match("/[^a-zA-Z0-9 \-_\(\)]+/", $data['name'])) {
                     $data['name'] = Toolbox::addslashes_deep($data['name']);
                  }
                  if (preg_match("/[^a-zA-Z0-9 \-_\(\)]+/", $data['version'])) {
                     $data['version'] = Toolbox::addslashes_deep($data['version']);
                  }
                  $comp_key = $data['name'].
                               "$$$$".$data['version'].
                               "$$$$".$data['manufacturers_id'].
                               "$$$$".$data['entities_id'];
                  $db_software[$comp_key] = $idtmp;
               }
            }
         
            $lastSoftwareid = 0;
            $lastSoftwareVid = 0;
            
            if (count($db_software) == 0) {
               $nb_unicity = count(FieldUnicity::getUnicityFieldsConfig("Software", $entities_id));
               $options = array();
               if ($nb_unicity == 0) {
                  $options['disable_unicity_check'] = TRUE;
               }
               $a_softwareInventory = array();
               $a_softwareVersionInventory = array();

               foreach ($a_computerinventory['software'] as $keysoft=>$a_software) {
                  $a_softwareInventory[$a_software['name']] = $a_software['name'];
                  $a_softwareVersionInventory[$a_software['version']] = $a_software['version'];
               }

               if (count($a_computerinventory['software']) > 50) {
                  $lastSoftwareid = $this->loadSoftwares($entities_id, 
                                                         $a_softwareInventory, 
                                                         $lastSoftwareid);
                  $ret = $DB->query("SELECT GET_LOCK('softwareversion', 3000)");
                  if ($DB->result($ret, 0, 0) == 1) {
                     $lastSoftwareVid = $this->loadSoftwareVersions($entities_id, 
                                                                    $a_softwareVersionInventory, 
                                                                    $lastSoftwareVid);
                     foreach ($a_computerinventory['software'] as $keysoft=>$a_software) {
                        if (isset($this->softList[$a_software['name']."$$$$".
                                 $a_software['manufacturers_id']])) {
                           $a_software['softwares_id'] = $this->softList[$a_software['name']."$$$$".
                                 $a_software['manufacturers_id']];
                           $a_software['_no_message'] = TRUE;
                           $this->addSoftware($a_software,
                                              $computers_id,
                                              $no_history,
                                              $options);
                           unset($a_computerinventory['software'][$keysoft]);
                        }
                     }
                     $ret = $DB->query("SELECT GET_LOCK('softwareversion', 3000)");
                  }
               }
               $ret = $DB->query("SELECT GET_LOCK('software', 3000)");
               if ($DB->result($ret, 0, 0) == 1) {
                  if (count($a_computerinventory['software']) > 50) {
                     $this->loadSoftwares($entities_id, $a_softwareInventory, $lastSoftwareid);
                  }
                  $this->loadSoftwareVersions($entities_id, 
                                              $a_softwareVersionInventory, 
                                              $lastSoftwareVid);
                  foreach ($a_computerinventory['software'] as $a_software) {
                     $a_software['_no_message'] = TRUE;
                     if (count($a_computerinventory['software']) > 50) {
                        if (isset($this->softList[$a_software['name']."$$$$".
                                 $a_software['manufacturers_id']])) {
                           $a_software['softwares_id'] = $this->softList[$a_software['name']."$$$$".
                                 $a_software['manufacturers_id']];
                        }
                     } else {
                        $a_software['softwares_id'] = -1;
                     }
                     $this->addSoftware($a_software,
                                        $computers_id,
                                        $no_history,
                                        $options);
                  }
                  $DB->request("SELECT RELEASE_LOCK('software')");
               }
            } else {
//               foreach ($a_computerinventory['software'] as $key => $arrayslower) {
//                  foreach ($db_software as $keydb => $arraydb) {
//                     if ($arrayslower == $arraydb) {
//                        unset($a_computerinventory['software'][$key]);
//                        unset($db_software[$keydb]);
//                        break;
//                     }
//                  }
//               }
               foreach ($a_computerinventory['software'] as $key => $arrayslower) {
                  if (isset($db_software[$key])) {
                     unset($a_computerinventory['software'][$key]);
                     unset($db_software[$key]);
                  }
               }

               if (count($a_computerinventory['software']) == 0
                  && count($db_software) == 0) {
                  // Nothing to do
               } else {
                  if (count($db_software) != 0) {
                     // Delete softwares in DB
                     foreach ($db_software as $idtmp) {
                        $this->computer_SoftwareVersion->delete(array('id'=>$idtmp), 1);
                     }
                  }
                  if (count($a_computerinventory['software']) != 0) {
                     $nb_unicity = count(FieldUnicity::getUnicityFieldsConfig("Software", 
                                                                              $entities_id));
                     $options = array();
                     if ($nb_unicity == 0) {
                        $options['disable_unicity_check'] = TRUE;
                     }
                     $ret = $DB->query("SELECT GET_LOCK('software', 3000)");
                     if ($DB->result($ret, 0, 0) == 1) {
                        foreach ($a_computerinventory['software'] as $keysoft=>$a_software) {
                           $a_softwareInventory[$a_software['name']] = $a_software['name'];
                           $a_softwareVersionInventory[$a_software['version']] = 
                                          $a_software['version'];
                        }
                        if (count($a_computerinventory['software']) > 50) {
                           $this->loadSoftwares($entities_id, 
                                                $a_softwareInventory, 
                                                $lastSoftwareid);
                        }
                        $this->loadSoftwareVersions($entities_id, 
                                                    $a_softwareVersionInventory, 
                                                    $lastSoftwareVid);
                        foreach($a_computerinventory['software'] as $a_software) {
                           $a_software['_no_message'] = TRUE;
                           if (count($a_computerinventory['software']) > 50) {
                              if (isset($this->softList[$a_software['name']."$$$$".
                                       $a_software['manufacturers_id']])) {
                                 $a_software['softwares_id'] = $this->softList[$a_software['name'].
                                       "$$$$".$a_software['manufacturers_id']];
                              }
                           } else {
                              $a_software['softwares_id'] = -1;
                           }
                           $this->addSoftware($a_software,
                                              $computers_id,
                                              $no_history,
                                              $options);
                        }
                        $DB->request("SELECT RELEASE_LOCK('software')");
                     }
                  }
               }
            }
         }
         
      // * Virtualmachines
         if ($pfConfig->getValue("import_vm") != 0) {
            $db_computervirtualmachine = array();
            if ($no_history === FALSE) {
               $query = "SELECT `id`, `name`, `uuid`, `virtualmachinesystems_id` 
                     FROM `glpi_computervirtualmachines`
                  WHERE `computers_id` = '$computers_id'
                     AND `is_dynamic`='1'";
               $result = $DB->query($query);         
               while ($data = $DB->fetch_assoc($result)) {
                  $idtmp = $data['id'];
                  unset($data['id']);            
                  $data1 = Toolbox::addslashes_deep($data);
                  $data2 = array_map('strtolower', $data1);
                  $db_computervirtualmachine[$idtmp] = $data2;
               }
            }

            $simplecomputervirtualmachine = array();
            if (isset($a_computerinventory['virtualmachine'])) {
               foreach ($a_computerinventory['virtualmachine'] as $key=>$a_computervirtualmachine) {
                  $a_field = array('name', 'uuid', 'virtualmachinesystems_id');
                  foreach ($a_field as $field) {
                     if (isset($a_computervirtualmachine[$field])) {
                        $simplecomputervirtualmachine[$key][$field] = 
                                    $a_computervirtualmachine[$field];
                     }
                  }            
               }
            }
            foreach ($simplecomputervirtualmachine as $key => $arrays) {
               $arrayslower = array_map('strtolower', $arrays);
               foreach ($db_computervirtualmachine as $keydb => $arraydb) {
                  if ($arrayslower == $arraydb) {
                     $input = array();
                     $input['id'] = $keydb;
                     if (isset($a_computerinventory['virtualmachine'][$key]['vcpu'])) {
                        $input['vcpu'] = $a_computerinventory['virtualmachine'][$key]['vcpu'];
                     }
                     if (isset($a_computerinventory['virtualmachine'][$key]['ram'])) {
                        $input['ram'] = $a_computerinventory['virtualmachine'][$key]['ram'];
                     }
                     if (isset($a_computerinventory['virtualmachine'][$key]['virtualmachinetypes_id'])) {
                        $input['virtualmachinetypes_id'] = 
                             $a_computerinventory['virtualmachine'][$key]['virtualmachinetypes_id'];
                     }
                     if (isset($a_computerinventory['virtualmachine'][$key]['virtualmachinestates_id'])) {
                        $input['virtualmachinestates_id'] = 
                            $a_computerinventory['virtualmachine'][$key]['virtualmachinestates_id'];
                     }
                     $computerVirtualmachine->update($input);
                     unset($simplecomputervirtualmachine[$key]);
                     unset($a_computerinventory['virtualmachine'][$key]);
                     unset($db_computervirtualmachine[$keydb]);
                     break;
                  }
               }
            }
            if (count($a_computerinventory['virtualmachine']) == 0
               AND count($db_computervirtualmachine) == 0) {
               // Nothing to do
            } else {
               if (count($db_computervirtualmachine) != 0) {
                  // Delete virtualmachine in DB
                  foreach ($db_computervirtualmachine as $idtmp => $data) {
                     $computerVirtualmachine->delete(array('id'=>$idtmp), 1);
                  }
               }
               if (count($a_computerinventory['virtualmachine']) != 0) {
                  foreach($a_computerinventory['virtualmachine'] as $a_virtualmachine) {
                     $a_virtualmachine['computers_id'] = $computers_id;
                     $computerVirtualmachine->add($a_virtualmachine, array(), FALSE);
                  }
               }
            }
         }
         
      // * ComputerDisk
         if ($pfConfig->getValue("import_volume") != 0) {
            $db_computerdisk = array();
            if ($no_history === FALSE) {
               $query = "SELECT `id`, `name`, `device`, `mountpoint` 
                   FROM `glpi_computerdisks`
                   WHERE `computers_id` = '".$computers_id."'
                     AND `is_dynamic`='1'";
               $result = $DB->query($query);         
               while ($data = $DB->fetch_assoc($result)) {
                  $idtmp = $data['id'];
                  unset($data['id']);            
                  $data1 = Toolbox::addslashes_deep($data);
                  $data2 = array_map('strtolower', $data1);
                  $db_computerdisk[$idtmp] = $data2;
               }
            }
            $simplecomputerdisk = array();
            foreach ($a_computerinventory['computerdisk'] as $key=>$a_computerdisk) {
               $a_field = array('name', 'device', 'mountpoint');
               foreach ($a_field as $field) {
                  if (isset($a_computerdisk[$field])) {
                     $simplecomputerdisk[$key][$field] = $a_computerdisk[$field];
                  }
               }            
            }
            foreach ($simplecomputerdisk as $key => $arrays) {
               $arrayslower = array_map('strtolower', $arrays);
               foreach ($db_computerdisk as $keydb => $arraydb) {
                  if ($arrayslower == $arraydb) {
                     $input = array();
                     $input['id'] = $keydb;
                     if (isset($a_computerinventory['computerdisk'][$key]['filesystems_id'])) {
                        $input['filesystems_id'] = 
                                 $a_computerinventory['computerdisk'][$key]['filesystems_id'];
                     }
                     $input['totalsize'] = $a_computerinventory['computerdisk'][$key]['totalsize'];                  
                     $input['freesize'] = $a_computerinventory['computerdisk'][$key]['freesize'];
                     $computerDisk->update($input);
                     unset($simplecomputerdisk[$key]);
                     unset($a_computerinventory['computerdisk'][$key]);
                     unset($db_computerdisk[$keydb]);
                     break;
                  }
               }
            }

            if (count($a_computerinventory['computerdisk']) == 0
               AND count($db_computerdisk) == 0) {
               // Nothing to do
            } else {
               if (count($db_computerdisk) != 0) {
                  // Delete computerdisk in DB
                  foreach ($db_computerdisk as $idtmp => $data) {
                     $computerDisk->delete(array('id'=>$idtmp), 1);
                  }
               }
               if (count($a_computerinventory['computerdisk']) != 0) {
                  foreach($a_computerinventory['computerdisk'] as $a_computerdisk) {
                     $a_computerdisk['computers_id']  = $computers_id;
                     $a_computerdisk['is_dynamic']    = 1;
                     $a_computerdisk['_no_history']   = $no_history;
                     $computerDisk->add($a_computerdisk, array(), FALSE);
                  }
               }
            }
         }
         
     
      // * Networkports
         if ($pfConfig->getValue("component_networkcard") != 0) {
            $db_networkport = array();
            if ($no_history === FALSE) {
               $query = "SELECT `id`, `name`, `mac`, `instantiation_type` 
                   FROM `glpi_networkports`
                   WHERE `items_id` = '$computers_id'
                     AND `itemtype`='Computer'
                     AND `is_dynamic`='1'";
               $result = $DB->query($query);         
               while (($data = $DB->fetch_assoc($result))) {
                  $idtmp = $data['id'];
                  unset($data['id']);
                  if (is_null($data['mac'])) {
                     $data['mac'] = '';
                  }
                  if (preg_match("/[^a-zA-Z0-9 \-_\(\)]+/", $data['name'])) {
                     $data['name'] = Toolbox::addslashes_deep($data['name']);
                  }
                  $db_networkport[$idtmp] = array_map('strtolower', $data);
               }
            }
            $simplenetworkport = array();
            foreach ($a_computerinventory['networkport'] as $key=>$a_networkport) {
               $a_field = array('name', 'mac', 'instantiation_type');
               foreach ($a_field as $field) {
                  if (isset($a_networkport[$field])) {
                     $simplenetworkport[$key][$field] = $a_networkport[$field];
                  }
               }            
            }
            foreach ($simplenetworkport as $key => $arrays) {
               $arrayslower = array_map('strtolower', $arrays);
               foreach ($db_networkport as $keydb => $arraydb) {
                  if ($arrayslower == $arraydb) {
                     // Get networkname
                     $a_networknames_find = current($networkName->find("`items_id`='".$keydb."'
                                                          AND `itemtype`='NetworkPort'", "", 1));
                     if (!isset($a_networknames_find['id'])) {
                        $a_networkport['entities_id'] = $_SESSION["plugin_fusinvinventory_entity"];
                        $a_networkport['items_id'] = $computers_id;
                        $a_networkport['itemtype'] = "Computer";
                        $a_networkport['is_dynamic'] = 1;
                        $a_networkport['_no_history'] = $no_history;
                        $a_networkport['items_id'] = $keydb;
                        unset($a_networkport['_no_history']);
                        $a_networkport['is_recursive'] = 0;
                        $a_networkport['itemtype'] = 'NetworkPort';
                        unset($a_networkport['name']);
                        $a_networkport['_no_history'] = $no_history;
                        $a_networknames_id = $networkName->add($a_networkport, array(), FALSE);
                        $a_networknames_find['id'] = $a_networknames_id;
                     }                     
                     
                     // Same networkport, verify ipaddresses
                     $db_addresses = array();
                     $query = "SELECT `id`, `name` FROM `glpi_ipaddresses`
                         WHERE `items_id` = '".$a_networknames_find['id']."'
                           AND `itemtype`='NetworkName'";
                     $result = $DB->query($query);         
                     while ($data = $DB->fetch_assoc($result)) {
                        $db_addresses[$data['id']] = $data['name'];
                     }
                     $a_computerinventory_ipaddress = 
                                 $a_computerinventory['networkport'][$key]['ipaddress'];
                     foreach ($a_computerinventory_ipaddress as $key2 => $arrays2) {
                        foreach ($db_addresses as $keydb2 => $arraydb2) {
                           if ($arrays2 == $arraydb2) {
                              unset($a_computerinventory_ipaddress[$key2]);
                              unset($db_addresses[$keydb2]);
                              break;
                           }
                        }
                     }
                     if (count($a_computerinventory_ipaddress) == 0
                        AND count($db_addresses) == 0) {
                        // Nothing to do
                     } else {
                        if (count($db_addresses) != 0) {
                           // Delete ip address in DB                     
                           foreach (array_keys($db_addresses) as $idtmp) {
                              $iPAddress->delete(array('id'=>$idtmp), 1);
                           }
                        }
                        if (count($a_computerinventory_ipaddress) != 0) {
                           foreach ($a_computerinventory_ipaddress as $ip) {
                              $input = array();
                              $input['items_id']   = $a_networknames_find['id'];
                              $input['itemtype']   = 'NetworkName';
                              $input['name']       = $ip;
                              $input['is_dynamic'] = 1;
                              $iPAddress->add($input, array(), FALSE);
                           }
                        }
                     }

                     unset($db_networkport[$keydb]);
                     unset($simplenetworkport[$key]);
                     unset($a_computerinventory['networkport'][$key]);
                     break;
                  }
               }
            }

            if (count($a_computerinventory['networkport']) == 0
               AND count($db_networkport) == 0) {
               // Nothing to do
            } else {
               if (count($db_networkport) != 0) {
                  // Delete networkport in DB
                  foreach ($db_networkport as $idtmp => $data) {
                     $networkPort->delete(array('id'=>$idtmp), 1);
                  }
               }
               if (count($a_computerinventory['networkport']) != 0) {
                  foreach ($a_computerinventory['networkport'] as $a_networkport) {
                     $a_networkport['entities_id'] = $_SESSION["plugin_fusinvinventory_entity"];
                     $a_networkport['items_id'] = $computers_id;
                     $a_networkport['itemtype'] = "Computer";
                     $a_networkport['is_dynamic'] = 1;
                     $a_networkport['_no_history'] = $no_history;
                     $a_networkport['items_id'] = $networkPort->add($a_networkport, array(), FALSE);
                     unset($a_networkport['_no_history']);
                     $a_networkport['is_recursive'] = 0;
                     $a_networkport['itemtype'] = 'NetworkPort';
                     unset($a_networkport['name']);
                     $a_networkport['_no_history'] = $no_history;
                     $a_networknames_id = $networkName->add($a_networkport, array(), FALSE);
                     foreach ($a_networkport['ipaddress'] as $ip) {
                        $input = array();
                        $input['items_id']   = $a_networknames_id;
                        $input['itemtype']   = 'NetworkName';
                        $input['name']       = $ip;
                        $input['is_dynamic'] = 1;
                        $input['_no_history'] = $no_history;
                        $iPAddress->add($input, array(), FALSE);
                     }
                  }
               }
            }
         }
         
         
      // * Antivirus
         $db_antivirus = array();
         if ($no_history === FALSE) {
            $query = "SELECT `id`, `name`, `version` 
                  FROM `glpi_plugin_fusioninventory_inventorycomputerantiviruses`
               WHERE `computers_id` = '$computers_id'";
            $result = $DB->query($query);         
            while ($data = $DB->fetch_assoc($result)) {
               $idtmp = $data['id'];
               unset($data['id']);            
               $data1 = Toolbox::addslashes_deep($data);
               $data2 = array_map('strtolower', $data1);
               $db_antivirus[$idtmp] = $data2;
            }
         }
         $simpleantivirus = array();
         foreach ($a_computerinventory['antivirus'] as $key=>$a_antivirus) {
            $a_field = array('name', 'version');
            foreach ($a_field as $field) {
               if (isset($a_antivirus[$field])) {
                  $simpleantivirus[$key][$field] = $a_antivirus[$field];
               }
            }            
         }
         foreach ($simpleantivirus as $key => $arrays) {
            $arrayslower = array_map('strtolower', $arrays);
            foreach ($db_antivirus as $keydb => $arraydb) {
               if ($arrayslower == $arraydb) {
                  $input = array();
                  $input = $a_computerinventory['antivirus'][$key];
                  $input['id'] = $keydb;
                  $pfInventoryComputerAntivirus->update($input);
                  unset($simpleantivirus[$key]);
                  unset($a_computerinventory['antivirus'][$key]);
                  unset($db_antivirus[$keydb]);
                  break;
               }
            }
         }
         if (count($a_computerinventory['antivirus']) == 0
            AND count($db_antivirus) == 0) {
            // Nothing to do
         } else {
            if (count($db_antivirus) != 0) {
               foreach ($db_antivirus as $idtmp => $data) {
                  $pfInventoryComputerAntivirus->delete(array('id'=>$idtmp), 1);
               }
            }
            if (count($a_computerinventory['antivirus']) != 0) {
               foreach($a_computerinventory['antivirus'] as $a_antivirus) {
                  $a_antivirus['computers_id'] = $computers_id;
                  $pfInventoryComputerAntivirus->add($a_antivirus, array(), FALSE);
               }
            }
         }


      // * Batteries
         /* Standby, see ticket http://forge.fusioninventory.org/issues/1907
         $db_batteries = array();
         if ($no_history === FALSE) {
            $query = "SELECT `id`, `name`, `serial` 
                  FROM `glpi_plugin_fusioninventory_inventorycomputerbatteries`
               WHERE `computers_id` = '$computers_id'";
            $result = $DB->query($query);         
            while ($data = $DB->fetch_assoc($result)) {
               $idtmp = $data['id'];
               unset($data['id']);            
               $data = Toolbox::addslashes_deep($data);
               $data = array_map('strtolower', $data);
               $db_batteries[$idtmp] = $data;
            }
         }
         $simplebatteries = array();
         foreach ($a_computerinventory['batteries'] as $key=>$a_batteries) {
            $a_field = array('name', 'serial');
            foreach ($a_field as $field) {
               if (isset($a_batteries[$field])) {
                  $simplebatteries[$key][$field] = $a_batteries[$field];
               }
            }            
         }
         foreach ($simplebatteries as $key => $arrays) {
            $arrayslower = array_map('strtolower', $arrays);
            foreach ($db_batteries as $keydb => $arraydb) {
               if ($arrayslower == $arraydb) {
                  $input = array();
                  $input = $a_computerinventory['batteries'][$key];
                  $input['id'] = $keydb;
                  $pfInventoryComputerBatteries->update($input);
                  unset($simplebatteries[$key]);
                  unset($a_computerinventory['batteries'][$key]);
                  unset($db_batteries[$keydb]);
                  break;
               }
            }
         }
         if (count($a_computerinventory['batteries']) == 0
            AND count($db_batteries) == 0) {
            // Nothing to do
         } else {
            if (count($db_batteries) != 0) {
               foreach ($db_batteries as $idtmp => $data) {
                  $pfInventoryComputerBatteries->delete(array('id'=>$idtmp), 1);
               }
            }
            if (count($a_computerinventory['batteries']) != 0) {
               foreach($a_computerinventory['batteries'] as $a_batteries) {
                  $a_batteries['computers_id'] = $computers_id;
                  $pfInventoryComputerBatteries->add($a_batteries, array(), FALSE);
               }
            }
         }
*/
         
         
      $entities_id = $_SESSION["plugin_fusinvinventory_entity"];
      // * Monitors
         if ($pfConfig->getValue("import_monitor") != 0) {
            $db_monitors = array();
            $computer_Item = new Computer_Item();
            if ($no_history === FALSE) {
               if ($pfConfig->getValue('import_monitor') == 1) {
                  // Global import
                  $query = "SELECT `glpi_monitors`.`name`, `glpi_monitors`.`manufacturers_id`,
                        `glpi_monitors`.`serial`, `glpi_monitors`.`comment`, 
                        `glpi_monitors`.`is_global`, `glpi_computers_items`.`id` as link_id 
                        FROM `glpi_computers_items`
                     LEFT JOIN `glpi_monitors` ON `items_id`=`glpi_monitors`.`id`
                     WHERE `itemtype`='Monitor'
                        AND `computers_id`='".$computers_id."'
                        AND `entities_id`='".$entities_id."'
                        AND `glpi_computers_items`.`is_dynamic`='1'";
                  $result = $DB->query($query);
                  while ($data = $DB->fetch_assoc($result)) {
                     if ($data['is_global'] == 0) {
                        $computer_Item->delete(array('id' => $data['link_id']), 1);
                     } else {
                        $idtmp = $data['link_id'];
                        unset($data['link_id']);
                        unset($data['is_global']);
                        $data1 = Toolbox::addslashes_deep($data);
                        $data2 = array_map('strtolower', $data1);
                        $db_monitors[$idtmp] = $data2;
                     }
                  }                  
               } else if ($pfConfig->getValue('import_monitor') == 2) {
                  // Unique import
                  $query = "SELECT `glpi_monitors`.`name`, `glpi_monitors`.`manufacturers_id`,
                        `glpi_monitors`.`serial`, `glpi_monitors`.`comment`, 
                        `glpi_monitors`.`is_global`, `glpi_computers_items`.`id` as link_id 
                        FROM `glpi_computers_items`
                     LEFT JOIN `glpi_monitors` ON `items_id`=`glpi_monitors`.`id`
                     WHERE `itemtype`='Monitor'
                        AND `computers_id`='".$computers_id."'
                        AND `entities_id`='".$entities_id."'
                        AND `glpi_computers_items`.`is_dynamic`='1'";
                  $result = $DB->query($query);
                  while ($data = $DB->fetch_assoc($result)) {
                     if ($data['is_global'] == 1) {
                        $computer_Item->delete(array('id' => $data['link_id']), 1);
                     } else {
                        $idtmp = $data['link_id'];
                        unset($data['link_id']);
                        unset($data['is_global']);
                        $data1 = Toolbox::addslashes_deep($data);
                        $data2 = array_map('strtolower', $data1);
                        $db_monitors[$idtmp] = $data2;
                     }
                  }                  
               } else if ($pfConfig->getValue('import_monitor') == 3) {
                  // Unique import on serial number
                  $query = "SELECT `glpi_monitors`.`name`, `glpi_monitors`.`manufacturers_id`,
                        `glpi_monitors`.`serial`, `glpi_monitors`.`comment`, 
                        `glpi_monitors`.`is_global`, `glpi_computers_items`.`id` as link_id
                        FROM `glpi_computers_items`
                     LEFT JOIN `glpi_monitors` ON `items_id`=`glpi_monitors`.`id`
                     WHERE `itemtype`='Monitor'
                        AND `computers_id`='".$computers_id."'
                        AND `entities_id`='".$entities_id."'
                        AND `glpi_computers_items`.`is_dynamic`='1'";
                  $result = $DB->query($query);
                  while ($data = $DB->fetch_assoc($result)) {
                     if ($data['serial'] == ''
                             || $data['is_global'] == 1) {
                        $computer_Item->delete(array('id' => $data['link_id']), 1);
                     } else {
                        $idtmp = $data['link_id'];
                        unset($data['link_id']);
                        unset($data['is_global']);
                        $data1 = Toolbox::addslashes_deep($data);
                        $data2 = array_map('strtolower', $data1);
                        $db_monitors[$idtmp] = $data2;
                     }
                  }                  
               }
            }
            
            if (count($db_monitors) == 0) {
               foreach ($a_computerinventory['monitor'] as $a_monitor) {
                  $a_monitor['entities_id'] = $entities_id;
                  $this->addMonitor($a_monitor, $computers_id, $no_history);
               }
            } else {
               // Check all fields from source: 
               foreach ($a_computerinventory['monitor'] as $key => $arrays) {
                  $arrayslower = array_map('strtolower', $arrays);
                  foreach ($db_monitors as $keydb => $arraydb) {
                     if ($arrayslower == $arraydb) {
                        unset($a_computerinventory['monitor'][$key]);
                        unset($db_monitors[$keydb]);
                        break;
                     }
                  }
               }

               if (count($a_computerinventory['monitor']) == 0
                  AND count($db_monitors) == 0) {
                  // Nothing to do
               } else {
                  if (count($db_monitors) != 0) {
                     // Delete monitors links in DB
                     foreach ($db_monitors as $idtmp => $data) {
                        $computer_Item->delete(array('id'=>$idtmp), 1);
                     }
                  }
                  if (count($a_computerinventory['monitor']) != 0) {
                     foreach($a_computerinventory['monitor'] as $a_monitor) {
                        $a_monitor['entities_id'] = $entities_id;
                        $this->addMonitor($a_monitor, $computers_id, $no_history);
                     }
                  }
               }
            }
         }


      // * Printers
         if ($pfConfig->getValue("import_printer") != 0) {
            $db_printers = array();
            $computer_Item = new Computer_Item();
            if ($no_history === FALSE) {
               if ($pfConfig->getValue('import_printer') == 1) {
                  // Global import
                  $query = "SELECT `glpi_printers`.`name`, `glpi_printers`.`serial`,
                        `glpi_printers`.`is_global`, `glpi_computers_items`.`id` as link_id 
                        FROM `glpi_computers_items`
                     LEFT JOIN `glpi_printers` ON `items_id`=`glpi_printers`.`id`
                     WHERE `itemtype`='Printer'
                        AND `computers_id`='".$computers_id."'
                        AND `entities_id`='".$entities_id."'
                        AND `glpi_computers_items`.`is_dynamic`='1'";
                  $result = $DB->query($query);
                  while ($data = $DB->fetch_assoc($result)) {
                     if ($data['is_global'] == 0) {
                        $computer_Item->delete(array('id' => $data['link_id']), 1);
                     } else {
                        $idtmp = $data['link_id'];
                        unset($data['link_id']);
                        unset($data['is_global']);
                        $data1 = Toolbox::addslashes_deep($data);
                        $data2 = array_map('strtolower', $data1);
                        $db_printers[$idtmp] = $data2;
                     }
                  }                  
               } else if ($pfConfig->getValue('import_printer') == 2) {
                  // Unique import
                  $query = "SELECT `glpi_printers`.`name`, `glpi_printers`.`serial`,
                        `glpi_printers`.`is_global`, `glpi_computers_items`.`id` as link_id  
                        FROM `glpi_computers_items`
                     LEFT JOIN `glpi_printers` ON `items_id`=`glpi_printers`.`id`
                     WHERE `itemtype`='Printer'
                        AND `computers_id`='".$computers_id."'
                        AND `entities_id`='".$entities_id."'
                        AND `glpi_computers_items`.`is_dynamic`='1'";
                  $result = $DB->query($query);
                  while ($data = $DB->fetch_assoc($result)) {
                     if ($data['is_global'] == 1) {
                        $computer_Item->delete(array('id' => $data['link_id']), 1);
                     } else {
                        $idtmp = $data['link_id'];
                        unset($data['link_id']);
                        unset($data['is_global']);
                        $data1 = Toolbox::addslashes_deep($data);
                        $data2 = array_map('strtolower', $data1);
                        $db_printers[$idtmp] = $data2;
                     }
                  }                  
               } else if ($pfConfig->getValue('import_printer') == 3) {
                  // Unique import on serial number
                  $query = "SELECT `glpi_printers`.`name`, `glpi_printers`.`serial`,
                        `glpi_printers`.`is_global`, `glpi_computers_items`.`id` as link_id  
                        FROM `glpi_computers_items`
                     LEFT JOIN `glpi_printers` ON `items_id`=`glpi_printers`.`id`
                     WHERE `itemtype`='Printer'
                        AND `computers_id`='".$computers_id."'
                        AND `entities_id`='".$entities_id."'
                        AND `glpi_computers_items`.`is_dynamic`='1'";
                  $result = $DB->query($query);
                  while ($data = $DB->fetch_assoc($result)) {
                     if ($data['serial'] == ''
                             || $data['is_global'] == 1) {
                        $computer_Item->delete(array('id' => $data['link_id']), 1);
                     } else {
                        $idtmp = $data['link_id'];
                        unset($data['link_id']);
                        unset($data['is_global']);
                        $data = Toolbox::addslashes_deep($data);
                        $data = array_map('strtolower', $data);
                        $db_printers[$idtmp] = $data;
                     }
                  }                  
               }
            }
            
            if (count($db_printers) == 0) {
               foreach ($a_computerinventory['printer'] as $a_printer) {
                  $a_printer['entities_id'] = $entities_id;
                  $this->addPrinter($a_printer, $computers_id, $no_history);
               }
            } else {
               // Check all fields from source: 
               foreach ($a_computerinventory['printer'] as $key => $arrays) {
                  unset($arrays['have_usb']);
                  $arrayslower = array_map('strtolower', $arrays);
                  foreach ($db_printers as $keydb => $arraydb) {
                     if ($arrayslower == $arraydb) {
                        unset($a_computerinventory['printer'][$key]);
                        unset($db_printers[$keydb]);
                        break;
                     }
                  }
               }

               if (count($a_computerinventory['printer']) == 0
                  AND count($db_printers) == 0) {
                  // Nothing to do
               } else {
                  if (count($db_printers) != 0) {
                     // Delete printers links in DB
                     foreach ($db_printers as $idtmp => $data) {
                        $computer_Item->delete(array('id'=>$idtmp), 1);
                     }
                  }
                  if (count($a_computerinventory['printer']) != 0) {
                     foreach($a_computerinventory['printer'] as $a_printer) {
                        $a_printer['entities_id'] = $entities_id;
                        $this->addPrinter($a_printer, $computers_id, $no_history);
                     }
                  }
               }
            }
         }
         
      // * Peripheral
         if ($pfConfig->getValue("import_peripheral") != 0) {
            $db_peripherals = array();
            $computer_Item = new Computer_Item();
            if ($no_history === FALSE) {
               if ($pfConfig->getValue('import_peripheral') == 1) {
                  // Global import
                  $query = "SELECT `glpi_peripherals`.`name`, `glpi_peripherals`.`manufacturers_id`,
                        `glpi_peripherals`.`serial`, 
                        `glpi_peripherals`.`is_global`, `glpi_computers_items`.`id` as link_id 
                        FROM `glpi_computers_items`
                     LEFT JOIN `glpi_peripherals` ON `items_id`=`glpi_peripherals`.`id`
                     WHERE `itemtype`='Peripheral'
                        AND `computers_id`='".$computers_id."'
                        AND `entities_id`='".$entities_id."'
                        AND `glpi_computers_items`.`is_dynamic`='1'";
                  $result = $DB->query($query);
                  while ($data = $DB->fetch_assoc($result)) {
                     if ($data['is_global'] == 0) {
                        $computer_Item->delete(array('id' => $data['link_id']), 1);
                     } else {
                        $idtmp = $data['link_id'];
                        unset($data['link_id']);
                        unset($data['is_global']);
                        $data1 = Toolbox::addslashes_deep($data);
                        $data2 = array_map('strtolower', $data1);
                        $db_peripherals[$idtmp] = $data2;
                     }
                  }                  
               } else if ($pfConfig->getValue('import_peripheral') == 2) {
                  // Unique import
                  $query = "SELECT `glpi_peripherals`.`name`, `glpi_peripherals`.`manufacturers_id`,
                        `glpi_peripherals`.`serial`, 
                        `glpi_peripherals`.`is_global`, `glpi_computers_items`.`id` as link_id 
                        FROM `glpi_computers_items`
                     LEFT JOIN `glpi_peripherals` ON `items_id`=`glpi_peripherals`.`id`
                     WHERE `itemtype`='Peripheral'
                        AND `computers_id`='".$computers_id."'
                        AND `entities_id`='".$entities_id."'
                        AND `glpi_computers_items`.`is_dynamic`='1'";
                  $result = $DB->query($query);
                  while ($data = $DB->fetch_assoc($result)) {
                     if ($data['is_global'] == 1) {
                        $computer_Item->delete(array('id' => $data['link_id']), 1);
                     } else {
                        $idtmp = $data['link_id'];
                        unset($data['link_id']);
                        unset($data['is_global']);
                        $data1 = Toolbox::addslashes_deep($data);
                        $data2 = array_map('strtolower', $data1);
                        $db_peripherals[$idtmp] = $data2;
                     }
                  }                  
               } else if ($pfConfig->getValue('import_peripheral') == 3) {
                  // Unique import on serial number
                  $query = "SELECT `glpi_peripherals`.`name`, `glpi_peripherals`.`manufacturers_id`,
                        `glpi_peripherals`.`serial`, 
                        `glpi_peripherals`.`is_global`, `glpi_computers_items`.`id` as link_id 
                        FROM `glpi_computers_items`
                     LEFT JOIN `glpi_peripherals` ON `items_id`=`glpi_peripherals`.`id`
                     WHERE `itemtype`='Peripheral'
                        AND `computers_id`='".$computers_id."'
                        AND `entities_id`='".$entities_id."'
                        AND `glpi_computers_items`.`is_dynamic`='1'";
                  $result = $DB->query($query);
                  while ($data = $DB->fetch_assoc($result)) {
                     if ($data['serial'] == ''
                             || $data['is_global'] == 1) {
                        $computer_Item->delete(array('id' => $data['link_id']), 1);
                     } else {
                        $idtmp = $data['link_id'];
                        unset($data['link_id']);
                        unset($data['is_global']);
                        $data1 = Toolbox::addslashes_deep($data);
                        $data2 = array_map('strtolower', $data1);
                        $db_peripherals[$idtmp] = $data2;
                     }
                  }                  
               }
            }
            
            if (count($db_peripherals) == 0) {
               foreach ($a_computerinventory['peripheral'] as $a_peripheral) {
                  $a_peripheral['entities_id'] = $entities_id;
                  $this->addPeripheral($a_peripheral, $computers_id, $no_history);
               }
            } else {
               // Check all fields from source: 
               foreach ($a_computerinventory['peripheral'] as $key => $arrays) {
                  $arrayslower = array_map('strtolower', $arrays);
                  foreach ($db_peripherals as $keydb => $arraydb) {
                     if ($arrayslower == $arraydb) {
                        unset($a_computerinventory['peripheral'][$key]);
                        unset($db_peripherals[$keydb]);
                        break;
                     }
                  }
               }

               if (count($a_computerinventory['peripheral']) == 0
                  AND count($db_peripherals) == 0) {
                  // Nothing to do
               } else {
                  if (count($db_peripherals) != 0) {
                     // Delete peripherals links in DB
                     foreach ($db_peripherals as $idtmp => $data) {
                        $computer_Item->delete(array('id'=>$idtmp), 1);
                     }
                  }
                  if (count($a_computerinventory['peripheral']) != 0) {
                     foreach($a_computerinventory['peripheral'] as $a_peripheral) {
                        $a_peripheral['entities_id'] = $entities_id;
                        $this->addPeripheral($a_peripheral, $computers_id, $no_history);
                     }
                  }
               }
            }
         }
         
      // * storage
      // Manage by uuid to correspond with GLPI data
//         $db_storage = array();
//         if ($no_history === FALSE) {
//            $query = "SELECT `id`, `uuid` FROM ".
//                "`glpi_plugin_fusioninventory_inventorycomputerstorages`
//                WHERE `computers_id` = '$computers_id'";
//            $result = $DB->query($query);         
//            while ($data = $DB->fetch_assoc($result)) {
//               $idtmp = $data['id'];
//               unset($data['id']);            
//               $data = Toolbox::addslashes_deep($data);
//               $data = array_map('strtolower', $data);
//               $db_storage[$idtmp] = $data;
//            }
//         }         
//         if (count($db_storage) == 0) {
//            $a_links = array();
//            $a_uuid  = array();
//            foreach ($a_computerinventory['storage'] as $a_storage) {
//               $a_storage['computers_id'] = $computers_id;
//               $insert_id = $pfInventoryComputerStorage->add($a_storage);
//               if (isset($a_storage['uuid'])) {
//                  $a_uuid[$a_storage['uuid']] = $insert_id;
//                  if (isset($a_storage['uuid_link'])) {
//                     if (is_array($a_storage['uuid_link'])) {
//                        $a_links[$insert_id] = $a_storage['uuid_link'];
//                     } else {
//                        $a_links[$insert_id][] = $a_storage['uuid_link'];
//                     }
//                  }
//               }
//            }
//            foreach ($a_links as $id=>$data) {
//               foreach ($data as $num=>$uuid) {
//                  $a_links[$id][$num] = $a_uuid[$uuid];
//               }
//            }
//            foreach ($a_links as $id=>$data) {
//               foreach ($data as $id2) {
//                  $input = array();
//                  $input['plugin_fusioninventory_inventorycomputerstorages_id_1'] = $id;
//                  $input['plugin_fusioninventory_inventorycomputerstorages_id_2'] = $id2;
//                  $pfInventoryComputerStorage_Storage->add($input);
//               }
//            }            
//         } else {
//            // Check only field *** from source: 
//            
//         }
         
         
   }
   
   
   
   /**
    * Add a new processor component
    * 
    * @param type $data
    * @param type $computers_id
    * @param type $no_history
    * 
    * @return nothing
    */
   function addProcessor($data, $computers_id, $no_history) {
      $item_DeviceProcessor         = new Item_DeviceProcessor();
      $deviceProcessor              = new DeviceProcessor();
      
      $processors_id = $deviceProcessor->import($data);
      $data['deviceprocessors_id']  = $processors_id;
      $data['itemtype']             = 'Computer';
      $data['items_id']             = $computers_id;
      $data['is_dynamic']           = 1;
      $data['_no_history']          = $no_history;
      $item_DeviceProcessor->add($data, array(), FALSE);      
   }
   
   
   
   /**
    * Add a new memory component
    * 
    * @param type $data
    * @param type $computers_id
    * @param type $no_history
    * 
    * @return nothing
    */
   function addMemory($data, $computers_id, $no_history) {
      $item_DeviceMemory            = new Item_DeviceMemory();
      $deviceMemory                 = new DeviceMemory();
      
      $memories_id = $deviceMemory->import($data);
      $data['devicememories_id'] = $memories_id;
      $data['itemtype']          = 'Computer';
      $data['items_id']          = $computers_id;
      $data['is_dynamic']        = 1;
      $data['_no_history']       = $no_history;
      $item_DeviceMemory->add($data, array(), FALSE);
   }
   
   
   
   /**
    * Add a new hard disk component
    * 
    * @param type $data
    * @param type $computers_id
    * @param type $no_history
    * 
    * @return nothing
    */
   function addHardDisk($data, $computers_id, $no_history) {
      $item_DeviceHardDrive         = new Item_DeviceHardDrive();
      $deviceHardDrive              = new DeviceHardDrive();
      
      $harddrives_id = $deviceHardDrive->import($data);
      $data['deviceharddrives_id']  = $harddrives_id;
      $data['itemtype']             = 'Computer';
      $data['items_id']             = $computers_id;
      $data['is_dynamic']           = 1;
      $data['_no_history']          = $no_history;
      $item_DeviceHardDrive->add($data, array(), FALSE);
   }
   
   
   
   /**
    * Add a new graphic card component
    * 
    * @param type $data
    * @param type $computers_id
    * @param type $no_history
    * 
    * @return nothing
    */
   function addGraphicCard($data, $computers_id, $no_history) {
      $item_DeviceGraphicCard       = new Item_DeviceGraphicCard();
      $deviceGraphicCard            = new DeviceGraphicCard();
      
      $graphiccards_id = $deviceGraphicCard->import($data);
      $data['devicegraphiccards_id']   = $graphiccards_id;
      $data['itemtype']                = 'Computer';
      $data['items_id']                = $computers_id;
      $data['is_dynamic']              = 1;
      $data['_no_history']             = $no_history;
      $item_DeviceGraphicCard->add($data, array(), FALSE);      
   }
   
   
   
   /**
    * Add a new sound card component
    * 
    * @param type $data
    * @param type $computers_id
    * @param type $no_history
    * 
    * @return nothing
    */
   function addSoundCard($data, $computers_id, $no_history) {
      $item_DeviceSoundCard         = new Item_DeviceSoundCard();
      $deviceSoundCard              = new DeviceSoundCard();
      
      $sounds_id = $deviceSoundCard->import($data);
      $data['devicesoundcards_id']  = $sounds_id;
      $data['itemtype']             = 'Computer';
      $data['items_id']             = $computers_id;
      $data['is_dynamic']           = 1;
      $data['_no_history']          = $no_history;
      $item_DeviceSoundCard->add($data, array(), FALSE);
   }
   
   
   
   /**
    * Add a new controller component
    * 
    * @param type $data
    * @param type $computers_id
    * @param type $no_history
    * 
    * @return nothing
    */
   function addControl($data, $computers_id, $no_history) {
      $item_DeviceControl           = new Item_DeviceControl();
      $deviceControl                = new DeviceControl();
      
      $controllers_id = $deviceControl->import($data);
      $data['devicecontrols_id'] = $controllers_id;
      $data['itemtype']          = 'Computer';
      $data['items_id']          = $computers_id;
      $data['is_dynamic']        = 1;
      $data['_no_history']       = $no_history;
      $item_DeviceControl->add($data, array(), FALSE);
   }
   
   
   
   /**
    * Load software from DB are in the incomming inventory
    * 
    * @global type $DB
    * 
    * @param integer $entities_id entitity id
    * @param array $a_soft list of software from the agent inventory
    * @param integer $lastid last id search to not search from beginning
    * 
    * @return integer last id
    */
   function loadSoftwares($entities_id, $a_soft, $lastid = 0) {
      global $DB;
      
      $whereid = '';
      if ($lastid > 0) {
         $whereid = ' AND `id` > "'.$lastid.'"';
      } else {
         $whereid = " AND `name` IN ('".  implode("', '", $a_soft)."')";
      }
      
      $sql = "SELECT max( id ) AS max FROM `glpi_softwares`";
      $result = $DB->query($sql);
      $data = $DB->fetch_assoc($result);
      $lastid = $data['max'];
      
      $sql = "SELECT * FROM `glpi_softwares`
      WHERE `entities_id`='".$entities_id."'".$whereid;
      $result = $DB->query($sql); 

      while ($data = $DB->fetch_assoc($result)) {
         $this->softList[$data['name']."$$$$".$data['manufacturers_id']] = $data['id'];
      }
      return $lastid;
   }
   
   
   
   /**
    * Load software versions from DB are in the incomming inventory
    * 
    * @global type $DB
    * 
    * @param integer $entities_id entitity id
    * @param array $a_softVersion list of software versions from the agent inventory
    * @param integer $lastid last id search to not search from beginning
    * 
    * @return type
    */
   function loadSoftwareVersions($entities_id, $a_softVersion, $lastid = 0) {
      global $DB;
      
      $whereid = '';
      if ($lastid > 0) {
         $whereid = ' AND `id` > "'.$lastid.'"';
      } else {
         $whereid = " AND `name` IN ('".  implode("', '", $a_softVersion)."')";
      }
      
      $sql = "SELECT max( id ) AS max FROM `glpi_softwareversions`";
      $result = $DB->query($sql);
      $data = $DB->fetch_assoc($result);
      $lastid = $data['max'];
      
      $sql = "SELECT * FROM `glpi_softwareversions`
      WHERE `entities_id`='".$entities_id."'".$whereid;
      $result = $DB->query($sql);         
      while ($data = $DB->fetch_assoc($result)) { 
         $this->softVersionList[$data['name']."$$$$".$data['softwares_id']] = $data['id'];
      }
      return $lastid;
   }
   
   
   
   /**
    * Add a new software
    * 
    * @global type $DB
    * 
    * @param array $a_software
    * @param integer $computers_id id of the computer
    * @param boolean $no_history set TRUE if not want history
    * @param array $options
    * 
    * @return nothing
    */
   function addSoftware($a_software, $computers_id, $no_history, $options) {
      global $DB;

      $new = 0;
      $add = 0;
      if (isset($a_software['softwares_id'])) {
         if ($a_software['softwares_id'] == "-1") {
            //Look for the software by his name in GLPI for a specific entity
            $sql = "SELECT `id` FROM `glpi_softwares`
                    WHERE `manufacturers_id` = '".$a_software['manufacturers_id']."'
                          AND `name` = '".$a_software['name']."' " .
                          getEntitiesRestrictRequest('AND', 'glpi_softwares', 'entities_id', 
                                                     $a_software['entities_id'], TRUE).
                    " LIMIT 1";

            $res_soft = $DB->query($sql);
            if ($DB->numrows($res_soft) > 0) {
               $soft = $DB->fetch_assoc($res_soft);
               $a_software['softwares_id'] = $soft["id"];
            } else {
               $add = 1;
            }
         } else {
            // It's ok
         }
      } else {
         // notin DB, add new
         $add = 1;
      }
      
      if ($add == 1) {
         $a_software['softwares_id'] = $this->software->add($a_software, $options, FALSE);
         $this->addPrepareLog($a_software['softwares_id'], 'Software');
         $new = 1; 
      }
      
      $options = array();
      $options['disable_unicity_check'] = TRUE;
      if ($new == 1) {
         $a_software['name'] = $a_software['version'];
         $a_software['_no_history'] = $no_history;
         $softwareversions_id = $this->softwareVersion->add($a_software, $options, FALSE);
         $this->addPrepareLog($softwareversions_id, 'SoftwareVersion');
      } else {
         $softwareversions_id = 0;
         if (isset($this->softVersionList[$a_software['version']."$$$$".
                  $a_software['softwares_id']])) {
            $softwareversions_id = 
               $this->softVersionList[$a_software['version']."$$$$".$a_software['softwares_id']];
         } else {
            $a_software['name'] = $a_software['version'];
      $a_software['_no_history'] = $no_history;
            $softwareversions_id = $this->softwareVersion->add($a_software, $options, FALSE);
            $this->addPrepareLog($softwareversions_id, 'SoftwareVersion');
         }
      }
      $a_software['computers_id']         = $computers_id;
      $a_software['softwareversions_id']  = $softwareversions_id;
      $a_software['is_dynamic']           = 1;
      $a_software['_no_history']          = $no_history;

      $this->computer_SoftwareVersion->add($a_software, $options);
   }
   
   
   
   function addMonitor($data, $computers_id, $no_history) {
      global $DB;
      
      $computer_Item = new Computer_Item();
      $monitor       = new Monitor();
      $pfConfig      = new PluginFusioninventoryConfig();
      
      $monitors_id = 0;
      if ($pfConfig->getValue('import_monitor') == 1) {
         // Global import
         $query = "SELECT `glpi_monitors`.`id` FROM `glpi_monitors`
            WHERE `name`='".$data['name']."'
               AND `manufacturers_id`='".$data['manufacturers_id']."'
               AND `serial`='".$data['serial']."'
               AND `comment`='".$data['comment']."'
               AND `is_global`='1'
               AND `entities_id`='".$data['entities_id']."'
            LIMIT 1";
         $result = $DB->query($query);
         if ($DB->numrows($result) == 1) {
            $db_data = $DB->fetch_assoc($result);
            $monitors_id = $db_data['id'];
         } else {
            $data['is_global'] = 1;
            $monitors_id = $monitor->add($data);
         }
      } else if ($pfConfig->getValue('import_monitor') == 2) {
         // Unique import
         $added = 0;
         if ($data['serial'] != '') {
            $query = "SELECT `glpi_monitors`.`id` FROM `glpi_monitors`
               WHERE `serial`='".$data['serial']."'
                  AND `is_global`='0'
                  AND `entities_id`='".$data['entities_id']."'
               LIMIT 1";
            $result = $DB->query($query);
            if ($DB->numrows($result) == 1) {
               $db_data = $DB->fetch_assoc($result);
               $monitors_id = $db_data['id'];
            }
         }
         if ($monitors_id == 0) {
            $query = "SELECT `glpi_monitors`.`id` FROM `glpi_monitors`
               LEFT JOIN `glpi_computers_items` ON `items_id`=`glpi_monitors`.`id`
               WHERE `name`='".$data['name']."'
                  AND `manufacturers_id`='".$data['manufacturers_id']."'
                  AND `serial`='".$data['serial']."'
                  AND `comment`='".$data['comment']."'
                  AND `is_global`='0'
                  AND `entities_id`='".$data['entities_id']."'
                  AND `glpi_computers_items`.`itemtype`='Monitor'
                  AND `glpi_computers_items`.`id` IS NULL
               LIMIT 1";
            $result = $DB->query($query);
            if ($DB->numrows($result) == 1) {
               $db_data = $DB->fetch_assoc($result);
               $monitors_id = $db_data['id'];
            } else {
               $data['is_global'] = 0;
               $monitors_id = $monitor->add($data);
               $added = 1;
            }
         }
         if ($added == 0) {
            $monitor->getFromDB($monitors_id);
            $computer_Item->disconnectForItem($monitor);
         }
      } else if ($pfConfig->getValue('import_monitor') == 3) {
         // Unique import on serial number      
         $added = 0;
         $query = "SELECT `glpi_monitors`.`id` FROM `glpi_monitors`
            WHERE `name`='".$data['name']."'
               AND `manufacturers_id`='".$data['manufacturers_id']."'
               AND `serial`='".$data['serial']."'
               AND `comment`='".$data['comment']."'
               AND `is_global`='0'
               AND `entities_id`='".$data['entities_id']."'
            LIMIT 1";
         $result = $DB->query($query);
         if ($DB->numrows($result) == 1) {
            $db_data = $DB->fetch_assoc($result);
            $monitors_id = $db_data['id'];
         } else {
            $data['is_global'] = 0;
            $monitors_id = $monitor->add($data);
            $added = 1;
         }
         if ($added == 0) {
            $monitor->getFromDB($monitors_id);
            $computer_Item->disconnectForItem($monitor);
         }
      }
      $data['computers_id']   = $computers_id;
      $data['itemtype']       = 'Monitor';
      $data['items_id']       = $monitors_id;
      $data['is_dynamic']     = 1;
      $data['_no_history']    = $no_history;
      $computer_Item->add($data, array(), FALSE);      
   }
   
   
      
   function addPrinter($data, $computers_id, $no_history) {
      global $DB;
      
      $computer_Item = new Computer_Item();
      $printer       = new Printer();
      $pfConfig      = new PluginFusioninventoryConfig();
      
      $printers_id = 0;
      if ($pfConfig->getValue('import_printer') == 1) {
         // Global import
         $query = "SELECT `glpi_printers`.`id` FROM `glpi_printers`
            WHERE `name`='".$data['name']."'
               AND `serial`='".$data['serial']."'
               AND `is_global`='1'
               AND `entities_id`='".$data['entities_id']."'
            LIMIT 1";
         $result = $DB->query($query);
         if ($DB->numrows($result) == 1) {
            $db_data = $DB->fetch_assoc($result);
            $printers_id = $db_data['id'];
         } else {
            $data['is_global'] = 1;
            $printers_id = $printer->add($data);
         }
      } else if ($pfConfig->getValue('import_printer') == 2) {
         // Unique import
         $added = 0;
         if ($data['serial'] != '') {
            $query = "SELECT `glpi_printers`.`id` FROM `glpi_printers`
               WHERE `serial`='".$data['serial']."'
                  AND `is_global`='0'
                  AND `entities_id`='".$data['entities_id']."'
               LIMIT 1";
            $result = $DB->query($query);
            if ($DB->numrows($result) == 1) {
               $db_data = $DB->fetch_assoc($result);
               $printers_id = $db_data['id'];
            }
         }
         if ($printers_id == 0) {
            $query = "SELECT `glpi_printers`.`id` FROM `glpi_printers`
               LEFT JOIN `glpi_computers_items` ON `items_id`=`glpi_printers`.`id`
               WHERE `name`='".$data['name']."'
                  AND `serial`='".$data['serial']."'
                  AND `is_global`='0'
                  AND `entities_id`='".$data['entities_id']."'
                  AND `glpi_computers_items`.`itemtype`='Printer'
                  AND `glpi_computers_items`.`id` IS NULL
               LIMIT 1";
            $result = $DB->query($query);
            if ($DB->numrows($result) == 1) {
               $db_data = $DB->fetch_assoc($result);
               $printers_id = $db_data['id'];
            } else {
               $data['is_global'] = 0;
               $printers_id = $printer->add($data);
               $added = 1;
            }
         }
         if ($added == 0) {
            $printer->getFromDB($printers_id);
            $computer_Item->disconnectForItem($printer);
         }
      } else if ($pfConfig->getValue('import_printer') == 3) {
         // Unique import on serial number      
         $added = 0;
         $query = "SELECT `glpi_printers`.`id` FROM `glpi_printers`
            WHERE `name`='".$data['name']."'
               AND `serial`='".$data['serial']."'
               AND `is_global`='0'
               AND `entities_id`='".$data['entities_id']."'
            LIMIT 1";
         $result = $DB->query($query);
         if ($DB->numrows($result) == 1) {
            $db_data = $DB->fetch_assoc($result);
            $printers_id = $db_data['id'];
         } else {
            $data['is_global'] = 0;
            $printers_id = $printer->add($data);
            $added = 1;
         }
         if ($added == 0) {
            $printer->getFromDB($printers_id);
            $computer_Item->disconnectForItem($printer);
         }
      }
      $data['computers_id']   = $computers_id;
      $data['itemtype']       = 'Printer';
      $data['items_id']       = $printers_id;
      $data['is_dynamic']     = 1;
      $data['_no_history']    = $no_history;
      $computer_Item->add($data, array(), FALSE);      
   }
   
   
   
   function addPeripheral($data, $computers_id, $no_history) {
      global $DB;
      
      $computer_Item = new Computer_Item();
      $peripheral    = new Peripheral();
      $pfConfig      = new PluginFusioninventoryConfig();
      
      $peripherals_id = 0;
      if ($pfConfig->getValue('import_peripheral') == 1) {
         // Global import
         $query = "SELECT `glpi_peripherals`.`id` FROM `glpi_peripherals`
            WHERE `name`='".$data['name']."'
               AND `manufacturers_id`='".$data['manufacturers_id']."'
               AND `serial`='".$data['serial']."'
               AND `is_global`='1'
               AND `entities_id`='".$data['entities_id']."'
            LIMIT 1";
         $result = $DB->query($query);
         if ($DB->numrows($result) == 1) {
            $db_data = $DB->fetch_assoc($result);
            $peripherals_id = $db_data['id'];
         } else {
            $data['is_global'] = 1;
            $peripherals_id = $peripheral->add($data);
         }
      } else if ($pfConfig->getValue('import_peripheral') == 2) {
         // Unique import
         $added = 0;
         if ($data['serial'] == '') {
            $query = "SELECT `glpi_peripherals`.`id` FROM `glpi_peripherals`
               WHERE `serial`='".$data['serial']."'
                  AND `is_global`='0'
                  AND `entities_id`='".$data['entities_id']."'
               LIMIT 1";
            $result = $DB->query($query);
            if ($DB->numrows($result) == 1) {
               $db_data = $DB->fetch_assoc($result);
               $peripherals_id = $db_data['id'];
            }
         }
         if ($peripherals_id == 0) {
            $query = "SELECT `glpi_peripherals`.`id` FROM `glpi_peripherals`
               LEFT JOIN `glpi_computers_items` ON `items_id`=`glpi_peripherals`.`id`
               WHERE `name`='".$data['name']."'
                  AND `manufacturers_id`='".$data['manufacturers_id']."'
                  AND `serial`='".$data['serial']."'
                  AND `is_global`='0'
                  AND `entities_id`='".$data['entities_id']."'
                  AND `glpi_computers_items`.`itemtype`='Monitor'
                  AND `glpi_computers_items`.`id` IS NULL
               LIMIT 1";
            $result = $DB->query($query);
            if ($DB->numrows($result) == 1) {
               $db_data = $DB->fetch_assoc($result);
               $peripherals_id = $db_data['id'];
            } else {
               $data['is_global'] = 0;
               $peripherals_id = $peripheral->add($data);
               $added = 1;
            }
         }
         if ($added == 0) {
            $peripheral->getFromDB($peripherals_id);
            $computer_Item->disconnectForItem($peripheral);
         }
      } else if ($pfConfig->getValue('import_peripheral') == 3) {
         // Unique import on serial number      
         $added = 0;
         $query = "SELECT `glpi_peripherals`.`id` FROM `glpi_peripherals`
            WHERE `name`='".$data['name']."'
               AND `manufacturers_id`='".$data['manufacturers_id']."'
               AND `serial`='".$data['serial']."'
               AND `is_global`='0'
               AND `entities_id`='".$data['entities_id']."'
            LIMIT 1";
         $result = $DB->query($query);
         if ($DB->numrows($result) == 1) {
            $db_data = $DB->fetch_assoc($result);
            $peripherals_id = $db_data['id'];
         } else {
            $data['is_global'] = 0;
            $peripherals_id = $peripheral->add($data);
            $added = 1;
         }
         if ($added == 0) {
            $peripheral->getFromDB($peripherals_id);
            $computer_Item->disconnectForItem($peripheral);
         }
      }
      $data['computers_id']   = $computers_id;
      $data['itemtype']       = 'Peripheral';
      $data['items_id']       = $peripherals_id;
      $data['is_dynamic']     = 1;
      $data['_no_history']    = $no_history;
      $computer_Item->add($data, array(), FALSE);      
   }


   
   function arrayDiffEmulation($arrayFrom, $arrayAgainst) {
      $arrayAgainsttmp = array();
      foreach ($arrayAgainst as $key => $data) {
         $arrayAgainsttmp[serialize($data)] = $key;
      }
      
      foreach ($arrayFrom as $key => $value) {
         if (isset($arrayAgainsttmp[serialize($value)])) {
            unset($arrayFrom[$key]);
         }
      }
      return $arrayFrom;
   }
    
    
    
   function addPrepareLog($items_id, $itemtype, $itemtype_link='') {
      $this->log_add[] = array($items_id, $itemtype, $itemtype_link, $_SESSION["glpi_currenttime"]);
   }
    
    
   function addLog() {
      global $DB;
      
      if (count($this->log_add) > 0) {
         $username = addslashes($_SESSION["glpiname"]);

         $dataLog = array();
         foreach ($this->log_add as $data) {
            $dataLog[] = "('".implode("', '", $data)."', '".Log::HISTORY_CREATE_ITEM."', 
                           '".$username."', '', '')";
         }      

         // Build query
         $query = "INSERT INTO `glpi_logs`
                          (`items_id`, `itemtype`, `itemtype_link`, `date_mod`, `linked_action`, 
                            `user_name`, `old_value`, `new_value`)
                   VALUES ".implode(", ", $dataLog);

         $DB->query($query); 
         
      } 
   }
   
   
   
   function setDynamicLinkItems($computers_id) {
      global $DB;
      
      $DB->query("UPDATE `glpi_computerdisks` SET `is_dynamic`='1'
                     WHERE `computers_id`='".$computers_id."'");
      
      $DB->query("UPDATE `glpi_computers_items` SET `is_dynamic`='1'
                     WHERE `computers_id`='".$computers_id."'");  
      
      $DB->query("UPDATE `glpi_computers_softwareversions` SET `is_dynamic`='1'
                     WHERE `computers_id`='".$computers_id."'"); 
      
      $DB->query("UPDATE `glpi_computervirtualmachines` SET `is_dynamic`='1'
                     WHERE `computers_id`='".$computers_id."'"); 
      
      $a_tables = array("glpi_networkports", "glpi_items_devicecases", "glpi_items_devicecontrols",
                        "glpi_items_devicedrives", "glpi_items_devicegraphiccards",
                        "glpi_items_deviceharddrives", "glpi_items_devicememories",
                        "glpi_items_devicemotherboards", "glpi_items_devicenetworkcards",
                        "glpi_items_devicepcis", "glpi_items_devicepowersupplies",
                        "glpi_items_deviceprocessors", "glpi_items_devicesoundcards");
      
      foreach ($a_tables as $table) {
         $DB->query("UPDATE `".$table."` SET `is_dynamic`='1'
                        WHERE `items_id`='".$computers_id."'
                           AND `itemtype`='Computer'"); 
      }
   }
}

?>