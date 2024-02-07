
# Hoymiles Mikrowechselrichter mit AhoyDTU

Modul für IP-Symcon zur Integration der Hoymiles Mikrowechselrichter für Photovoltaik-Module über  AhoyDTU. 

AhoyDTU ist eine Firmware für den ESP8266 und ESP32 und bildet zusamen mit einem NRF24L01+ bzw. CMT2300A Funkmodul ein Gateway zur Kommunikation mit den Wechselrichtern. AhoyDTU stellt ein Webinterface zur Konfiguration und zum Auslesen der Wechselrichter zur Verfügung und kann mittels MQTT in andere Systeme eingebunden werden. Weitere Infos zum Bau und Einrichtung von AhoyDTU gibt es im  [AhoyDTU GitHub-Repository](https://github.com/lumapu/ahoy/tree/main).

# Danksagung
Diese Bibliothek besteht zu überwiegenden Teilen aus Code und Ideen der IPSymcon Bibliotheken ["HoymilesOpenDTU"](https://github.com/roastedelectrons/HoymilesOpenDTU) von Tobias Ohrdes und ["IPS-Zigbee2MQTT"](https://github.com/Schnittcher/IPS-Zigbee2MQTT) von Kai Schnittcher. Danke für die Inspiration und Eure tolle Arbeit!

### Inhaltsverzeichnis

1. [Voraussetzungen](#1-voraussetzungen)
2. [Enthaltene Module](#2-enthaltene-module)
3. [Software-Installation](#3-software-installation)
4. [Einrichtung in IP-Symcon](#4-einrichtung-in-ip-symcon)
5. [Einrichtung in AhoyDTU](#5-einrichtung-in-ahoydtu)
6. [Changelog](#6-changelog)
7. [Lizenz](#7-lizenz)


### 1. Voraussetzungen

- IP-Symcon ab Version 6.0
- AhoyDTU ([Dokumentation](https://github.com/lumapu/ahoy/tree/main))
- Hoymiles Modulwechselrichter

### 2. Enthaltene Module

- __Hoymiles Microinverter__ ([Dokumentation](MicroinverterOfAhoyDTU))  
	Das Modul stellt alle Daten der Hoymiles Modulwechselrichter, die an einem AhoyDTU Gateway angemeldet sind in IP-Symcon bereit. 

- __AhoyDTU_device__ ([Dokumentation](AhoyDTU_device))  
	Das Modul stellt die Betriebsdaten der AhoyDTU in IP-Symcon bereit. 

- __AhoyDTU Configurator__ ([Dokumentation](AhoyDTUConfigurator))  
	Gibt es aktuell noch nicht. Wenn ich Zeit finde kommt der vlt. noch nach

### 3. Software-Installation

Über den Module Store das 'IPSymcon_AhoyDTU_Modul'-Modul installieren.

### 4. Einrichtung in IP-Symcon

*Hinweis: Die Einrichtung sollte erfolgen, wenn der Wechselrichter eingeschaltet ist (es liegt eine ausreichende DC-Spannung am Modul-Eingang an), da nur dann alle notwendigen Daten von AhoyDTU bereitgestellt werden.*

Vor der Einrichtung in IP-Symcon sollten die MQTT-Einstellungen in AhoyDTU vorgenommen werden.

### 5. Einrichtung in AhoyDTU

Im Webinterface der AhoyDTU müssen unter *Settings->MQTT* die folgenden *MQTT Broker Parameter* angepasst werden:
- **Hostname**: IP oder Hostname des IP-Symcon Servers
- **Port**: Port des IP-Symcon MQTT-Servers
- **Topic**: Dieser Wert kann beliebig gesetzt werden und muss in IP-Symcon in der *AhoyDTU Instanz*-Instanz eingetragen werden.

Im Webinterface der AhoyDTU musseunter *Settings->Inverter* dem Inverter ein Name gegeben werden
- **Name**: Name des Inverters und gleichzeitig das Subtopic für MQTT. Dieser Name muss zusammen mit den AhoyDTU topic in der *Microinverter AhoyDTU Instanz* in der form "ahoyDTU topic" Schrägstrich "Inververname" eingetragen werden.

Beispiel: Das AhoyDTU Topic lautet "MeineAhoyDTU" und der Inverter hat den Namen "HoymilesInverter" so ergibt sich für die *Microinverter AhoyDTU Instanz* das Topic ""MeineAhoyDTU/HoymilesInverter"


### 6. Changelog
Version 0.9.0 (2023-02-07)
* initiale Version zum Testen

### 7. Lizenz
MIT License

Copyright (c) 2023 Tobias Schade

