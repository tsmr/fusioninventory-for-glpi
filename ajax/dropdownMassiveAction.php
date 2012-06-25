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

define('GLPI_ROOT','..');
include (GLPI_ROOT."/inc/includes.php");

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

if (isset($_POST["action"])
    && isset($_POST["itemtype"]) && !empty($_POST["itemtype"])) {

   if (!($item = getItemForItemtype($_POST['itemtype']))) {
      exit();
   }

   if (isset($_POST['sub_type'])) {
      if (!($item = getItemForItemtype($_POST['sub_type']))) {
         exit();
      }
   }

   if (in_array($_POST["itemtype"],$CFG_GLPI["infocom_types"])) {
      Session::checkSeveralRightsOr(array($_POST["itemtype"] => "w",
                                          "infocom"          => "w"));
   } else {
      $item->checkGlobal("w");
   }

   echo "<input type='hidden' name='action' value='".$_POST["action"]."'>";
   echo "<input type='hidden' name='itemtype' value='".$_POST["itemtype"]."'>";
   echo '&nbsp;';

   switch($_POST["action"]) {
      case "activate_rule" :
         Dropdown::showYesNo("activate_rule");
         echo "<br><br><input type='submit' name='massiveaction' class='submit' value='".
                        _sx('button', 'Activate')."'>\n";
         break;

      case 'move_under' :
         _e('As child of');
         Dropdown::show($_POST['itemtype'], array('name'     => 'parent',
                                                  'comments' => 0));
         echo "<br><br><input type='submit' name='massiveaction' class='submit' value='".
                        _sx('button', 'Move')."'>\n";
         break;

      case 'merge' :
         echo "&nbsp;".$_SESSION['glpiactive_entity_shortname'];
         echo "<br><br><input type='submit' name='massiveaction' class='submit' value='".
                        _sx('button', 'Merge')."'>\n";
         break;

      case "move_rule" :
         echo "<select name='move_type'>";
         echo "<option value='after' selected>".__('After')."</option>";
         echo "<option value='before'>".__('Before')."</option>";
         echo "</select>&nbsp;";

         if (isset($_POST['entity_restrict'])) {
            $condition = $_POST['entity_restrict'];
         } else {
            $condition = "";
         }
         Rule::dropdown(array('sub_type'        => $_POST['sub_type'],
                              'name'            => "ranking",
                              'entity_restrict' => $condition));
         echo "<br><br><input type='submit' name='massiveaction' class='submit' value='".
                        _sx('button', 'Move')."'>\n";
         break;

      case "add_followup" :
         TicketFollowup::showFormMassiveAction();
         break;

      case "add_task" :
         $tasktype = $_POST['itemtype']."Task";
         if ($ttype = getItemForItemtype($tasktype)) {
            $ttype->showFormMassiveAction();
         }
         break;

      case "add_actor" :
         $types            = array(0                           => Dropdown::EMPTY_VALUE,
                                   CommonITILObject::REQUESTER => __('Requester'),
                                   CommonITILObject::OBSERVER  => __('Watcher'),
                                   CommonITILObject::ASSIGN    => __('Assigned to'));
         $rand             = Dropdown::showFromArray('actortype', $types);

         $paramsmassaction = array('actortype' => '__VALUE__');

         Ajax::updateItemOnSelectEvent("dropdown_actortype$rand", "show_massiveaction_field",
                                       $CFG_GLPI["root_doc"].
                                          "/ajax/dropdownMassiveActionAddActor.php",
                                       $paramsmassaction);
         echo "<span id='show_massiveaction_field'>&nbsp;</span>\n";

         break;

      case "link_ticket" :
         $rand = Ticket_Ticket::dropdownLinks('link');
         printf(__('%1$s: %2$s'), __('Ticket'), __('ID'));
         echo "&nbsp;<input type='text' name='tickets_id_1' value='' size='10'>\n";
         echo "<br><br><input type='submit' name='massiveaction' class='submit' value='".
                        __s('Post')."'>";

         break;

      case "submit_validation" :
         TicketValidation::showFormMassiveAction();
         break;

      case "change_authtype" :
         $rand             = Auth::dropdown(array('name' => 'authtype'));
         $paramsmassaction = array('authtype' => '__VALUE__');

         Ajax::updateItemOnSelectEvent("dropdown_authtype$rand", "show_massiveaction_field",
                                       $CFG_GLPI["root_doc"].
                                          "/ajax/dropdownMassiveActionAuthMethods.php",
                                       $paramsmassaction);
         echo "<span id='show_massiveaction_field'>";
         echo "<br><br><input type='submit' name='massiveaction' class='submit' value='".
                        __s('Post')."'></span>\n";
         break;

      case "compute_software_category" :
      case "replay_dictionnary" :
      case "force_user_ldap_update" :
      case "delete" :
      case "purge" :
      case "restore" :
      case "add_transfer_list" :
      case "activate_infocoms" :
      case "delete_email" :
      case 'reset' :
         echo "<input type='submit' name='massiveaction' class='submit' value='".__s('Post')."'>\n";
         break;

      case "install" :
         Software::dropdownSoftwareToInstall("softwareversions_id",
                                             $_SESSION["glpiactive_entity"], 1);
         echo "<br><br><input type='submit' name='massiveaction' class='submit' value='".
                        __s('Install')."'>";
         break;

      case "connect" :
         Computer_Item::dropdownConnect('Computer', $_POST["itemtype"], "connect_item");
         echo "<br><br><input type='submit' name='massiveaction' class='submit' value='".
                        __s('Connect')."'>";
         break;

      case "connect_to_computer" :
         Dropdown::showAllItems("connect_item", 0, 0, $_SESSION["glpiactive_entity"],
                                array('Monitor', 'Peripheral', 'Phone',  'Printer'),
                                true, true);
         echo "<br><br><input type='submit' name='massiveaction' class='submit' value='".
                        __s('Connect')."'>";
         break;

      case "disconnect" :
         echo "<input type='submit' name='massiveaction' class='submit' value='".
                __s('Disconnect')."'>";
         break;

      case "add_user_group" :
      case "add_supervisor_group" :
      case "add_delegatee_group" :
         User::dropdown(array('right'  => "all"));
         echo "<br><br><input type='submit' name='massiveaction' class='submit' value='".
                        _sx('button', 'Add')."'>";
         break;

         
      case "add_group" :
         Group::dropdown(array('condition' => '`is_usergroup`'));
         echo "<br><br><input type='submit' name='massiveaction' class='submit' value='".
                        _sx('button', 'Add')."'>";
         break;

      case "add_userprofile" :
         Entity::dropdown(array('entity' => $_SESSION['glpiactiveentities']));
         echo ".&nbsp;"._n('Profile', 'Profiles', 1)."&nbsp;";
         Profile::dropdownUnder();
         echo ".&nbsp;".__('Recursive')."&nbsp;";
         Dropdown::showYesNo("is_recursive", 0);
         echo "<br><br><input type='submit' name='massiveaction' class='submit' value='".
                        _sx('button', 'Add')."'>";
         break;

      case "transform_to" :
         Dropdown::showItemTypes('transform_to', NetworkPort::getNetworkPortInstantiations(),
                                 array('value' => 'NetworkPortEthernet'));

         echo "<br><br><input type='submit' name='massiveaction' class='submit' value='".
                        _sx('button', 'Save')."'>";
         break;

      case "add_document" :
         Document::dropdown(array('name' => 'documents_id'));
         echo "<br><br><input type='submit' name='massiveaction' class='submit' value='".
                        _sx('button', 'Add')."'>";
         break;

      case "remove_document" :
         Document::dropdown(array('name' => 'documents_id'));
         echo "<br><br><input type='submit' name='massiveaction' class='submit' value='".
                        _sx('button', 'Delete')."'>";
         break;

      case "add_document_item" :
            Dropdown::showAllItems("items_id", 0, 0, 1,
                                   $CFG_GLPI["document_types"], false, true);
         echo "<br><br><input type='submit' name='massiveaction' class='submit' value='".
                        _sx('button', 'Add')."'>";
         break;

      case "remove_document_item" :
            Dropdown::showAllItems("items_id", 0, 0, 1,
                                   $CFG_GLPI["document_types"], false, true);
         echo "<br><br><input type='submit' name='massiveaction' class='submit' value='".
                        _sx('button', 'Delete')."'>";
         break;


      case "add_contract" :
         Contract::dropdown(array('name' => "contracts_id"));
         echo "<br><br><input type='submit' name='massiveaction' class='submit' value='".
                        _sx('button', 'Add')."'>";
         break;

      case "remove_contract" :
         Contract::dropdown(array('name' => "contracts_id"));
         echo "<br><br><input type='submit' name='massiveaction' class='submit' value='".
                        _sx('button', 'Delete')."'>";
         break;

      case "add_contract_item" :
            Dropdown::showAllItems("items_id", 0, 0, 1,
                                   $CFG_GLPI["contract_types"], false, true);
         echo "<br><br><input type='submit' name='massiveaction' class='submit' value='".
                        _sx('button', 'Add')."'>";
         break;

      case "remove_contract_item" :
            Dropdown::showAllItems("items_id", 0, 0, 1,
                                   $CFG_GLPI["contract_types"], false, true);
         echo "<br><br><input type='submit' name='massiveaction' class='submit' value='".
                        _sx('button', 'Delete')."'>";
         break;

      case "add_contact" :
         Contact::dropdown(array('name' => "contacts_id"));
         echo "<br><br><input type='submit' name='massiveaction' class='submit' value='".
                        _sx('button', 'Add')."'>";
         break;

      case "add_enterprise" :
         Supplier::dropdown(array('name' => "suppliers_id"));
         echo "<br><br><input type='submit' name='massiveaction' class='submit' value='".
                        _sx('button', 'Add')."'>";
         break;

      case "import_email" :
         Entity::dropdown();
         echo "<br><br><input type='submit' name='massiveaction' class='submit' value='".
                        _sx('button', 'Import')."'>";
         break;

      case "duplicate" :
         if ($item->isEntityAssign()) {
            Entity::dropdown();
         }
         echo "<br><br><input type='submit' name='massiveaction' class='submit' value='".
                        _sx('button', 'Duplicate')."'>";
         break;

      case "update" :
         $first_group    = true;
         $newgroup       = "";
         $items_in_group = 0;
         $show_all       = true;
         $show_infocoms  = true;

         $ic = new Infocom();
         if (in_array($_POST["itemtype"],$CFG_GLPI["infocom_types"])
             && (!$item->canUpdate() || !$ic->canUpdate())) {

            $show_all      = false;
            $show_infocoms = $ic->canUpdate();
         }
         $searchopt = Search::getCleanedOptions($_POST["itemtype"], 'w');

         echo "<select name='id_field' id='massiveaction_field'>";
         echo "<option value='0' selected>".Dropdown::EMPTY_VALUE."</option>";

         foreach ($searchopt as $key => $val) {
            if (!is_array($val)) {
               if (!empty($newgroup)
                   && ($items_in_group > 0)) {
                  echo $newgroup;
                  $first_group = false;
               }
               $items_in_group = 0;
               $newgroup       = "";
               if (!$first_group) {
                  $newgroup .= "</optgroup>";
               }
               $newgroup .= "<optgroup label=\"$val\">";

            } else {
               // No id and no entities_id massive action and no first item
               if ($val["field"]!='id'
                   && ($key != 1)
                   // Permit entities_id is explicitly activate
                   && (($val["linkfield"] != 'entities_id')
                       || (isset($val['massiveaction']) && $val['massiveaction']))) {

                  if (!isset($val['massiveaction']) || $val['massiveaction']) {

                     if ($show_all) {
                        $newgroup .= "<option value='$key'>".$val["name"]."</option>";
                        $items_in_group++;

                     } else {
                        // Do not show infocom items
                        if (($show_infocoms && Search::isInfocomOption($_POST["itemtype"],$key))
                            || (!$show_infocoms && !Search::isInfocomOption($_POST["itemtype"],
                                                                            $key))) {

                           $newgroup .= "<option value='$key'>".$val["name"]."</option>";
                           $items_in_group++;
                        }
                     }
                  }
               }
            }
         }

         if (!empty($newgroup)
             && ($items_in_group > 0)) {
            echo $newgroup;
         }
         if (!$first_group) {
            echo "</optgroup>";
         }
         echo "</select>";

         $paramsmassaction = array('id_field' => '__VALUE__',
                                   'itemtype' => $_POST["itemtype"]);

         foreach ($_POST as $key => $val) {
            if (preg_match("/extra_/",$key,$regs)) {
               $paramsmassaction[$key] = $val;
            }
         }
         Ajax::updateItemOnSelectEvent("massiveaction_field", "show_massiveaction_field",
                                       $CFG_GLPI["root_doc"]."/ajax/dropdownMassiveActionField.php",
                                       $paramsmassaction);

         echo "<br><br><span id='show_massiveaction_field'>&nbsp;</span>\n";
         break;

      default :
         // Plugin specific actions
         $split = explode('_',$_POST["action"]);

         if (($split[0] == 'plugin') && isset($split[1])) {
            // Normalized name plugin_name_action
            // Allow hook from any plugin on any (core or plugin) type
            Plugin::doOneHook($split[1], 'MassiveActionsDisplay',
                              array('itemtype' => $_POST["itemtype"],
                                    'action'   => $_POST["action"]));

         } else if ($plug=isPluginItemType($_POST["itemtype"])) {
            // non-normalized name
            // hook from the plugin defining the type
            Plugin::doOneHook($plug['plugin'], 'MassiveActionsDisplay', $_POST["itemtype"],
                              $_POST["action"]);
         }
   }
}
?>