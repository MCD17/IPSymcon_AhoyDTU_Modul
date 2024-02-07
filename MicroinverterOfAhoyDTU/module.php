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