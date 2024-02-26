<?php
	class genericMQTT_IPS_module extends IPSModule
	{
		public function Create()
		{
			//Never delete this line!
			parent::Create();			

			$this->RegisterPropertyString('MQTTBaseTopic', 'please set a base topic');
            $this->RegisterPropertyString('Variables', '[]');		
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

			//Connect to MQTT Server
			$this->ConnectParent('{C6D2AEB3-6E1F-4B2E-8E69-3A1A00246850}');		

			//Set Filter for ReceiveData
			$baseTopic = $this->ReadPropertyString('MQTTBaseTopic');
			$filter = '.*(' . preg_quote($baseTopic) . ').*';
			$this->SetReceiveDataFilter($filter);			


			$this->SendDebug('ApplyChanges: setFilter: ', $filter, 0);

			//Create variables from configuration file
			$variables = $this->GetVariablesConfiguration();

			foreach($variables as $variable)
			{
				$variableProfile = $variable["VariableProfile"];
				$this->MaintainVariable ($variable["Ident"], $this->translate( $variable["Name"] ), $variable["VariableType"], $variableProfile, $variable["Position"], $variable["IsActive"] );
				
				if ($variable["IsSettable"] === true) {
					@$this->EnableAction($variable["Ident"]);
				}
			}
		}

		public function RequestAction($Ident, $Value) 
        {
			$this->SendDebug("Request action", "Change ". $Ident ." to '". $Value. "'", 0);			
		}

		public function ReceiveData($JSONString)
		{
			$data = json_decode($JSONString);
			$this->SendDebug("ReceiveData", $JSONString , 0);
			$baseTopic = $this->ReadPropertyString('MQTTBaseTopic');

			$payload = mb_convert_encoding($data->Payload, 'UTF-8');
			$dataTopic = $data->Topic;
			$baseTopic = $baseTopic.'/';	

			if ( strpos( $dataTopic, $baseTopic) === 0)
			{
				$subTopic = str_replace($baseTopic, '', $dataTopic);
				$ident = str_replace( '/', '_', $subTopic);

				if ( @$this->GetIDForIdent($ident)) 
				{
					$this->SendDebug("ReceiveData ", "Set variable ". $ident ." to ". $payload , 0);
					$this->SetValue($ident, $payload);
				}
			}
		}

		public function GetConfigurationForm()
		{
			$pathToFormFile = $this->ReadPropertyString("PathToFormFile");
			$form = json_decode(file_get_contents($pathToFormFile), true);

			// Set variables configuration
			$variablesIndex = array_search('Variables', array_column( $form['elements'], 'name'));
			if ( $variablesIndex !== false)
			{
				$form['elements'][$variablesIndex]['values'] = $this->GetVariablesConfiguration();
			}

			return json_encode($form);
		}

		public function GetVariablesConfiguration()
		{
			// Get variables configuration
			$variablesConfiguration = json_decode($this->ReadPropertyString("Variables"), true);
			$pathToConfigurationFile = $this->ReadPropertyString("PathToConfigurationFile");
		

			// Get variables list template
			$variablesList = $this->GetVariablesListFromFile($pathToConfigurationFile);

			// Generate a new Variable List from template
			foreach ($variablesList as $index => $newVariable)
			{
				$variablesList[$index]['Name'] = $this->Translate($newVariable['Name']) ;
				
				// If configuration for variable exists, keep Active parameter
				$variablesIndex = array_search($newVariable['Ident'], array_column( $variablesConfiguration, 'Ident'));
				if ($variablesIndex !== false)
				{
					$variablesList[$index]['IsActive']  = $variablesConfiguration[$variablesIndex]['IsActive'];
				}
			}
			return $variablesList;
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

		private function GetVariablesListFromFile($filepath)
		{			
			if (is_file($filepath))
			{
				$data = json_decode(file_get_contents($filepath), true);
			}
			else
			{
				$data = array();
			}
	
			return $data;
		}

		protected function RegisterProfile($VarTyp, $Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize, $Digits = 0)
		{
			if (!IPS_VariableProfileExists($Name)) 
			{
				if (!IPS_CreateVariableProfile($Name, $VarTyp)) 
				{
					$this->LogMessage('Could not create variable profile for variable: ' .$Name, KL_ERROR);
					return false;
				}
			} else {
				$profile = IPS_GetVariableProfile($Name);
				if ($profile['ProfileType'] != $VarTyp) 
				{
					$this->LogMessage('Variable profile type does not match for profile ' .$Name, KL_ERROR);
					return false;
				}
			}

			$wasSuccessful = true;
			$wasSuccessful &= IPS_SetVariableProfileIcon($Name, $Icon);
			$wasSuccessful &= IPS_SetVariableProfileText($Name, $Prefix, $Suffix);
			switch ($VarTyp) {
				case VARIABLETYPE_FLOAT:
					$wasSuccessful &= IPS_SetVariableProfileDigits($Name, $Digits);
					// no break
				case VARIABLETYPE_INTEGER:
					$wasSuccessful &= IPS_SetVariableProfileValues($Name, $MinValue, $MaxValue, $StepSize);
					break;
			}
			return $wasSuccessful;
		}
	}