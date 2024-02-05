<?php

declare(strict_types=1);
	class AhoyDTU_Instance extends IPSModule
	{
		const PREFIX = "AHOY_DTU";
		public function Create()
		{
			//Never delete this line!
			parent::Create();		

			$this->RegisterPropertyString('BaseTopic', 'solar/');
            $this->RegisterPropertyString('Variables', '[]');

			$this->RegisterProfile(2, static::PREFIX.".Wh", "Electricity", "", " Wh", 0, 0, 0, 1);
			$this->RegisterProfile(2, static::PREFIX.".VAr", "Electricity", "", " VAr", 0, 0, 0, 1);
			
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


			$this->ConnectParent('{40505457-AB2C-057B-C9D7-657EBB53A528}');

			//Setze Filter für ReceiveData
			$baseTopic = $this->ReadPropertyString('BaseTopic');

			$filter = '.*(' . preg_quote($baseTopic) . ').*';
			$this->SetReceiveDataFilter($filter);
			$this->LogMessage('Filter: '.$filter, KL_MESSAGE);

			// Get Variable list
			$variables = $this->GetVariablesConfiguration();

			foreach( $variables as $variable)
			{
				$variableProfile = $variable["VariableProfile"];
				$this->MaintainVariable($variable["Ident"], $this->translate( $variable["Name"] ), $variable["VariableType"], $variableProfile, $variable["Position"], $variable["Active"] );
			}
		}

		public function RequestAction($Ident, $Value) 
        {			
		}

		public function ReceiveData($JSONString)
		{
			$data = json_decode($JSONString);

			$this->SendDebug("ReceiveData", $JSONString , 0);

			$variables = json_decode( $this->ReadPropertyString("Variables"), true);

			$Serial = $this->ReadPropertyString('Serial');

			$payload= utf8_decode($data->Payload);
			$topic = $data->Topic;
			$inverterTopic = $Serial.'/';


			if ( @$this->GetIDForIdent('dtu_status') ) 
			{

				if ( strpos( $topic, 'dtu/status' ) === 0 && $payload == "offline")
				{
					$this->SetValue( 'dtu_status', false);

					return;
				}	

				$this->SetValue( 'dtu_status', true);
			}


			if ( strpos( $topic, $inverterTopic) === 0)
			{
				$subTopic = str_replace( $inverterTopic, '', $topic);
				$ident = str_replace( '/', '_', $subTopic);

				if ( @$this->GetIDForIdent($ident) ) 
				{
					$this->SetValue( $ident, $payload);
				}
			}

		}

		public function GetConfigurationForm()
		{
			$form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);

			// Set variables configuration
			$variablesIndex = array_search( 'Variables', array_column( $form['elements'], 'name') );
			if ( $variablesIndex !== false)
			{
				$form['elements'][$variablesIndex]['values'] = $this->GetVariablesConfiguration();
			}

			return json_encode($form);
		}

		public function GetVariablesConfiguration()
		{
			// Get variables configuration
			$variablesConfiguration = json_decode( $this->ReadPropertyString("Variables"), true);

			// Get variables list template
			$variableList = $this->GetVariablesList();

			// Generate a new Variable List from template
			foreach ($variableList as $index => $newVariable)
			{
				$variableList[$index]['Name'] = $this->Translate($newVariable['Name']) ;
				
				// If configuration for variable exists, keep Active parameter
				$variablesIndex = array_search($newVariable['Ident'], array_column( $variablesConfiguration, 'Ident'));
				if ($variablesIndex !== false)
				{
					$variableList[$index]['Active']  = $variablesConfiguration[$variablesIndex]['Active'];
				}
			}
			
			return $variableList;
		}

		private function MQTTSend(string $Topic, string $Payload)
		{
			$Server['DataID'] = "{6F642E77-958C-6C58-2101-F142FD7836DA}"; // '{043EA491-0325-4ADD-8FC2-A30C8EEB4D3F}' <- MQTT receive GUID
			$Server['PacketType'] = 3;
			$Server['QualityOfService'] = 0;
			$Server['Retain'] = false;
			$Server['Topic'] = $Topic;
			$Server['Payload'] = $Payload;
			$ServerJSON = json_encode($Server, JSON_UNESCAPED_SLASHES);
			$ServerJSON = json_encode($Server);
			$this->SendDebug(__FUNCTION__ . 'MQTT Server', $ServerJSON, 0);
			$resultServer = @$this->SendDataToParent($ServerJSON);
		}

		private function GetVariablesList()
		{
	
			$file = __DIR__ . "/../libs/variables_microinverter.json";
			if (is_file($file))
			{
				$data = json_decode(file_get_contents($file), true);
			}
			else
			{
				$data = array();
			}
	
			return $data;
		}

		private function MigrateSplitter()
		{
			$parent = IPS_GetInstance($this->InstanceID)['ConnectionID'];

			if ( $parent != 0 && IPS_GetInstance($parent)['ModuleInfo']['ModuleID'] != '{40505457-AB2C-057B-C9D7-657EBB53A528}')
			{
				$BaseTopic = $this->ReadPropertyString('BaseTopic');

				IPS_DisconnectInstance($this->InstanceID);
				$newParent = 0;
				$dtus = IPS_GetInstanceListByModuleID('{40505457-AB2C-057B-C9D7-657EBB53A528}');
				foreach( $dtus as $dtu)
				{
					if ( IPS_GetProperty( $dtu, 'BaseTopic') == $BaseTopic)
					{
						$newParent = $dtu;
					}
				}
				if ($newParent !== 0)
				{
					IPS_ConnectInstance($this->InstanceID, $newParent);
				}
				else
				{	
					// Create OpenDTU Splitter
					$dtu = IPS_CreateInstance ('{40505457-AB2C-057B-C9D7-657EBB53A528}');
					IPS_SetName($dtu, 'OpenDTU');
					//Connect OpenDTU to MQTT-Server
					IPS_DisconnectInstance($dtu);
					IPS_ConnectInstance($dtu, $parent);
					// Set BaseTopic
					IPS_SetProperty($dtu, 'BaseTopic', $BaseTopic);
					IPS_ApplyChanges($dtu);
					// Connect this instance to OpenDTU
					IPS_ConnectInstance($this->InstanceID, $dtu);
				}
			}
		}

		protected function RegisterProfile($VarTyp, $Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize, $Digits = 0)
		{
			if (!IPS_VariableProfileExists($Name)) {
				IPS_CreateVariableProfile($Name, $VarTyp);
			} else {
				$profile = IPS_GetVariableProfile($Name);
				if ($profile['ProfileType'] != $VarTyp) {
					throw new \Exception('Variable profile type does not match for profile ' . $Name, E_USER_WARNING);
				}
			}
	
			IPS_SetVariableProfileIcon($Name, $Icon);
			IPS_SetVariableProfileText($Name, $Prefix, $Suffix);
			switch ($VarTyp) {
				case VARIABLETYPE_FLOAT:
					IPS_SetVariableProfileDigits($Name, $Digits);
					// no break
				case VARIABLETYPE_INTEGER:
					IPS_SetVariableProfileValues($Name, $MinValue, $MaxValue, $StepSize);
					break;
			}
		}
	}