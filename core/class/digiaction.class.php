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

/* * ***************************Includes********************************* */
require_once __DIR__  . '/../../../../core/php/core.inc.php';

class digiaction extends eqLogic {
   /*     * *************************Attributs****************************** */

   /*
   * Permet de définir les possibilités de personnalisation du widget (en cas d'utilisation de la fonction 'toHtml' par exemple)
   * Tableau multidimensionnel - exemple: array('custom' => true, 'custom::layout' => false)
	public static $_widgetPossibility = array();
   */

   /*     * ***********************Methode static*************************** */

   /*
     * Fonction exécutée automatiquement toutes les minutes par Jeedom
      public static function cron() {
      }
     */

   /*
     * Fonction exécutée automatiquement toutes les 5 minutes par Jeedom
      public static function cron5() {
      }
     */

   /*
     * Fonction exécutée automatiquement toutes les 10 minutes par Jeedom
      public static function cron10() {
      }
     */

   /*
     * Fonction exécutée automatiquement toutes les 15 minutes par Jeedom
      public static function cron15() {
      }
     */

   /*
     * Fonction exécutée automatiquement toutes les 30 minutes par Jeedom
      public static function cron30() {
      }
     */

   /*
     * Fonction exécutée automatiquement toutes les heures par Jeedom
      public static function cronHourly() {
      }
     */

   /*
     * Fonction exécutée automatiquement tous les jours par Jeedom
      public static function cronDaily() {
      }
     */



   /*     * *********************Méthodes d'instance************************* */

   // Fonction exécutée automatiquement après la sauvegarde (création ou mise à jour) de l'équipement 
   public function postSave() {

      $currentMode = $this->getCmd(null, 'currentMode');
      if (!is_object($currentMode)) {
         $currentMode = new digiactionCmd();
      }
      $currentMode->setName(__('Mode', __FILE__));
      $currentMode->setEqLogic_id($this->id);
      $currentMode->setLogicalId('currentMode');
      $currentMode->setType('info');
      $currentMode->setOrder(1);
      $currentMode->setSubType('string');
      $currentMode->save();

      $currentMode = $this->getCmd(null, 'digimessage');
      if (!is_object($currentMode)) {
         $currentMode = new digiactionCmd();
      }
      $currentMode->setName(__('Message', __FILE__));
      $currentMode->setEqLogic_id($this->id);
      $currentMode->setLogicalId('digimessage');
      $currentMode->setType('info');
      $currentMode->setOrder(2);
      $currentMode->setSubType('string');
      $currentMode->save();

      $updateMsg = $this->getCmd(null, 'updatemessage');
      if (!is_object($updateMsg)) {
         $updateMsg = new digiactionCmd();
      }
      $updateMsg->setName(__('MaJ Message', __FILE__));
      $updateMsg->setEqLogic_id($this->id);
      $updateMsg->setLogicalId('updatemessage');
      $updateMsg->setType('action');
      $updateMsg->setOrder(3);
      $updateMsg->setSubType('message');
      $updateMsg->save();

      $existing_mode = array();
      if (is_array($this->getConfiguration('modes'))) {
         $i = 3;
         foreach ($this->getConfiguration('modes') as $key => $value) {
            $cmd = $this->getCmd(null, $value['name']);
            $existing_mode[] = $value['name'];
            if (!is_object($cmd)) {
               $cmd = new digiactionCmd();
            }
            $cmd->setName($value['name']);
            $cmd->setEqLogic_id($this->id);
            $cmd->setType('action');
            $cmd->setSubType('other');
            $i++;
            $cmd->setOrder($i);
            $cmd->setLogicalId($value['name']);
            if (isset($value['icon'])) {
               $cmd->setDisplay('icon', $value['icon']);
            }
            $cmd->save();
         }
      }

      foreach ($this->getCmd() as $cmd) {
         if ($cmd->getType() == 'action' && !in_array($cmd->getLogicalId(), $existing_mode) && $cmd->getLogicalId() != 'updatemessage') {
            $cmd->remove();
         }
      }

      $this->refreshWidget();
   }




