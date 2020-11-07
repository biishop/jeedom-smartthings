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

class smartthings extends eqLogic {
    /*     * *************************Attributs****************************** */



    /*     * ***********************Methode static*************************** */


      public static function cron() {
          $eqLogics = eqLogic::byType('smartthings');
          foreach ($eqLogics as $eqLogic) {
              $eqLogic->refresh();
          }
      }



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

    public function preInsert() {
        
    }

    public function postInsert() {
        
    }

    public function preSave() {
        
    }

    public function postSave() {
        
    }

    public function preUpdate() {
        
    }

    public function postUpdate() {
        
    }

    public function preRemove() {
        
    }

    public function postRemove() {
        
    }

    public function refresh() {
        if($this->getConfiguration('type') == "6962dd3b-aac6-4e86-9d85-9b86ba6ff166") {
            $status = self::getDeviceStatus($this->getConfiguration('deviceId'));
            $this->checkAndUpdateCmd('switch', ($status->switch->switch->value == "off") ? 0 : 1);
            $this->checkAndUpdateCmd('status', ($status->washerOperatingState->machineState->value == "stop") ? 0 : 1);
            $vars = get_object_vars($status);
            $this->checkAndUpdateCmd('temperature', $vars["custom.washerWaterTemperature"]->washerWaterTemperature->value);
            $this->checkAndUpdateCmd('rinse_cycles', $vars["custom.washerRinseCycles"]->washerRinseCycles->value);
            $this->checkAndUpdateCmd('job', $status->execute->data->value->payload->currentJobState);
            $this->checkAndUpdateCmd('remaining_time', self::dateDiff(time(), strtotime($status->washerOperatingState->completionTime->value)));
            $this->checkAndUpdateCmd('progress', $status->execute->data->value->payload->progressPercentage);
            $this->checkAndUpdateCmd('mode', self::getWasherModeLabel($status->washerMode->washerMode->value));
            $this->checkAndUpdateCmd('end_mode', $status->washerOperatingState->completionTime->value);
            $this->checkAndUpdateCmd('spin_level', $vars["custom.washerSpinLevel"]->washerSpinLevel->value);
        } else if($this->getConfiguration('type') == "c2c-rgbw-color-bulb") {
            // TODO
        } else if($this->getConfiguration('type') == "Samsung OCF Air Conditioner") {
			$status = self::getDeviceStatus($this->getConfiguration('deviceId'));
			$this->checkAndUpdateCmd('status', ($status->switch->switch->value == "off") ? 0 : 1);
			$this->checkAndUpdateCmd('temperature', $status->temperatureMeasurement->temperature->value);
			$this->checkAndUpdateCmd('thermostat', $status->thermostatCoolingSetpoint->coolingSetpoint->value);
			$this->checkAndUpdateCmd('modefn', $status->airConditionerMode->airConditionerMode->value);
			$this->checkAndUpdateCmd('fanmode', $status->airConditionerFanMode->fanMode->value);
			$this->checkAndUpdateCmd('power', $status->powerConsumptionReport->powerConsumption->value->energy);
			
        } else if($this->getConfiguration('type') == "Samsung OCF TV") {
			$status = self::getDeviceStatus($this->getConfiguration('deviceId'));
			$this->checkAndUpdateCmd('status', ($status->switch->switch->value == "off") ? 0 : 1);
			$this->checkAndUpdateCmd('source', $status->mediaInputSource->inputSource->value);
        }
    }
	
    public function controle($cmd) {
		log::add('smartthings', 'info', __('type test', __FILE__));
		if($this->getConfiguration('type') == "Samsung OCF Air Conditioner") {
			log::add('smartthings', 'debug', __('SetDeviceStatus launch', __FILE__));
			$status = self::SetDeviceStatus($this->getConfiguration('deviceId'),$cmd);
        } elseif($this->getConfiguration('type') == "Samsung OCF TV") {
			log::add('smartthings', 'debug', __('SetDeviceStatus launch', __FILE__));
			$status = self::SetDeviceStatus($this->getConfiguration('deviceId'),$cmd);
        }
    }	

