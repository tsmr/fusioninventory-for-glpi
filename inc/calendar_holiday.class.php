<?php
/*
 * @version $Id$
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2012 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file: Julien Dombre
// Purpose of file:
// ----------------------------------------------------------------------

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class Calendar_Holiday extends CommonDBRelation {

   public $auto_message_on_action = false;

   // From CommonDBRelation
   public $itemtype_1 = 'Calendar';
   public $items_id_1 = 'calendars_id';
   public $itemtype_2 = 'Holiday';
   public $items_id_2 = 'holidays_id';


   function canCreateItem() {

      $calendar = new Calendar();
      return $calendar->can($this->fields['calendars_id'],'w');
   }


   function prepareInputForAdd($input) {

      if (!isset($input['holidays_id'])
          || !isset($input['calendars_id'])
          || ($input['calendars_id'] <= 0)
          || ($input['holidays_id'] <= 0)) {
         return false;
      }
      return $input;
   }


   /**
    * Show holidays for a calendar
    *
    * @param $calendar Calendar object
   **/
   static function showForCalendar(Calendar $calendar) {
      global $DB, $CFG_GLPI;

      $ID = $calendar->getField('id');
      if (!$calendar->can($ID,'r')) {
         return false;
      }

      $canedit = $calendar->can($ID,'w');

      $rand    = mt_rand();

      $query = "SELECT DISTINCT `glpi_calendars_holidays`.`id` AS linkID,
                                `glpi_holidays`.*
                FROM `glpi_calendars_holidays`
                LEFT JOIN `glpi_holidays`
                     ON (`glpi_calendars_holidays`.`holidays_id` = `glpi_holidays`.`id`)
                WHERE `glpi_calendars_holidays`.`calendars_id` = '$ID'
                ORDER BY `glpi_holidays`.`name`";
      $result = $DB->query($query);
      $numrows = $DB->numrows($result);


      echo "<form name='calendarholiday_form$rand' id='calendarholiday_form$rand' method='post'
             action='";
      echo Toolbox::getItemTypeFormURL(__CLASS__)."'>";
//       if ($canedit && $numrows) {
//          Html::openArrowMassives("calendarholiday_form$rand", true, true);
//          Html::closeArrowMassives(array('delete' => __('Delete')));
//       }



      echo "<div class='center'><table class='tab_cadre_fixehov'>";
      echo "<tr><th colspan='2'>".__('Name')."</th>";
      echo "<th>".__('Start')."</th>";
      echo "<th>".__('End')."</th>";
      echo "<th>".__('Recurrent')."</th>";
      echo "</tr>";



      $used = array();

      if ($numrows) {

         Session::initNavigateListItems('Holiday',
         //TRANS : %1$s is the itemtype name, %2$s is the name of the item (used for headings of a list)
                                        sprintf(__('%1$s = %2$s'), $calendar->getTypeName(1),
                                                $calendar->fields["name"]));

         while ($data = $DB->fetch_assoc($result)) {
            Session::addToNavigateListItems('Holiday', $data["id"]);
            echo "<tr class='tab_bg_1'>";
            echo "<td width='10'>";
            if ($canedit) {
               echo "<input type='checkbox' name='item[".$data["linkID"]."]' value='1'>";
            } else {
               echo "&nbsp;";
            }
            echo "</td>";
            $used[$data['id']] = $data['id'];
            echo "<td><a href='".Toolbox::getItemTypeFormURL('Holiday')."?id=".$data['id']."'>".
                      $data["name"]."</a></td>";
            echo "<td>".Html::convDate($data["begin_date"])."</td>";
            echo "<td>".Html::convDate($data["end_date"])."</td>";
            echo "<td>".Dropdown::getYesNo($data["is_perpetual"])."</td>";
            echo "</tr>";
         }
      }

      if ($canedit) {
         echo "<tr class='tab_bg_2'><td class='right'  colspan='4'>";
         echo "<input type='hidden' name='calendars_id' value='$ID'>";
         Holiday::dropdown(array('used'   => $used,
                                 'entity' => $calendar->fields["entities_id"]));
         echo "</td><td class='center'>";
         echo "<input type='submit' name='add' value=\"".__s('Add')."\" class='submit'>";
         echo "</td></tr>";
      }

      echo "</table></div>";

      if ($canedit && $numrows) {
         Html::openArrowMassives("calendarholiday_form$rand",true);
         Html::closeArrowMassives(array('delete' => __('Delete')));
      }
      echo "</form>";
   }


   /**
    * Duplicate all holidays from a calendar to his clone
    *
    * @param $oldid
    * @param $newid
   **/
   static function cloneCalendar($oldid, $newid) {
      global $DB;

      $query = "SELECT *
                FROM `glpi_calendars_holidays`
                WHERE `calendars_id` = '$oldid'";

      foreach ($DB->request($query) as $data) {
         $ch                   = new self();
         unset($data['id']);
         $data['calendars_id'] = $newid;
         $data['_no_history']  = true;

         $ch->add($data);
      }
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if (!$withtemplate) {
         switch ($item->getType()) {
            case 'Calendar' :
               if ($_SESSION['glpishow_count_on_tabs']) {
                  return self::createTabEntry(_n('Close time','Close times', 2),
                                              countElementsInTable($this->getTable(),
                                                                   "calendars_id
                                                                        = '".$item->getID()."'"));
               }
               return _n('Close time','Close times', 2);
         }
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      if ($item->getType()=='Calendar') {
         self::showForCalendar($item);
      }
      return true;
   }
}
?>