   public function doPreCheck($_mode) {
      if (!is_array($this->getConfiguration('modes'))) {
         return;
      }

      $checkResult = null;
      foreach ($this->getConfiguration('modes') as $key => $value) {
         if ($value['name'] != $_mode) {
            continue;
         }
         foreach ($value['preCheck'] as $action) {
            try {
               if (isset($action['options']) && $action['options']['enable'] == 1) {
                  $check = $action['cmdInfo'];
                  $checkHuman = jeedom::toHumanReadable($check);
                  $tmpCheck = jeedom::evaluateExpression($check);

                  if ("$tmpCheck" === "$check") {
                     throw new Exception('Il doit y avoir un souci, car le résultat est le même que l\'expression');
                  }

                  if ($tmpCheck) {
                     log::add('digiaction', 'debug', '│ TRUE - expression "' . $checkHuman . '"');
                     $checkResult = true;
                  } else {
                     log::add('digiaction', 'debug', '│ FALSE - expression "' . $checkHuman . '"');
                     $checkResult = false;
                  }
                  $checkResult = $checkResult && $tmpCheck;
               } else {
                  log::add('digiaction', 'debug', '│ DISABLE - option is not enable');
               }
            } catch (Exception $e) {
               log::add('digiaction', 'error', __('Erreur lors de l\'éxecution de ', __FILE__) . $action['cmdInfo'] . __('. Détails : ', __FILE__) . $e->getMessage());
               $checkResult = false;
               break;
            }
         }
         break;
      }

      if (is_null($checkResult)) {
         log::add('digiaction', 'debug', '│ no Pré-Check setup');
         $checkResult = true;
      }

      return $checkResult;
   }


   public function doAction($_mode, $_type) {
      if (!is_array($this->getConfiguration('modes'))) {
         return;
      }

      $checkAction = null;
      foreach ($this->getConfiguration('modes') as $key => $value) {
         if ($value['name'] != $_mode) {
            continue;
         }
         log::add('digiaction', 'debug', '│ *** action(s) ' . $_type . ' will be executed ***');
         foreach ($value[$_type] as $action) {
            try {
               $options = array();
               if (isset($action['options'])) {
                  $options = $action['options'];
               }
               $tmpAction = scenarioExpression::createAndExec('action', $action['cmd'], $options);

               log::add('digiaction', 'debug', '│ action done : ' . $tmpAction);

               $checkAction = true;
            } catch (Exception $e) {
               log::add('digiaction', 'error', __('Erreur lors de l\'éxecution de ', __FILE__) . $action['cmd'] . __('. Détails : ', __FILE__) . $e->getMessage());
               $checkAction = false;
               break;
            }
         }
         break;
      }

      if (is_null($checkAction)) {
         log::add('digiaction', 'debug', '│ no action setup for ' . $_type);
         $checkAction = true;
      }

      return $checkAction;
   }

   /*
     * Non obligatoire : permet de modifier l'affichage du widget (également utilisable par les commandes)
      public function toHtml($_version = 'dashboard') {

      }
     */

   public function toHtml($_version = 'dashboard') {

      $replace = $this->preToHtml($_version);
      if (!is_array($replace)) {
         return $replace;
      }
      // $this->emptyCacheWidget(); //vide le cache. Pratique pour le développement

      $version = jeedom::versionAlias($_version);

      foreach (($this->getCmd('info')) as $cmd) {
         $replace['#' . $cmd->getLogicalId() . '_id#'] = $cmd->getId();
         $replace['#' . $cmd->getLogicalId() . '#'] = $cmd->execCmd();
      }

      $replace['#eqLogic_id#'] = $this->getId();

      $replace['#title#'] = $this->getName();

      $countdown = $this->getConfiguration('countdown', 10);
      $replace['#countdown#'] = empty($countdown) ? 10000 : ($countdown == -1 ? -1 : intval($countdown) * 1000);

      $replace['#modeAvailable#'] = $this->getAvailableModeHTML();
      $replace['#currentMode#'] = $this->getCmd(null, 'currentMode')->execCmd();
      // $replace['#message#'] = $this->getCmd(null, 'digimessage')->execCmd();

      return $this->postToHtml($_version, template_replace($replace, getTemplate('core', $version, 'digiaction', 'digiaction')));
   }


   /*
     * * * * * * * * * * * * * * * * * *  
     * CUSTOM FUNCTION
     * * * * * * * * * * * * * * * * * *  
     */

   public function getAvailableModeFromCurrent($currentMode) {

      $available = array();
      foreach ($this->getConfiguration('modes') as $item => $conf) {
         //check on empty for really first usage when the currentMode is not yet set 
         if (!empty($currentMode) && $conf['name'] != $currentMode) {
            continue;
         }
         foreach ($conf['availableMode'] as $availableMode) {
            log::add('digiaction', 'debug', '│ checking available mode settings from current mode : ' . $currentMode);
            foreach ($availableMode as $key => $value) {
               log::add('digiaction', 'debug', '│        ' . $key . ' : ' . $value);
               if ($value == 1) array_push($available, $key);
            }
         }
         if (!empty($currentMode)) break;
      }
      return $available;
   }