    public static function getWasherModeLabel($mode) {
        switch ($mode) {
            case "Table_00_Course_5C":
                return "Super rapide";
                break;
            case "Table_00_Course_5B":
                return "Coton";
                break;
            case "Table_00_Course_68":
                return "ECoton";
                break;
            case "Table_00_Course_67":
                return "Synthétique";
                break;
            case "Table_00_Course_65":
                return "Laine";
                break;
            case "Table_00_Course_6C":
                return "Jeans";
                break;
            case "Table_00_Course_64":
                return "Rinçage + essorage";
                break;
            case "Table_00_Course_63":
                return "Nettoyage tambour";
                break;
            case "Table_00_Course_61":
                return "Couleur";
                break;
            case "Table_00_Course_60":
                return "Imperméable";
                break;
            case "Table_00_Course_5F":
                return "Bébé coton";
                break;
            case "Table_00_Course_5E":
                return "Délicat";
                break;
            case "Table_00_Course_66":
                return "Draps";
                break;
            case "Table_00_Course_5D":
                return "Eco";
                break;
        }
    }

    public static function dateDiff($date1, $date2){
        $diff = abs($date1 - $date2);
        $resultDiff = array();

        $tmp = $diff;
        $resultDiff['second'] = $tmp % 60;

        $tmp = floor( ($tmp - $resultDiff['second']) /60 );
        $resultDiff['minute'] = $tmp % 60;

        $tmp = floor( ($tmp - $resultDiff['minute'])/60 );
        $resultDiff['hour'] = $tmp % 24;

        $tmp = floor( ($tmp - $resultDiff['hour'])  /24 );
        $resultDiff['day'] = $tmp;

        $str = "";

        if($resultDiff['hour'] > 0) {
            $str .= $resultDiff['hour']." heure";
        }

        if($resultDiff['minute'] > 0) {
            if($str != "") {
                $str.= " et ";
            }
            $str .= $resultDiff['minute']." minute";
        }

        return $str;
    }

