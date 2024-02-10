<?php
require_once(__DIR__ . "/../libs/genericMQTT_IPS_module.php");

	class MicroinverterAhoyDTU extends genericMQTT_IPS_module
	{
		const PREFIX = "AHOY_DTU";
		public function Create()
		{
			//Never delete this line!
			parent::Create();
			$this->RegisterPropertyString('PathToConfigurationFile', __DIR__ . "/../libs/variables_microinverter.json");					
			if ($this->RegisterProfile(1, static::PREFIX.".inverter.status", "", "", "", 0, 0, 0, 1)) 
			{
				IPS_SetVariableProfileAssociation(static::PREFIX.".inverter.status", 0, "offline", "", 0);
				IPS_SetVariableProfileAssociation(static::PREFIX.".inverter.status", 1, "available - not producing", "", 0);
				IPS_SetVariableProfileAssociation(static::PREFIX.".inverter.status", 2, "available & producing", "", 0);
				IPS_SetVariableProfileAssociation(static::PREFIX.".inverter.status", 3, "available & was producing", "", 0);
				IPS_SetVariableProfileAssociation(static::PREFIX.".inverter.status", 4, "was available", "", 0);
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