   public function getModeDetails($_mode = null) {
      $currentMode = $this->getCmd(null, 'currentMode')->execCmd();

      if (is_null($_mode)) {
         log::add('digiaction', 'debug', '│ current mode : ' . $currentMode);
         $modeArray = $this->getAvailableModeFromCurrent($currentMode);
      } else {
         $modeArray = array($_mode);
      }

      $detailedList = array();
      if (count($modeArray) > 0) {
         log::add('digiaction', 'debug', '│ retrieved available mode lists : ' . implode('//', $modeArray));

         foreach ($this->getConfiguration('modes') as $mode) {
            if (!in_array($mode['name'], $modeArray)) {
               continue;
            }
            log::add('digiaction', 'debug', '│ mode details retrieved for ' . $mode['name']);
            array_push($detailedList, $mode);
         }
      } else {
         // array_push($detailedList, $currentMode); 
         log::add('digiaction', 'error', '│ no available mode setup for ' . $currentMode);
      }
      return $detailedList;
   }

   public function getAvailableModeHTML() {
      self::addLogTemplate('CREATE HTML CODE FOR AVAILABLE MODES');
      $modes = $this->getModeDetails();
      $eqId = $this->getId();

      $result = '';
      foreach ($modes as $mode) {
         $tmpResult = '<div>';
         $cmd = $this->getCmd('action',  $mode['name']);
         $cmdId = $cmd->getId();

         $digi = ($mode['confirmDigicode'] == 1) ? 'digiactionEnterPin' : '';
         if (!empty($mode['icon'])) {
            $tmpResult .= '<li class="digiActionMode digiActionNoBg ' . $digi . '" digi-action="' . $mode['name'] . '" digi-cmdId="' . $cmdId . '" digi-timer="' . $mode['timer'] . '" title="mode ' . $mode['name'] . '">';
            $tmpResult .= str_replace("img-responsive", "", $mode['icon']);
         } else {
            $tmpResult .= '<li class="digiActionMode digiActionText ' . $digi . '" digi-action="' . $mode['name'] . '" digi-cmdId="' . $cmdId . '" digi-timer="' . $mode['timer'] . '" >';
            $tmpResult .= $mode['name'];
         }
         $tmpResult .= '</li></div>';
         log::add('digiaction', 'debug', '│ mode added : ' . $tmpResult);
         $result .= $tmpResult;
      }

      log::add('digiaction', 'debug', '│ all HTML modes résult : ' . $result);
      self::addLogTemplate();

      return $result;
   }

   public function verifCodeUser($userCode, $nextCmdId) {
      self::addLogTemplate('CHECK USER CODE');

      try {
         // check if the new mode requires a password
         $checkPwd = $this->hasPasswordRequired($nextCmdId);

         if (!$checkPwd) {
            self::addLogTemplate();
            return true;
         }

         // if password is required, then check the password send to see if it matches on saved password.
         $check = 0;
         log::add('digiaction', 'debug', '│ checking password : >' . $userCode . '<');
         foreach ($this->getConfiguration('users') as $user) {
            //if ( """$user['userCode']""" != "$userCode" ){
            // log::add('digiaction', 'debug', '│ info user : '. json_encode($user) ) ;

            if (strcmp($user['userCode'], $userCode) !== 0) {
               continue;
            }

            $now = time();

            // $beginOfDay = strtotime("today", $now);
            // $endOfDay   = strtotime("tomorrow", $beginOfDay) - 1;

            log::add('digiaction', 'debug', '│ time = ' . $now);
            log::add('digiaction', 'debug', '│ startFrom = ' . strtotime($user['startFrom']));
            log::add('digiaction', 'debug', '│ endTo = ' . strtotime($user['endTo']));
            self::addLogTemplate('check start date', true);
            $isset = empty($user['startFrom']) ? 'true' : 'false';
            log::add('digiaction', 'debug', '│ empty : ' . $isset);
            if ($isset == 'false') {
               $checkDate = self::checkIsAValidDate($user['startFrom']) ? 'true' : 'false';
               log::add('digiaction', 'debug', '│ checkIsAValidDate : ' . $checkDate);
               $comp = (strtotime($user['startFrom']) < $now) ? 'true' : 'false';
               log::add('digiaction', 'debug', '│ before now : ' . $comp);
            }

            self::addLogTemplate('check end date', true);
            $isset = empty($user['endTo']) ? 'true' : 'false';
            log::add('digiaction', 'debug', '│ empty : ' . $isset);
            if ($isset == 'false') {
               $checkDate = self::checkIsAValidDate($user['endTo']) ? 'true' : 'false';
               log::add('digiaction', 'debug', '│ checkIsAValidDate : ' . $checkDate);
               $comp = (strtotime($user['endTo']) > $now) ? 'true' : 'false';
               log::add('digiaction', 'debug', '│ after now : ' . $comp);
            }
            self::addLogTemplate(null, true);


            if (!empty($user['startFrom']) && self::checkIsAValidDate($user['startFrom']) && strtotime($user['startFrom']) > $now) {
               log::add('digiaction', 'debug', '│ date restriction -- password OK for user [' . $user['name'] . '] but start date in the futur');
               $check = 2;
               break;
            }

            if (!empty($user['endTo']) && self::checkIsAValidDate($user['endTo']) && strtotime($user['endTo']) < $now) {
               log::add('digiaction', 'debug', '│ date restriction -- password OK for user [' . $user['name'] . '] but end date in the past');
               $check = 2;
               break;
            }

            log::add('digiaction', 'debug', '│ password OK for user : ' . $user['name']);
            $check = 1;
            break;
         }

         if ($check == 0) log::add('digiaction', 'debug', '│ no user found with password "' . $userCode . '"');
      } catch (Exception $e) {
         log::add('digiaction', 'error', '│ Could not get cmd details => ' . $e->getMessage());
         $check = false;
      }

      self::addLogTemplate();
      return ($check == 1) ? true : false;
   }

