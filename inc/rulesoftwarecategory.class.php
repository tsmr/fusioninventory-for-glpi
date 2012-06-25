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
// Original Author of file: Walid Nouh
// Purpose of file:
// ----------------------------------------------------------------------
if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}


/**
* Rule class store all information about a GLPI rule :
*   - description
*   - criterias
*   - actions
*
**/
class RuleSoftwareCategory extends Rule {

   // From Rule
   public $right    = 'rule_softwarecategories';
   public $can_sort = true;


   function getTitle() {
      return __('Rules for assigning a category to software');
   }


   /**
    * @see inc/Rule::maxActionsCount()
   **/
   function maxActionsCount() {
      return 1;
   }


   function getCriterias() {

      $criterias                          = array();

      $criterias['name']['field']         = 'name';
      $criterias['name']['name']          = _n('Software', 'Software', 2);
      $criterias['name']['table']         = 'glpi_softwares';

      $criterias['manufacturer']['field'] = 'name';
      $criterias['manufacturer']['name']  = __('Publisher');
      $criterias['manufacturer']['table'] = 'glpi_manufacturers';

      $criterias['comment']['field']      = 'comment';
      $criterias['comment']['name']       = __('Comments');
      $criterias['comment']['table']      = 'glpi_softwares';
      return $criterias;
   }


   function getActions() {

      $actions                                   = array();

      $actions['softwarecategories_id']['name']  = __('Category');
      $actions['softwarecategories_id']['type']  = 'dropdown';
      $actions['softwarecategories_id']['table'] = 'glpi_softwarecategories';
      return $actions;
   }

}
?>