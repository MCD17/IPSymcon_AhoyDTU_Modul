<?php
require_once(__DIR__ . "/../libs/genericMQTT_IPS_module.php");

	class MicroinverterOfAhoyDTU extends genericMQTT_IPS_module
	{
		const PREFIX = "AHOY_DTU";
		public function Create()
		{
			//Never delete this line!
			parent::Create();				
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

			//Create variables from configuration file
			$this->CreateVariablesFromConfigurationFile(__DIR__ . "/../libs/variables_microinverter.json");
		}
	}