   public static function checkIsAValidDate($myDateString) {
      return (bool)strtotime($myDateString);
   }

   public function hasPasswordRequired($nextCmdId) {
      $cmd = digiactionCmd::byId($nextCmdId);
      if (!is_object($cmd)) {
         throw new Exception('Unexisting command ID for ' . $nextCmdId);
      }

      $cmdName = $cmd->getLogicalId();
      log::add('digiaction', 'debug', '│ found the cmd \'' . $cmdName . '\' for cmdId ' . $nextCmdId);

      $modes = $this->getModeDetails($cmdName);

      foreach ($modes as $mode) {
         if ($mode['name'] != $cmdName) {
            continue;
         }
         if ($mode['confirmDigicode'] == 1) {
            log::add('digiaction', 'debug', '│ password setup - check required');
            return true;
         } else {
            log::add('digiaction', 'debug', '│ NO password needed');
            return false;
         }
      }

      return false;
   }

   public static function addLogTemplate($msg = null, $inter = false) {

      if (!is_null($msg)) {
         $first = $inter ? '├' : '┌';
         log::add('digiaction', 'debug', $first . '────────────────────────────────────');
         log::add('digiaction', 'debug', '│    ' . $msg);
         log::add('digiaction', 'debug', '├────────────────────────────────────');
      } elseif ($inter) {
         log::add('digiaction', 'debug', '├────────────────────────────────────');
      } else {
         log::add('digiaction', 'debug', '└────────────────────────────────────');
      }
   }

   /*
     * Non obligatoire : permet de déclencher une action après modification de variable de configuration
    public static function postConfig_<Variable>() {
    }
     */

   /*
     * Non obligatoire : permet de déclencher une action avant modification de variable de configuration
    public static function preConfig_<Variable>() {
    }
     */

   /*     * **********************Getteur Setteur*************************** */
}

class digiactionCmd extends cmd {
   /*     * *************************Attributs****************************** */

   /*
      public static $_widgetPossibility = array();
    */

   /*     * ***********************Methode static*************************** */


   /*     * *********************Methode d'instance************************* */

   /*
     * Non obligatoire permet de demander de ne pas supprimer les commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
     */
   public function dontRemoveCmd() {
      return true;
   }


   // Exécution d'une commande  
   public function execute($_options = array()) {


      switch ($this->getLogicalId()) {
         case 'updatemessage':
            $value    = $_options['message'];
            $eqLogic = $this->getEqLogic();
            $eqLogic->checkAndUpdateCmd('digimessage', $value);
            break;

         default:
            digiaction::addLogTemplate('CMD EXEC');
            $eqLogic = $this->getEqLogic();

            $currentMode = $eqLogic->getCmd(null, 'currentMode');
            if (!is_object($currentMode)) {
               throw new Exception(__('La commande de mode courant est introuvable', __FILE__));
            }

            $newMode = $this->getLogicalId();

            log::add('digiaction', 'debug', '│ running setup for mode : ' . $newMode);

            $preCheck = $eqLogic->doPreCheck($newMode);
            if (!$preCheck) {
               log::add('digiaction', 'debug', '│ global check FALSE ');
               log::add('digiaction', 'debug', '│ run action -> skipped ');
               $eqLogic->checkAndUpdateCmd('digimessage', 'Contrôle(s) en échec');
               $eqLogic->doAction($newMode, 'preCheckActionError');
            } else {
               log::add('digiaction', 'debug', '│ global check TRUE');
               $eqLogic->doAction($newMode, 'doAction');
               $currentMode->event($newMode);
               $eqLogic->checkAndUpdateCmd('digimessage', 'Commande réalisée pour ' . $newMode);
            }

            digiaction::addLogTemplate();
            $eqLogic->refreshWidget();
            return;
      }
   }


   /*     * **********************Getteur Setteur*************************** */
}
