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

define('GLPI_ROOT', '../../..');
include (GLPI_ROOT . "/inc/includes.php");

Session::checkLoginUser();

if (isset($_GET["popup"])) {
   $_SESSION["glpipopup"]["name"] = $_GET["popup"];
}

if (isset($_SESSION["glpipopup"]["name"])) {
   switch ($_SESSION["glpipopup"]["name"]) {
      case "test_rule" :
         Html::popHeader(__('Test'),$_SERVER['PHP_SELF']);
         include "../../../front/rule.test.php";
         break;

      case "test_all_rules" :
         Html::popHeader(__('Test rules engine'),$_SERVER['PHP_SELF']);
         include "../../../front/rulesengine.test.php";
         break;

      case "show_cache" :
         Html::popHeader(__('Cache informations', 'fusioninventory'),$_SERVER['PHP_SELF']);
         include "../../../front/rule.cache.php";
         break;
      
      case "pluginfusioninventory_networkport_display_options" :
         Html::popHeader(__('Network ports display options', 'fusioninventory'), $_SERVER['PHP_SELF']);
         include "networkport.display.php";
         break;
      
   }
   echo "<div class='center'><br><a href='javascript:window.close()'>".__('Back')."</a>";
   echo "</div>";
   Html::popFooter();
}

?>