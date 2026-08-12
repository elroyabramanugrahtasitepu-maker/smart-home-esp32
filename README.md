# Smart Home ESP32

A **Smart Home system based on ESP32** designed to control and monitor various household devices using IoT technology, Wi-Fi connectivity, sensors, and a web-based control system.

## 📌 Description

This project is an implementation of **Internet of Things (IoT)** using ESP32 to control household devices such as lights and fans while monitoring environmental conditions using sensors.

The system communicates with a **web server and REST API**, allowing devices to be monitored and controlled remotely through a web interface.

## ⚙️ Features

* Control multiple lights using ESP32
* Automatic fan control
* Gas detection using MQ-2 sensor
* Door monitoring using a magnetic sensor
* Buzzer alarm system
* ESP32 communication with REST API
* Web-based device control
* Real-time device status monitoring

## 🧰 Hardware

* ESP32 DevKit
* MQ-2 Gas Sensor
* Magnetic Sensor
* Relay Module
* Lights
* Fan
* Buzzer
* Jumper Wires
* Power Supply

## 💻 Software & Technologies

* Arduino IDE
* C/C++
* ESP32
* PHP
* MySQL
* REST API
* HTML
* CSS
* JavaScript

## 🔄 How It Works

1. The ESP32 connects to a Wi-Fi network.
2. The ESP32 retrieves device status from the REST API.
3. Commands from the web interface are sent to the server.
4. The ESP32 retrieves and processes the commands.
5. The ESP32 controls the connected devices according to the received commands.
6. The MQ-2 and magnetic sensors monitor environmental conditions.
7. When specific conditions are detected, the system can automatically activate the fan or buzzer.

## 📁 Project Structure

```text
smart-home-esp32/
├── smart-home-esp32.ino
├── api/
│   ├── get_status.php
│   └── update_device.php
├── web/
│   └── control-center/
└── README.md
```

## 🎯 Project Objectives

The main objective of this project is to implement **IoT and Embedded System technology** to create a smart home system that can be monitored and controlled through a network.

## 🚀 Future Development

Possible future improvements include:

* ESP32-CAM integration
* Face recognition for smart door access
* Servo-based smart door
* Advanced monitoring dashboard
* Real-time notifications
* Mobile application integration

## 👨‍💻 Author

**El Roy Abram Anugrahta Sitepu**

Computer Systems Engineering
Universitas AKPRIND Indonesia
