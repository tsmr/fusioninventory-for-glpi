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
// Original Author of file:
// Purpose of file:
// ----------------------------------------------------------------------

define('GLPI_ROOT', '..');
include (GLPI_ROOT . "/inc/includes.php");

Session::checkSeveralRightsOr(array('knowbase' => 'r',
                                    'faq'      => 'r'));

if (isset($_GET["id"])) {
   Html::redirect($CFG_GLPI["root_doc"]."/front/knowbaseitem.form.php?id=".$_GET["id"]);
}

Html::header(KnowbaseItem::getTypeName(1), $_SERVER['PHP_SELF'], "utils", "knowbase");

// Search a solution
if (!isset($_REQUEST["contains"])
    && isset($_REQUEST["item_itemtype"])
    && isset($_REQUEST["item_items_id"])) {

   if ($item = getItemForItemtype($_REQUEST["item_itemtype"])) {
      if ($item->getFromDB($_REQUEST["item_items_id"])) {
         $_REQUEST["contains"] = addslashes($item->getField('name'));
      }
   }
}

// Manage forcetab : non standard system (file name <> class name)
if (isset($_REQUEST['forcetab'])) {
   Session::setActiveTab('Knowbase', $_REQUEST['forcetab']);
   unset($_REQUEST['forcetab']);
}

$kb = new Knowbase();
$kb->show(Toolbox::addslashes_deep($_REQUEST));


Html::footer();
?>