    public static function synchronize() {
        $devices = self::getDevices();
		log::add('smartthings', 'info', __('synchronize deviceID', __FILE__));
        foreach ($devices->items as $device) {

			log::add('smartthings', 'info', __('Log des deviceID', __FILE__));	
            if(!self::isDeviceExist($device->deviceId)) {
				// Log des deviceID
				log::add('smartthings', 'info', __('Existing Device ->', __FILE__).'Device Name:'.$device->name.'/Type ID:'.$device->deviceTypeId.'/TypeName:' . $device->deviceTypeName);
                if($device->deviceTypeName  == "Samsung OCF Washer") { // Machine à laver Samsung
                    $eqLogic = new eqLogic();
                    $eqLogic->setEqType_name('smartthings');
                    $eqLogic->setIsEnable(1);
                    $eqLogic->setIsVisible(1);
                    $eqLogic->setName($device->label);
                    $eqLogic->setConfiguration('type', $device->deviceTypeId);
                    $eqLogic->setConfiguration('deviceId', $device->deviceId);
                    $eqLogic->save();

                    $smartthingsCmd = new smartthingsCmd();
                    $smartthingsCmd->setType('info');
                    $smartthingsCmd->setSubType('binary');
                    $smartthingsCmd->setName('Statut');
                    $smartthingsCmd->setLogicalId("status");
                    $smartthingsCmd->setEqLogic_id($eqLogic->getId());
                    $smartthingsCmd->save();

                    $smartthingsCmd = new smartthingsCmd();
                    $smartthingsCmd->setType('info');
                    $smartthingsCmd->setSubType('binary');
                    $smartthingsCmd->setName('Sous tension');
                    $smartthingsCmd->setLogicalId("switch");
                    $smartthingsCmd->setEqLogic_id($eqLogic->getId());
                    $smartthingsCmd->save();

                    $smartthingsCmd = new smartthingsCmd();
                    $smartthingsCmd->setType('info');
                    $smartthingsCmd->setSubType('numeric');
                    $smartthingsCmd->setName('Temperature');
                    $smartthingsCmd->setLogicalId("temperature");
                    $smartthingsCmd->setEqLogic_id($eqLogic->getId());
                    $smartthingsCmd->save();

                    $smartthingsCmd = new smartthingsCmd();
                    $smartthingsCmd->setType('info');
                    $smartthingsCmd->setSubType('numeric');
                    $smartthingsCmd->setName('Nombre cycle de rinçage');
                    $smartthingsCmd->setLogicalId("rinse_cycles");
                    $smartthingsCmd->setEqLogic_id($eqLogic->getId());
                    $smartthingsCmd->save();

                    $smartthingsCmd = new smartthingsCmd();
                    $smartthingsCmd->setType('info');
                    $smartthingsCmd->setSubType('string');
                    $smartthingsCmd->setName('Programme courant');
                    $smartthingsCmd->setLogicalId("job");
                    $smartthingsCmd->setEqLogic_id($eqLogic->getId());
                    $smartthingsCmd->save();

                    $smartthingsCmd = new smartthingsCmd();
                    $smartthingsCmd->setType('info');
                    $smartthingsCmd->setSubType('string');
                    $smartthingsCmd->setName('Cycle courant');
                    $smartthingsCmd->setLogicalId("mode");
                    $smartthingsCmd->setEqLogic_id($eqLogic->getId());
                    $smartthingsCmd->save();

                    $smartthingsCmd = new smartthingsCmd();
                    $smartthingsCmd->setType('info');
                    $smartthingsCmd->setSubType('string');
                    $smartthingsCmd->setName('Fin du programme');
                    $smartthingsCmd->setLogicalId("end_mode");
                    $smartthingsCmd->setEqLogic_id($eqLogic->getId());
                    $smartthingsCmd->save();

                    $smartthingsCmd = new smartthingsCmd();
                    $smartthingsCmd->setType('info');
                    $smartthingsCmd->setSubType('numeric');
                    $smartthingsCmd->setName('Essorage');
                    $smartthingsCmd->setLogicalId("spin_level");
                    $smartthingsCmd->setEqLogic_id($eqLogic->getId());
                    $smartthingsCmd->save();

                    $smartthingsCmd = new smartthingsCmd();
                    $smartthingsCmd->setType('info');
                    $smartthingsCmd->setSubType('string');
                    $smartthingsCmd->setName('Temps restant');
                    $smartthingsCmd->setLogicalId("remaining_time");
                    $smartthingsCmd->setEqLogic_id($eqLogic->getId());
                    $smartthingsCmd->save();

                    $smartthingsCmd = new smartthingsCmd();
                    $smartthingsCmd->setType('info');
                    $smartthingsCmd->setSubType('numeric');
                    $smartthingsCmd->setName('Progression');
                    $smartthingsCmd->setLogicalId("progress");
                    $smartthingsCmd->setEqLogic_id($eqLogic->getId());
                    $smartthingsCmd->save();

                    $smartthingsCmd = new smartthingsCmd();
                    $smartthingsCmd->setType('action');
                    $smartthingsCmd->setSubType('other');
                    $smartthingsCmd->setName('Refresh');
                    $smartthingsCmd->setLogicalId("refresh");
                    $smartthingsCmd->setEqLogic_id($eqLogic->getId());
                    $smartthingsCmd->save();
                } else if($device->name == "c2c-rgbw-color-bulb") { // Yeelight
                    $eqLogic = new eqLogic();
                    $eqLogic->setEqType_name('smartthings');
                    $eqLogic->setIsEnable(1);
                    $eqLogic->setIsVisible(1);
                    $eqLogic->setName($device->label);
                    $eqLogic->setConfiguration('type', $device->name);
                    $eqLogic->setConfiguration('deviceId', $device->deviceId);
                    $eqLogic->save();
                //} else if($device->name == "[room a/c] Samsung") { // Climatiseur
				} else if($device->deviceTypeName == "Samsung OCF Air Conditioner") { // Climatiseur
					log::add('event', 'info', __('New Climatiseur', __FILE__));
                    $eqLogic = new eqLogic();
                    $eqLogic->setEqType_name('smartthings');
                    $eqLogic->setIsEnable(1);
                    $eqLogic->setIsVisible(1);
                    $eqLogic->setName($device->label);
                    $eqLogic->setConfiguration('type', $device->deviceTypeName);
                    $eqLogic->setConfiguration('deviceId', $device->deviceId);
                    $eqLogic->save();
					
                    $smartthingsCmd = new smartthingsCmd();
                    $smartthingsCmd->setType('info');
                    $smartthingsCmd->setSubType('binary');
                    $smartthingsCmd->setName('Status');
                    $smartthingsCmd->setLogicalId("status");
                    $smartthingsCmd->setEqLogic_id($eqLogic->getId());
                    $smartthingsCmd->save();					

                    $smartthingsCmd = new smartthingsCmd();
                    $smartthingsCmd->setType('info');
                    $smartthingsCmd->setSubType('numeric');
                    $smartthingsCmd->setName('Temperature');
                    $smartthingsCmd->setLogicalId("temperature");
                    $smartthingsCmd->setEqLogic_id($eqLogic->getId());
                    $smartthingsCmd->save();

                    $smartthingsCmd = new smartthingsCmd();
                    $smartthingsCmd->setType('info');
                    $smartthingsCmd->setSubType('numeric');
                    $smartthingsCmd->setName('Thermostat');
                    $smartthingsCmd->setLogicalId("thermostat");
                    $smartthingsCmd->setEqLogic_id($eqLogic->getId());
                    $smartthingsCmd->save();

                    $smartthingsCmd = new smartthingsCmd();
                    $smartthingsCmd->setType('info');
                    $smartthingsCmd->setSubType('string');
                    $smartthingsCmd->setName('Mode Fn');
                    $smartthingsCmd->setLogicalId("modefn");
                    $smartthingsCmd->setEqLogic_id($eqLogic->getId());
                    $smartthingsCmd->save();

                    $smartthingsCmd = new smartthingsCmd();
                    $smartthingsCmd->setType('info');
                    $smartthingsCmd->setSubType('string');
                    $smartthingsCmd->setName('Mode Fan');
                    $smartthingsCmd->setLogicalId("fanmode");
                    $smartthingsCmd->setEqLogic_id($eqLogic->getId());
                    $smartthingsCmd->save();

                    $smartthingsCmd = new smartthingsCmd();
                    $smartthingsCmd->setType('info');
                    $smartthingsCmd->setSubType('string');
                    $smartthingsCmd->setName('Conso Total');
                    $smartthingsCmd->setLogicalId("power");
                    $smartthingsCmd->setEqLogic_id($eqLogic->getId());
                    $smartthingsCmd->save();
					
					
					
					

                    $smartthingsCmd = new smartthingsCmd();
                    $smartthingsCmd->setType('action');
                    $smartthingsCmd->setSubType('other');
                    $smartthingsCmd->setName('Mise en route');
                    $smartthingsCmd->setLogicalId("controle_on");
                    $smartthingsCmd->setEqLogic_id($eqLogic->getId());
                    $smartthingsCmd->save();
					
					$smartthingsCmd = new smartthingsCmd();
                    $smartthingsCmd->setType('action');
                    $smartthingsCmd->setSubType('other');
                    $smartthingsCmd->setName('Arrêt');
                    $smartthingsCmd->setLogicalId("controle_off");
                    $smartthingsCmd->setEqLogic_id($eqLogic->getId());
                    $smartthingsCmd->save();

					$smartthingsCmd = new smartthingsCmd();
                    $smartthingsCmd->setType('action');
                    $smartthingsCmd->setSubType('message');
                    $smartthingsCmd->setName('T Termostat');
                    $smartthingsCmd->setLogicalId("cible_termo");
                    $smartthingsCmd->setEqLogic_id($eqLogic->getId());
                    $smartthingsCmd->save();



					
                } else if($device->deviceTypeName == "Samsung OCF TV") { // Climatiseur
					log::add('event', 'info', __('New Televiseur', __FILE__));
                    $eqLogic = new eqLogic();
                    $eqLogic->setEqType_name('smartthings');
                    $eqLogic->setIsEnable(1);
                    $eqLogic->setIsVisible(1);
                    $eqLogic->setName($device->label);
                    $eqLogic->setConfiguration('type', $device->deviceTypeName);
                    $eqLogic->setConfiguration('deviceId', $device->deviceId);
                    $eqLogic->save();
					
					$smartthingsCmd = new smartthingsCmd();
                    $smartthingsCmd->setType('info');
                    $smartthingsCmd->setSubType('binary');
                    $smartthingsCmd->setName('Status');
                    $smartthingsCmd->setLogicalId("status");
                    $smartthingsCmd->setEqLogic_id($eqLogic->getId());
                    $smartthingsCmd->save();
					
					$smartthingsCmd = new smartthingsCmd();
                    $smartthingsCmd->setType('info');
                    $smartthingsCmd->setSubType('string');
                    $smartthingsCmd->setName('Source');
                    $smartthingsCmd->setLogicalId("source");
                    $smartthingsCmd->setEqLogic_id($eqLogic->getId());
                    $smartthingsCmd->save();
					
                    $smartthingsCmd = new smartthingsCmd();
                    $smartthingsCmd->setType('action');
                    $smartthingsCmd->setSubType('other');
                    $smartthingsCmd->setName('Mise en route');
                    $smartthingsCmd->setLogicalId("controle_on");
                    $smartthingsCmd->setEqLogic_id($eqLogic->getId());
                    $smartthingsCmd->save();
					
					$smartthingsCmd = new smartthingsCmd();
                    $smartthingsCmd->setType('action');
                    $smartthingsCmd->setSubType('other');
                    $smartthingsCmd->setName('Arrêt');
                    $smartthingsCmd->setLogicalId("controle_off");
                    $smartthingsCmd->setEqLogic_id($eqLogic->getId());
                    $smartthingsCmd->save();					
					
                }
            }
        }
    }

