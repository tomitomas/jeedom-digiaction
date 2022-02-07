<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

try {
  require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
  include_file('core', 'authentification', 'php');

  // if (!isConnect('admin')) {
  //     throw new Exception(__('401 - Accès non autorisé', __FILE__));
  // }

  /* Fonction permettant l'envoi de l'entête 'Content-Type: application/json'
    En V3 : indiquer l'argument 'true' pour contrôler le token d'accès Jeedom
    En V4 : autoriser l'exécution d'une méthode 'action' en GET en indiquant le(s) nom(s) de(s) action(s) dans un tableau en argument
  */
  //ajax::init();
  switch (init('action')) {
    case 'updateCmdConfig':
      /*
      when renaming a mode it will delete the previous command and create a new one
      this function is used to update the name & logicalId of the existing cmd instead of deleting it
      */
      digiaction::addLogTemplate('RENAME CMD NAME');
      log::add('digiaction', 'debug', '│ Check for eqId "' . init('eqId') . '"');
      $eqLogic = digiaction::byId(init('eqId'));
      if (!is_object($eqLogic)) {
        log::add('digiaction', 'debug', '│ No equipement found with id "' . init('eqId') . '"');
        ajax::success("ko");
        digiaction::addLogTemplate();
        break;
      }

      $currentMode = $eqLogic->getCmd(null, 'currentMode');
      $currentModeValue = is_object($currentMode) ? $currentMode->execCmd() : null;
      foreach (init('arrayRename') as $item) {
        $old = $item['old'];
        $new = $item['new'];

        $cmd = $eqLogic->getCmd(null, $old);
        if (!is_object($cmd)) {
          log::add('digiaction', 'debug', '│ No Cmd found with name "' . $old . '"');
          ajax::success("ko");
          digiaction::addLogTemplate();
          break;
        }
        $cmd->setName($new);
        $cmd->setLogicalId($new);

        if ($currentModeValue == $old) {
          log::add('digiaction', 'debug', '│ currentMode <' . $currentModeValue . '> is the one renamed <' . $old . '>  -- updating current mode');
          $eqLogic->checkAndUpdateCmd('currentMode', $new);
        }

        log::add('digiaction', 'debug', '│ Cmd renamed done from "' . $old . '" to "' . $new . '"');
        $cmd->save();
      }
      digiaction::addLogTemplate();
      ajax::success();
      break;

    case 'getAvailableMode':
      $eqLogic = digiaction::byId(init('eqId'));
      $modes = $eqLogic->getAvailableModeHTML();
      ajax::success(json_encode($modes));
      break;

    case 'verifUser':
      $eqLogic = digiaction::byId(init('eqId'));
      $eqLogic->checkAndUpdateCmd('digimessage', '');
      list($verif, $userName, $isPanic) = $eqLogic->verifCodeUser(init('userCode'), init('cmdId'));
      if (!$verif) {
        $txtKO = $eqLogic->getConfiguration('textCodeKO', 'Code inconnu');
        $eqLogic->checkAndUpdateCmd('digimessage', $txtKO);
        $data = "ko";
      } else {
        $digiCmd = digiactionCmd::byId(init('cmdId'));
        digiaction::addLogTemplate('PRE-CHECK CONTROLS');
        $verifPreCheck = $eqLogic->doPreCheck($digiCmd->getLogicalId());

        if (!$verifPreCheck) {
          $eqLogic->checkAndUpdateCmd('digimessage', 'Contrôle(s) en échec');
          digiaction::addLogTemplate('PRE-CHECK FAILED - PROCEED WITH ERROR ACTION', true);
          $eqLogic->doAction($digiCmd->getLogicalId(), 'preCheckActionError');
          $data = "ko";
        } else {
          $data = "ok";
        }
        digiaction::addLogTemplate();
      }
      ajax::success($data);
      break;

    case 'verifUserAndDoAction':
      $eqLogic = digiaction::byId(init('eqId'));
      $eqLogic->checkAndUpdateCmd('digimessage', '');
      list($verif, $userName, $isPanic) = $eqLogic->verifCodeUser(init('userCode'), init('cmdId'));
      if (!$verif) {
        $txtKO = $eqLogic->getConfiguration('textCodeKO', 'Code inconnu');
        $eqLogic->checkAndUpdateCmd('digimessage', $txtKO);
        $data = "code inconnu";
      } else {
        $digiCmd = digiactionCmd::byId(init('cmdId'));
        $digiCmd->execute(array('userName' => $userName, 'panic' => $isPanic));
        $data = "";
      }
      ajax::success($data);

      break;
  }


  throw new Exception(__('Aucune méthode correspondante à : ', __FILE__) . init('action'));
  /*     * *********Catch exeption*************** */
} catch (Exception $e) {
  ajax::error(displayException($e), $e->getCode());
}
