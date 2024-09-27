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

   private static $_log_trace;

   public function __construct() {
      $this->_log_trace  = self::getTrace();
   }
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
     */
   public static function cron5() {
      foreach (eqLogic::byType(__CLASS__) as $eqLogic) {
         $eqLogic->save();
      }
   }

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
   // Fonction exécutée automatiquement avant la création de l'équipement 
   public function preInsert() {
      $this->setDefaultColor();

      $this->setIsEnable(1);
   }

   // Fonction exécutée automatiquement après la création de l'équipement 
   public function postInsert() {
   }

   // Fonction exécutée automatiquement avant la mise à jour de l'équipement 
   public function preUpdate() {
   }

   // Fonction exécutée automatiquement après la mise à jour de l'équipement 
   public function postUpdate() {
   }

   // Fonction exécutée automatiquement avant la suppression de l'équipement 
   public function preRemove() {
   }

   // Fonction exécutée automatiquement après la suppression de l'équipement 
   public function postRemove() {
   }

   // Fonction exécutée automatiquement après la sauvegarde (création ou mise à jour) de l'équipement 
   public function preSave() {

      $configInit = $this->getConfiguration('users');
      $configUsers = $this->checkOrInitUserValidityDate($configInit, $this->getHumanName(), true);
      $this->setConfiguration('users', $configUsers);
   }

   public function postSave() {

      $currentMode = $this->getCmd(null, 'currentMode');
      if (!is_object($currentMode)) {
         $currentMode = new digiactionCmd();
         $currentMode->setOrder(1);
      }
      $currentMode->setName(__('Mode', __FILE__));
      $currentMode->setEqLogic_id($this->id);
      $currentMode->setLogicalId('currentMode');
      $currentMode->setType('info');
      $currentMode->setSubType('string');
      $currentMode->save();

      $currentMode = $this->getCmd(null, 'digimessage');
      if (!is_object($currentMode)) {
         $currentMode = new digiactionCmd();
         $currentMode->setOrder(2);
      }
      $currentMode->setName(__('Message', __FILE__));
      $currentMode->setEqLogic_id($this->id);
      $currentMode->setLogicalId('digimessage');
      $currentMode->setType('info');
      $currentMode->setSubType('string');
      $currentMode->save();

      $updateMsg = $this->getCmd(null, 'updatemessage');
      if (!is_object($updateMsg)) {
         $updateMsg = new digiactionCmd();
         $updateMsg->setOrder(3);
      }
      $updateMsg->setName(__('MaJ Message', __FILE__));
      $updateMsg->setEqLogic_id($this->id);
      $updateMsg->setLogicalId('updatemessage');
      $updateMsg->setType('action');
      $updateMsg->setSubType('message');
      $updateMsg->setDisplay('title_disable', 1);
      $updateMsg->save();

      $changeUserPwd = $this->getCmd(null, 'changeUserPwd');
      if (!is_object($changeUserPwd)) {
         $changeUserPwd = new digiactionCmd();
         $changeUserPwd->setOrder(4);
      }
      $changeUserPwd->setName(__('Changer code utilisateur', __FILE__));
      $changeUserPwd->setEqLogic_id($this->id);
      $changeUserPwd->setLogicalId('changeUserPwd');
      $changeUserPwd->setType('action');
      $changeUserPwd->setSubType('message');
      $changeUserPwd->setDisplay('title_placeholder', __('Utilisateur', __FILE__));
      $changeUserPwd->setDisplay('message_placeholder', __('Nouveau code', __FILE__));
      $changeUserPwd->save();

      $existing_mode = array();
      $existing_mode[] = 'changeUserPwd';
      $existing_mode[] = 'updatemessage';
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
         if ($cmd->getType() == 'action' && !in_array($cmd->getLogicalId(), $existing_mode)) {
            $cmd->remove();
         }
      }

      $this->refreshWidget();
   }

   public function setDefaultColor() {
      log::add(__CLASS__, 'debug', '| set default color for ' . $this->getName());
      $this->setConfiguration('colorBgDefault', "#3c8dbc");
      $this->setConfiguration('colorBgActif', "#3c8dbc");
      $this->setConfiguration('colorTextDefault', "#ffffff");
      $this->setConfiguration('colorTextActif', "#ffffff");
   }

   public static function checkOrInitUserValidityDate($configUsers, $eqHumanName, $_force = false) {

      foreach ($configUsers as $key => $value) {
         if ($value['userCode'] === "") {
            $userName = empty($value['name']) ? '' : ' pour l\'utilisateur [' . $value['name'] . ']';
            throw new Exception("Pas de code saisi" . $userName);
         } elseif (!preg_match("/^(A|B|(\d))+$/", $value['userCode'], $match)) {
            $userName = empty($value['name']) ? '' : ' pour l\'utilisateur [' . $value['name'] . ']';
            throw new Exception("Code [" . $value['userCode'] . "] non valide" . $userName);
         }

         if (!empty($value['startCron'])) {
            $now = date("Y-m-d H:i:s");

            // if no duration (anymore) but end date set, then remove end date
            if (empty($value['duration']) && !empty($value['endTo'])) {
               unset($value['endTo']);
            }

            // check if next start date is set or if end date is passed
            if ($_force || empty($value['startFrom']) || (!empty($value['endTo']) && strtotime($value['endTo']) < strtotime($now))) {
               $cron = new cron();
               $cron->setSchedule($value['startCron']);
               $nextRunCron = self::getNextRunDate($cron, $now);
               // log::add(__CLASS__, 'info', '| nextRunCron : ' . $nextRunCron->format("Y-m-d H:i:s"));

               // if real cron && next date exist 
               if ($nextRunCron != false) {
                  if (self::$_log_trace) self::addLogTemplate('UPDATING VALIDITY DATE FOR ' . $eqHumanName . ' - USER : ' . $value['name']);
                  $startCronArray = explode(' ', $value['startCron']);
                  // if fixed date is set 
                  if (count($startCronArray) == 6) {
                     // if date asked and next one calculated have the same year, then it's a real next date
                     if ($nextRunCron->format("Y") == $startCronArray[5]) {
                        if (self::$_log_trace) log::add(__CLASS__, 'debug', '| start date : ' . $nextRunCron->format("Y-m-d H:i:s"));
                        $value['startFrom'] = $nextRunCron->format("Y-m-d H:i:s");
                        if (!empty($value['duration'])) {
                           $value['endTo'] = date("Y-m-d H:i:s", strtotime($value['startFrom']) + ($value['duration'] * 60));
                           if (self::$_log_trace)  log::add(__CLASS__, 'debug', '| end date : ' .  $value['endTo']);
                        }
                     }
                     // if not the same, then it's in the futur and we can t apply it
                     else {
                        // calculate the previous date
                        $prevRunCron = self::getPreviousRunDate($cron, $now);
                        if (self::$_log_trace) log::add(__CLASS__, 'debug', '| fixed date in the past : ' . $prevRunCron->format("Y-m-d H:i:s"));

                        if ($prevRunCron != false) {
                           $value['startFrom'] = $prevRunCron->format("Y-m-d H:i:s");

                           if (!empty($value['duration'])) {
                              $value['endTo'] = date("Y-m-d H:i:s", strtotime($value['startFrom']) + ($value['duration'] * 60));
                           }
                        } else {
                           if (!empty($value['startFrom']))  unset($value['startFrom']);
                           if (!empty($value['endTo'])) unset($value['endTo']);
                        }
                     }
                  }
                  // a real cron date
                  else {
                     if (self::$_log_trace)  log::add(__CLASS__, 'debug', '| start date : ' . $nextRunCron->format("Y-m-d H:i:s"));
                     $value['startFrom'] = $nextRunCron->format("Y-m-d H:i:s");
                     if (!empty($value['duration'])) {

                        // check the next next date, to see if there is no conflict with the duration
                        $nextDateTmp = date("Y-m-d H:i:s", strtotime($value['startFrom']) + 60);
                        $nextRunCron_2 = self::getNextRunDate($cron, $nextDateTmp);
                        if (self::$_log_trace) log::add(__CLASS__, 'debug', '| next next date : ' . $nextRunCron_2->format("Y-m-d H:i:s"));

                        $datediff = strtotime($nextRunCron_2->format("Y-m-d H:i:s")) - strtotime($nextRunCron->format("Y-m-d H:i:s"));
                        $datediffInMin = round($datediff / 60);
                        if ($datediff < ($value['duration'] * 60)) {
                           if (self::$_log_trace) log::add(__CLASS__, 'debug', '| duration [' . $value['duration'] . '] is lower than 2 occurences  ' . $datediffInMin);
                           if (!empty($value['endTo']))  unset($value['endTo']);
                           throw new Exception('Erreur sur l\'utilisateur [' . $value['name'] . '], la durée de validité ' . $value['duration'] . ' doit être inférieur à ' . $datediffInMin . ' min');
                        } else {
                           $value['endTo'] = date("Y-m-d H:i:s", strtotime($value['startFrom']) + ($value['duration'] * 60));
                           if (self::$_log_trace) log::add(__CLASS__, 'debug', '| end date : ' .  $value['endTo']);
                        }
                     }
                  }

                  if (self::$_log_trace) self::addLogTemplate();
               }
            }
         } else {
            if (!empty($value['startFrom']))  unset($value['startFrom']);
            if (!empty($value['endTo']))  unset($value['endTo']);
         }

         $configUsers[$key] = $value;
      }

      return $configUsers;
   }


   public static function getNextRunDate($cron, $start) {
      try {
         $c = new Cron\CronExpression(checkAndFixCron($cron->getSchedule()), new Cron\FieldFactory);
         return $c->getNextRunDate($start, 0, true);
      } catch (Exception $e) {
         log::add(__CLASS__, 'warning', '| issue with cron expreesion : ' . $e->getMessage());
      } catch (Error $e) {
         log::add(__CLASS__, 'warning', '| issue with cron expreesion : ' . $e->getMessage());
      }
      return false;
   }

   public static function getPreviousRunDate($cron, $start) {
      try {
         $c = new Cron\CronExpression(checkAndFixCron($cron->getSchedule()), new Cron\FieldFactory);
         return $c->getPreviousRunDate($start, 0, true);
      } catch (Exception $e) {
         log::add(__CLASS__, 'warning', '| issue with cron expreesion : ' . $e->getMessage());
      } catch (Error $e) {
         log::add(__CLASS__, 'warning', '| issue with cron expreesion : ' . $e->getMessage());
      }
      return false;
   }

   public static function getTrace() {
      return (bool) config::byKey('trace', __CLASS__, false);
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
         if (!key_exists('preCheck', $value)) {
            log::add('digiaction', 'warning', '│ the key "preCheck" does not exist for mode ' . $_mode);
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

   public function replaceCustomData(string $data, string $modeName = '') {

      $arrResearch = array('#eqId#', '#eqName#', '#modeName#', '#nbWrongPwd#');
      $arrReplace = array($this->getId(), $this->getName(), $modeName, $this->getConfiguration('currentWrongPwd', 0));

      return str_replace($arrResearch, $arrReplace, $data);
   }

   public function doAction($_mode, $_type, $is_panic = false) {
      if (!is_array($this->getConfiguration('modes'))) {
         return;
      }

      $checkAction = null;
      foreach ($this->getConfiguration('modes') as $key => $value) {
         if ($value['name'] != $_mode) {
            continue;
         }
         if (!key_exists($_type, $value)) {
            log::add('digiaction', 'warning', '│ the key "' . $_type . '" does not exist for mode ' . $_mode);
            continue;
         }
         log::add('digiaction', 'debug', '│ *** action(s) ' . $_type . ' will be executed ***');

         foreach ($value[$_type] as $action) {
            try {
               $options = array();
               if (isset($action['options'])) {
                  $options = $action['options'];
               }

               if (isset($options['enable']) && !$options['enable']) {
                  log::add('digiaction', 'debug', '│ action skipped -- not enable ');
                  continue;
               }

               log::add('digiaction', 'debug', '│ will check for panic => current cmd :' . ($options['panic'] ? 'true' : 'false') . ' // panic user : ' . ($is_panic ? 'true' : 'false'));
               if (isset($options['panic']) && $options['panic'] && !$is_panic) {
                  log::add('digiaction', 'debug', '│ action skipped -- not in panic mode ! ');
                  continue;
               }

               if (isset($options['tags'])) {
                  $options['tags'] =  $this->replaceCustomData($options['tags'], $_mode);
               }
               if (isset($options['message'])) {
                  $options['message'] =  $this->replaceCustomData($options['message'], $_mode);
               }
               $options['source'] = __CLASS__;
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
      $replace['#randomkeys#'] = $this->getConfiguration('randomkeys', 0);

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

   public function getCurrentMode() {
      $cmd = $this->getCmd('info',  'currentMode');
      $currentMode = is_object($cmd) ? $cmd->execCmd() : '';

      return $currentMode;
   }

   public function getAvailableModeHTML() {

      self::addLogTemplate('CREATE HTML CODE FOR AVAILABLE MODES [' . $this->getName() . ']');
      $modes = $this->getModeDetails();
      $currentMode = $this->getCurrentMode();
      $defaultBgColor = $this->getConfiguration('colorBgDefault');
      $defaultTextColor = $this->getConfiguration('colorTextDefault');

      $actifBgColor = $this->getConfiguration('colorBgActif');
      $actifTextColor = $this->getConfiguration('colorTextActif');

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
            $backgoundColor = ($currentMode == $mode['name']) ? $actifBgColor : $defaultBgColor;
            $textColor = ($currentMode == $mode['name']) ? $actifTextColor : $defaultTextColor;
            $style = 'style="background-color:' . $backgoundColor . '!important;color:' . $textColor . '!important;"';

            $tmpResult .= '<li class="digiActionMode digiActionText ' . $digi . '" digi-action="' . $mode['name'] . '" digi-cmdId="' . $cmdId . '" digi-timer="' . $mode['timer'] . '" ' . $style . '>';
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
         list($modeWrongPwd, $setupWrongPwd, $currentWrongPwd) = $this->getSecurityOptions($nextCmdId);

         if (!$checkPwd) {
            self::addLogTemplate();
            if ($setupWrongPwd > 0) {
               $this->setConfiguration('currentWrongPwd', 0);
               $this->save(true);
            }
            return array(true, null, false);
         }

         // if password is required, then check the password send to see if it matches on saved password.
         $check = 0;
         log::add('digiaction', 'debug', '│ checking password : >' . $userCode . '<');
         foreach ($this->getConfiguration('users') as $user) {

            if (isset($user['active']) && !$user['active']) {
               log::add('digiaction', 'debug', '│ user "' . $user['name'] . '" disabled');
               continue;
            }

            if (strcmp($user['userCode'], $userCode) !== 0) {
               // log::add('digiaction', 'debug', '│ wrong code ');
               continue;
            }

            $userPanic = $user['isPanic'] ?: false;

            $now = time();

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
               continue;
            }

            if (!empty($user['endTo']) && self::checkIsAValidDate($user['endTo']) && strtotime($user['endTo']) < $now) {
               log::add('digiaction', 'debug', '│ date restriction -- password OK for user [' . $user['name'] . '] but end date in the past');
               $check = 2;
               continue;
            }

            log::add('digiaction', 'debug', '│ password OK for user : ' . $user['name']);
            if ($userPanic) log::add('digiaction', 'warning', '│ **** PANIC USER ! *****');
            $check = 1;
            break;
         }

         if ($check == 0) {
            log::add('digiaction', 'warning', '│ no user found with password "' . $userCode . '"');

            //increment wrong pwd
            $newWrongPwd = $currentWrongPwd + 1;

            log::add('digiaction', 'debug', '│ increment wrong pwd to ' . $newWrongPwd . ' => setup limit ' . $setupWrongPwd . ' with mode ' . $modeWrongPwd);
            $this->setConfiguration('currentWrongPwd', $newWrongPwd);
            $this->save(true);

            if (
               $modeWrongPwd != 'none' && $setupWrongPwd > 0 &&
               (
                  ($modeWrongPwd == 'modulo' && ($newWrongPwd % $setupWrongPwd) == 0)
                  || ($modeWrongPwd == 'greaterThan' && $setupWrongPwd <= $newWrongPwd)
               )
            ) {
               log::add('digiaction', 'debug', '│ will perform actions for wrong password ');
               $cmd = cmd::byId($nextCmdId);
               $newMode = $cmd->getLogicalId();
               $this->doAction($newMode, 'doWrongPwd');
            }
         } else {
            $this->setConfiguration('currentWrongPwd', 0);
            $this->save(true);
         }
      } catch (Exception $e) {
         log::add('digiaction', 'error', '│ Could not get cmd details => ' . $e->getMessage());
         $check = false;
      }

      self::addLogTemplate();
      $checkFinal = ($check == 1) ? true : false;
      return array($checkFinal, $user['name'] ?? null, $userPanic);
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

   public function getSecurityOptions($nextCmdId) {
      $cmd = digiactionCmd::byId($nextCmdId);
      if (!is_object($cmd)) {
         throw new Exception('Unexisting command ID for ' . $nextCmdId);
      }

      $cmdName = $cmd->getLogicalId();
      // log::add('digiaction', 'debug', '│ found the cmd \'' . $cmdName . '\' for cmdId ' . $nextCmdId);

      $modes = $this->getModeDetails($cmdName);

      foreach ($modes as $mode) {
         if ($mode['name'] != $cmdName) {
            continue;
         }
         return array($mode['alertWrongPwd'] ?? "none", $mode['nbWrongPwd'] ?? -1,  $this->getConfiguration('currentWrongPwd', 0));
      }

      return array('none', -1,  $this->getConfiguration('currentWrongPwd', 0));
   }

   public static function addLogTemplate($msg = null, $inter = false, $level = 'debug') {

      if (!is_null($msg)) {
         $first = $inter ? '├' : '┌';
         log::add('digiaction', $level, $first . '────────────────────────────────────');
         log::add('digiaction', $level, '│    ' . $msg);
         log::add('digiaction', $level, '├────────────────────────────────────');
      } elseif ($inter) {
         log::add('digiaction', $level, '├────────────────────────────────────');
      } else {
         log::add('digiaction', $level, '└────────────────────────────────────');
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
      /**
       * @var digiaction $eqLogic
       */
      switch ($this->getLogicalId()) {
         case 'updatemessage':
            $value    = $_options['message'];
            $eqLogic = $this->getEqLogic();
            $eqLogic->checkAndUpdateCmd('digimessage', $value);
            break;

         case 'changeUserPwd':
            if (!isset($_options['title'])) {
               throw new Exception(__('Aucun utilisateur indiqué (champs "titre")', __FILE__));
            }
            if (!isset($_options['message'])) {
               throw new Exception(__('Aucun nouveau mot de passe indiqué (champ "message")', __FILE__));
            }
            if (!preg_match("/^(A|B|(\d))+$/", $_options['message'], $match)) {
               throw new Exception(__('Code non valide', __FILE__));
            }

            $eqLogic = $this->getEqLogic();
            $users =   $eqLogic->getConfiguration('users');

            $userSearched = trim($_options['title']);
            log::add('digiaction', 'debug', '│ we are looking for user : ' . $userSearched);
            foreach ($users as $key => $user) {
               if ($user['name'] != $userSearched) {
                  continue;
               }

               $user['userCode'] = trim($_options['message']);
               $users[$key] = $user;

               $eqLogic->setConfiguration('users', $users);
               $eqLogic->save();
               return;
            }
            log::add('digiaction', 'debug', '│ user ' . $userSearched . ' not found :(');

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
            $isPanic = isset($_options['panic']) && $_options['panic'];
            if ($isPanic) {
               //if mode panic, then bypass pre-check option
               log::add('digiaction', 'debug', '│ PRE-CHECK test are skipped -- PANIC IN PROGRESS ');
               $preCheck = true;
            } else {
               $preCheck = $eqLogic->doPreCheck($newMode);
            }
            if (!$preCheck) {
               log::add('digiaction', 'debug', '│ global check FALSE ');
               log::add('digiaction', 'debug', '│ run action -> skipped ');
               $eqLogic->checkAndUpdateCmd('digimessage', 'Contrôle(s) en échec');
               $eqLogic->doAction($newMode, 'preCheckActionError', $isPanic);
            } else {
               log::add('digiaction', 'debug', '│ global check TRUE');
               $eqLogic->doAction($newMode, 'doAction', $isPanic);
               $currentMode->event($newMode);

               $txtOKtemp = $eqLogic->getConfiguration('textOK', 'Actions réalisées pour ' . $newMode);
               $txtOK = $eqLogic->replaceCustomData($txtOKtemp, $newMode);
               $eqLogic->checkAndUpdateCmd('digimessage', $txtOK);
               if (!empty($_options['userName'])) {
                  log::add('digiaction', 'info', '│ Commande "' . $this->getName() . '" a été réalisée par : ' . $_options['userName']);
               } else {
                  log::add('digiaction', 'info', '│ Commande "' . $this->getName() . '" a été réalisée (sans contrôle)');
               }
            }

            digiaction::addLogTemplate();
            $eqLogic->refreshWidget();
            return;
      }
   }


   /*     * **********************Getteur Setteur*************************** */
}