    private static function getDevices() {
        $token = config::byKey('token', 'smartthings');
		log::add('smartthings', 'debug', __('REQ getDevices', __FILE__));
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.smartthings.com/v1/devices",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer " . $token
            ),
        ));

		log::add('smartthings', 'debug', __('URL:', __FILE__).$curl);
        $response = curl_exec($curl);
		log::add('smartthings', 'debug', __('response:', __FILE__).$response);
        curl_close($curl);

        return json_decode($response);
    }

    private static function getDeviceStatus($deviceId) {
        $token = config::byKey('token', 'smartthings');
		log::add('smartthings', 'debug', __('REQ getDeviceStatus', __FILE__));
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.smartthings.com/v1/devices/" .$deviceId. "/components/main/status",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer " . $token
            ),
        ));
		log::add('smartthings', 'debug', __('URL:', __FILE__).$curl);
        $response = curl_exec($curl);
		log::add('smartthings', 'debug', __('response:', __FILE__).$response);
        curl_close($curl);

        return json_decode($response);
    }

    private static function SetDeviceStatus($deviceId,$cmd) {
        $token = config::byKey('token', 'smartthings');
		
        $curl = curl_init();

		$data = '{"commands": [{"component": "main" , "capability": "switch", "command": "'.$cmd.'"}]}';
		
		log::add('smartthings', 'debug', __('REQ SetDeviceStatus', __FILE__).$data);
		//	"{"commands": [{"component": "main" , "capability": "switch", "command": "off"})]}"
						
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.smartthings.com/v1/devices/" .$deviceId. "/commands",
            //CURLOPT_RETURNTRANSFER => true,
            //CURLOPT_ENCODING => "",
            //CURLOPT_MAXREDIRS => 10,
            //CURLOPT_TIMEOUT => 0,
            //CURLOPT_FOLLOWLOCATION => true,
            //CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer " . $token
            ),
			CURLOPT_POSTFIELDS => $data
        ));
		
		log::add('smartthings', 'debug', __('URL:', __FILE__).$curl);
        $response = curl_exec($curl);
		log::add('smartthings', 'debug', __('response:', __FILE__).$response);
        curl_close($curl);

        return json_decode($response);
    }	

    private static function isDeviceExist ($deviceId) {
        $eqLogics = eqLogic::byType('smartthings');
        foreach ($eqLogics as $eqLogic) {
            if ($deviceId == $eqLogic->getConfiguration('deviceId')) {
                return true;
            }
        }
        return false;
    }

    /*
     * Non obligatoire mais permet de modifier l'affichage du widget si vous en avez besoin
      public function toHtml($_version = 'dashboard') {

      }
     */

    /*
     * Non obligatoire mais ca permet de déclencher une action après modification de variable de configuration
    public static function postConfig_<Variable>() {
    }
     */

    /*
     * Non obligatoire mais ca permet de déclencher une action avant modification de variable de configuration
    public static function preConfig_<Variable>() {
    }
     */

    /*     * **********************Getteur Setteur*************************** */
}

class smartthingsCmd extends cmd {
    /*     * *************************Attributs****************************** */


    /*     * ***********************Methode static*************************** */


    /*     * *********************Methode d'instance************************* */

    /*
     * Non obligatoire permet de demander de ne pas supprimer les commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
      public function dontRemoveCmd() {
      return true;
      }
     */

    public function execute($_options = array()) {
        if($this->getType() == "action") {
            if($this->getLogicalId() == "refresh") {
                $eqLogic = $this->getEqLogic();
                $eqLogic->refresh();
            }			
            if($this->getLogicalId() == "controle_on") {
                $eqLogic = $this->getEqLogic();
                $eqLogic->controle('on');
            }	
			if($this->getLogicalId() == "controle_off") {
                $eqLogic = $this->getEqLogic();
                $eqLogic->controle('off');
            }	
        }
    }
    /*     * **********************Getteur Setteur*************************** */
}


