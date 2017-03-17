<?php

/*
   ------------------------------------------------------------------------
   FusionInventory
   Copyright (C) 2010-2016 by the FusionInventory Development Team.

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
   @author    Alexandre Delaunay
   @co-author
   @copyright Copyright (c) 2010-2016 FusionInventory team
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

class PluginFusioninventoryDeployGroup_Staticdata extends CommonDBRelation{

   static $rightname = "plugin_fusioninventory_group";

   // From CommonDBRelation
   static public $itemtype_1 = 'PluginFusioninventoryDeployGroup';
   static public $items_id_1 = 'groups_id';

   static public $itemtype_2 = 'itemtype';
   static public $items_id_2 = 'items_id';

   /**
    * @see CommonGLPI::getTabNameForItem()
   **/
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if (!$withtemplate
          && ($item->getType() == 'PluginFusioninventoryDeployGroup')
             && $item->fields['type'] == PluginFusioninventoryDeployGroup::STATIC_GROUP) {

         $tabs[1] = _n('Criterion', 'Criteria', 2);
         $count = countElementsInTable(getTableForItemType(__CLASS__),
                                  "`itemtype`='Computer'
                                    AND `plugin_fusioninventory_deploygroups_id`='".$item->getID()."'");
         if ($_SESSION['glpishow_count_on_tabs']) {
            $tabs[2] = self::createTabEntry(_n('Associated item','Associated items', $count), $count);
         } else {
            $tabs[2] = _n('Associated item','Associated items', $count);
         }

         return $tabs;
      }
      return '';
   }

   /**
    * @param $item         CommonGLPI object
    * @param $tabnum       (default 1)
    * @param $withtemplate (default 0)
   **/
   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      switch ($tabnum) {
         case 1:
            self::showCriteriaAndSearch($item);
            break;
         case 2:
            self::showResults($item);
            break;
      }

      return true;
   }

   static function showCriteriaAndSearch(PluginFusioninventoryDeployGroup $item) {
      $search_params                 = PluginFusioninventoryDeployGroup::getSearchParamsAsAnArray($item, true);
      //If metacriteria array is empty, remove it as it displays the metacriteria form,
      //and it's is not we want !
      if (isset($search_params['metacriteria']) && empty($search_params['metacriteria'])) {
         unset($search_params['metacriteria']);
      }
      PluginFusioninventoryDeployGroup::showCriteria($item, true, $search_params);
      unset($_SESSION['glpisearch']['PluginFusioninventoryComputer']);
      if (isset($_GET['preview'])) {
         //Add extra parameters for massive action display : only the Add action should be displayed
         $search_params['massiveactionparams']['extraparams']['id'] = $item->getID();
         $search_params['massiveactionparams']['extraparams']['custom_action'] = 'add_to_group';
         $search_params['massiveactionparams']['extraparams']['massive_action_fields'] = array ('action', 'id');
         foreach (array('sort', 'order', 'start') as $field) {
            if (isset($_GET[$field])) {
               $search_params[$field] = $_GET[$field];
            }
         }
         $search_params['target'] = Toolbox::getItemTypeFormURL("PluginFusioninventoryDeployGroup" , true)."?id=".$item->getID()."&preview=1";
         Search::showList('PluginFusioninventoryComputer', $search_params);
      }
   }

   static function showResults(PluginFusioninventoryDeployGroup $group) {
      $computers_params['metacriteria'] = array();
      $computers_params['criteria'][]   = array('searchtype' => 'equals',
                                                'value' => $_GET['id'],
                                                'field' => 6000);
      $search_params    = Search::manageParams('PluginFusioninventoryComputer', $computers_params);

      //Add extra parameters for massive action display : only the Delete action should be displayed
      $search_params['massiveactionparams']['extraparams']['id'] = $_GET['id'];
      $search_params['massiveactionparams']['extraparams']['custom_action'] = 'delete_from_group';
      $search_params['massiveactionparams']['extraparams']['massive_action_fields'] = array ('action', 'id');
      $search_params['target'] = Toolbox::getItemTypeFormURL("PluginFusioninventoryDeployGroup" , true)."?id=".$group->getID();
      foreach (array('sort', 'order', 'start') as $field) {
         if (isset($_GET[$field])) {
            $search_params[$field] = $_GET[$field];
         }
      }
      Search::showList('PluginFusioninventoryComputer', $search_params);
   }


   /**
   * Duplicate entries from one group to another
   * @param $source_deploygroups_id the source group ID
   * @param $target_deploygroups_id the target group ID
   * @return the duplication status, as a boolean
   */
   static function duplicate($source_deploygroups_id, $target_deploygroups_id) {
      $result        = true;
      $pfStaticGroup = new self();

      $groups = $pfStaticGroup->find("`plugin_fusioninventory_deploygroups_id`='$source_deploygroups_id'");
      foreach ($groups as $group) {
         unset($group['id']);
         $group['plugin_fusioninventory_deploygroups_id']
            = $target_deploygroups_id;
         if (!$pfStaticGroup->add($group)) {
            $result |= false;
         }
      }
      return $result;
   }   
}

?>
