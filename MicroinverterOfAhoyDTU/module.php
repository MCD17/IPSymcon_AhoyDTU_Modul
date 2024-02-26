<?php
require_once(__DIR__ . "/../libs/genericMQTT_IPS_module.php");

	class MicroinverterAhoyDTU extends genericMQTT_IPS_module
	{

		public function Create()
		{
		    $profileName = "AHOY_DTU.inverter.status";
			$profileColor = -1; //tranparent	

			//Never delete this line!
			parent::Create();
			$this->RegisterPropertyString('PathToConfigurationFile', __DIR__ . "/../libs/variables_microinverter.json");
			$this->RegisterPropertyString('PathToFormFile', __DIR__ . "/../libs/ahoydtu_microinverter_form.json");
			$this->RegisterPropertyString('InverterSerial', '');
			
						
			if (!$this->RegisterProfile(1, $profileName, "", "", "", 0, 0, 1, 1)) 
			{
				$this->LogMessage('Variable profile for variable "' .$profileName. '" could not registered correctly.', KL_ERROR);
				return;
			}
			$wasSuccessful = true;
			$wasSuccessful &= IPS_SetVariableProfileAssociation($profileName, 0, "offline", "", $profileColor);
			$wasSuccessful &= IPS_SetVariableProfileAssociation($profileName, 1, "available - not producing", "", $profileColor);
			$wasSuccessful &= IPS_SetVariableProfileAssociation($profileName, 2, "available & producing", "", $profileColor);
			$wasSuccessful &= IPS_SetVariableProfileAssociation($profileName, 3, "available & was producing", "", $profileColor);
			$wasSuccessful &= IPS_SetVariableProfileAssociation($profileName, 4, "was available", "", $profileColor);

			if (!$wasSuccessful)
			{
				$this->LogMessage('Variable profile for variable "' .$profileName. '" could not registered correctly.', KL_ERROR);
				return;
			}			
		}

		public function Destroy()
		{
			//Never delete this line!
			parent::Destroy();
		}

		public function ApplyChanges()
		{
			//Never delete this line!
			parent::ApplyChanges();
		}

		public function RequestAction($Ident, $Value) 
        {
			parent::RequestAction($Ident, $Value);

			switch($Ident) {
				case "setRelativePowerLimit":
					$this->SetLimitRelative( intval($Value));
					$this->SetValue($Ident, $Value);
					break;
				case "setAbsolutePowerLimit":
					$this->SetLimitAbsolute( intval($Value));
					$this->SetValue($Ident, $Value);
					break;
				case "setResetInverter":
					$this->ResetInverter( boolval($Value));
					$this->SetValue($Ident, $Value);
					break;
				case "inverterID":
					//$this->SwitchInverter( boolval( $Value )  );
					$this->SetValue($Ident, $Value);
					break;
					$this->LogMessage('Unknown variable ident "' .$Ident. '".', KL_ERROR);				
			}		
		}

		public function SetLimitRelative(int $limit)
		{
			$baseTopic = $this->ReadPropertyString('MQTTBaseTopic');
			$serial = $this->ReadPropertyString('InverterSerial');

			$topic = $baseTopic . '/ctrl/limit/' . $serial;  

			$this->MQTTSend($topic, strval($limit));
		}

		public function SetLimitAbsolute(int $limit)
		{
			$baseTopic = $this->ReadPropertyString('MQTTBaseTopic');
			$serial = $this->ReadPropertyString('InverterSerial');

			$topic = $baseTopic . '/ctrl/limit/' . $serial;  
			$value = strval($limit).'W';

			$this->MQTTSend($topic,  $value);
		}

		public function ResetInverter(bool $status) 
		{
			$baseTopic = $this->ReadPropertyString('MQTTBaseTopic');
			$serial = $this->ReadPropertyString('InverterSerial');

			$topic = $baseTopic . '/ctrl/restart/' . $serial;  

			$this->MQTTSend($topic, strval($status));
		}
	}
