<?php
require_once(__DIR__ . "/../libs/genericMQTT_IPS_module.php");

	class AhoyDTU_device extends genericMQTT_IPS_module
	{
		public function Create()
		{
			$profileName = "AHOY_DTU.device.status";

			//Never delete this line!
			parent::Create();
			$this->RegisterPropertyString('PathToConfigurationFile', __DIR__ . "/../libs/variables_ahoydtu.json");
				
			if ($this->RegisterProfile(1, $profileName, "", "", "", 0, 0, 0, 1)) 
			{
				$this->LogMessage('Variable profile for variable "' .$profileName. '" could not registered correctly.', KL_ERROR);
				return;
			}					
			$wasSuccessful = true;
			$wasSuccessful &= IPS_SetVariableProfileAssociation($profileName, 0, "offline", "", -1);
			$wasSuccessful &= IPS_SetVariableProfileAssociation($profileName, 1, "partial", "", -1);
			$wasSuccessful &= IPS_SetVariableProfileAssociation($profileName, 2, "online", "", -1);

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
	}