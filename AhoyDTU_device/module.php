<?php
require_once(__DIR__ . "/../libs/genericMQTT_IPS_module.php");

	class AhoyDTU_device extends genericMQTT_IPS_module
	{
		const PREFIX = "AHOY_DTU";
		public function Create()
		{
			//Never delete this line!
			parent::Create();
			$this->RegisterPropertyString('PathToConfigurationFile', __DIR__ . "/../libs/variables_ahoydtu.json");					
			if ($this->RegisterProfile(1, static::PREFIX.".device.status", "", "", "", 0, 0, 0, 1))
			{
				IPS_SetVariableProfileAssociation(static::PREFIX.".status", 0, "offline", "", 0);
				IPS_SetVariableProfileAssociation(static::PREFIX.".status", 1, "partial", "", 0);
				IPS_SetVariableProfileAssociation(static::PREFIX.".status", 2, "online", "", 0);
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