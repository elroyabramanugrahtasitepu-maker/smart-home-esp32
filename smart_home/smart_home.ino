/***************************************************
* Testing Smart Home ESP32 & WiFi Connection
* Board   : ESP32 DEVKIT
* Input   : MQ2, DHT22, Magnet Sensor, Push Button
* Output  : LED, Buzzer, Motor DC, Servo Pintu
***************************************************/
#include <LiquidCrystal_I2C.h>
#include <DHT.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h> 
#include <WebServer.h>   // Library tambahan untuk WebServer ESP32-CAM
#include <ESP32Servo.h>  // Library tambahan untuk Motor Servo

#define DHTPIN 4
#define DHTTYPE DHT22
#define Lampu_depan 14
#define Lampu_tidur 27
#define Lampu_tengah 26
#define Lampu_belakang 25
#define Kipas_angin 33
#define Bell 32
#define MQ2_SENSOR  16
#define Magnet 17
#define SERVO_PIN 18     // Pin untuk Motor Servo Pintu
#define BUTTON_PIN 21    // Pin untuk Push Button (GND)

// === KREDENSIAL WIFI ===
const char* ssid = "ESP_TEST";
const char* password = "enakbangetcok";

// === URL PHP BACKEND ===
String serverName = "http://10.122.18.44/smarthome/api.php"; 

DHT dht(DHTPIN, DHTTYPE);
LiquidCrystal_I2C lcd(0x27, 16, 2);

// === INISIALISASI WEB SERVER & SERVO ===
WebServer server(80); 
Servo pintuServo;

boolean st, fg, fm;
int humi, temp, u, j;
int MQ2_SENSOR_Value = 0;
unsigned long timerKipas = 0;
bool statusKipas = false;

// STATUS TOGGLE SERVO (false = TUTUP, true = BUKA)
bool statusPintuServo = false; 
int servoManual = 0;

// === VARIABEL UNTUK MENGINGAT STATUS (DARURAT GAS) ===
bool gas_was_detected = false;
int last_lampu_depan = 0;
int last_lampu_tidur = 0;
int last_lampu_tengah = 0;
int last_lampu_belakang = 0;
int last_kipas_angin = 0;

// === VARIABEL DEBOUNCE PUSH BUTTON ===
int buttonState = HIGH;
int lastButtonState = HIGH;
unsigned long lastDebounceTime = 0;
unsigned long debounceDelay = 50; 

// === VARIABEL TIMER HTTP (NON-BLOCKING / TANPA DELAY) ===
unsigned long previousMillisHTTP = 0;
const long intervalHTTP = 1000; // Kirim/baca database setiap 1 detik agar tombol tidak lag

//=====================================
void read_DHT22(){
  humi = dht.readHumidity();
  temp = dht.readTemperature();
  if (isnan(humi) || isnan(temp)) {
    lcd.setCursor(3,1);
    lcd.print("--");
    lcd.setCursor(10,1);
    lcd.print("--");
    return;
  }
  else {
    lcd.setCursor(3,1);
    lcd.print(temp);
    lcd.setCursor(10,1);
    lcd.print(humi);
  }
}

//=====================================
void setup()
{   
  Serial.begin(115200);
  
  pinMode(Magnet, INPUT_PULLUP);
  pinMode(MQ2_SENSOR, INPUT);
  pinMode(BUTTON_PIN, INPUT_PULLUP); // Push Button terhubung ke D21 & GND
  
  pinMode(Lampu_depan, OUTPUT);
  pinMode(Lampu_tidur, OUTPUT);
  pinMode(Lampu_tengah, OUTPUT);
  pinMode(Lampu_belakang, OUTPUT);
  pinMode(Kipas_angin, OUTPUT);
  pinMode(Bell, OUTPUT);
  
  // Setup Motor Servo Pintu
  pintuServo.attach(SERVO_PIN);
  pintuServo.write(0); // Posisi awal pintu tertutup (0 derajat)
  statusPintuServo = false;

  lcd.begin(16,2); 
  lcd.init();  
  lcd.backlight(); 
  
  // === PROSES KONEKSI WIFI ===
  lcd.setCursor(0, 0);
  lcd.print("Connecting WiFi");
  Serial.print("Connecting to ");
  Serial.println(ssid);
  
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
    lcd.setCursor(0, 1);
    lcd.print("Wait...");
  }
  
  Serial.println("");
  Serial.println("WiFi connected.");
  Serial.print("IP address: ");
  Serial.println(WiFi.localIP());
  
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("WiFi Connected!");
  delay(2000);
  
  // === RUTE HTTP UNTUK ESP32-CAM ===
  server.on("/buka_pintu", []() {
    Serial.println("Sinyal dari ESP-CAM: WAJAH DIKENALI!");
    lcd.clear(); 
    lcd.setCursor(0,0); lcd.print(" WAJAH DIKENALI ");
    
    pintuServo.write(90); // Servo Buka
    statusPintuServo = true; // Tandai akses sah
    
    server.send(200, "text/plain", "Pintu Dibuka");
  });

  server.on("/tolak_akses", []() {
    Serial.println("Sinyal dari ESP-CAM: AKSES DITOLAK!");
    lcd.clear(); 
    lcd.setCursor(0,0); lcd.print(" AKSES DITOLAK! ");
    
    for (int i = 0; i < 3; i++) {
      digitalWrite(Bell, HIGH); delay(300);
      digitalWrite(Bell, LOW); delay(300);
    }
    server.send(200, "text/plain", "Buzzer Bunyi");
    lcd.clear();
  });

  server.begin(); 
  Serial.println("HTTP Server Mulai!");

  // ===================================
  // TES HARDWARE AWAL
  // ===================================
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print(" Testing Fungsi ");
  lcd.setCursor(0, 1);
  lcd.print(" = Smart Home = ");
  dht.begin();
  delay(2000);
  lcd.clear(); lcd.print("FINISH TEST....");
  delay(1000);
}

//=====================================
void loop()
{
  // Menerima request dari ESP32-CAM secara real-time
  server.handleClient(); 

  // 1. BACA SENSOR GAS & MAGNET
  int status_gas = (digitalRead(MQ2_SENSOR) == LOW) ? 1 : 0; 
  bool pintu_terbuka = (digitalRead(Magnet) == HIGH); // HIGH = Magnet terpisah (Pintu Terbuka Fisik)
  
  // ==========================================================
  // FITUR KESELAMATAN DARURAT GAS & KEAMANAN
  // ==========================================================
  if (status_gas == 1) {
      if (!gas_was_detected) {
          last_lampu_depan = digitalRead(Lampu_depan);
          last_lampu_tidur = digitalRead(Lampu_tidur);
          last_lampu_tengah = digitalRead(Lampu_tengah);
          last_lampu_belakang = digitalRead(Lampu_belakang);
          last_kipas_angin = digitalRead(Kipas_angin);
          gas_was_detected = true;
          Serial.println("!! BAHAYA ASAP/GAS !! Mematikan listrik & MEMBUKA PINTU.");
      }
      
      digitalWrite(Lampu_depan, LOW);
      digitalWrite(Lampu_tidur, LOW);
      digitalWrite(Lampu_tengah, LOW);
      digitalWrite(Lampu_belakang, LOW);
      digitalWrite(Kipas_angin, LOW);
      digitalWrite(Bell, HIGH);
      
      pintuServo.write(90);      
      statusPintuServo = true;
      
      timerKipas = 0;
      statusKipas = false;
      
  } else {
      // --- KONDISI NORMAL ---
      
      // 1. BACA PUSH BUTTON FULL MANUAL TOGGLE (Paling diprioritaskan)
      int reading = digitalRead(BUTTON_PIN);
      if (reading != lastButtonState) {
          lastDebounceTime = millis();
      }
      
      if ((millis() - lastDebounceTime) > debounceDelay) {
          if (reading != buttonState) {
              buttonState = reading;
              
              // Saat tombol ditekan (Transisi ke LOW)
              if (buttonState == LOW) {
                  statusPintuServo = !statusPintuServo; // Balikkan status (Toggle)
                  servoManual = 0; // OVERRIDE: Paksa reset nilai dari database agar tombol tidak dilawan
                  
                  if (statusPintuServo) {
                      pintuServo.write(90); // Tekan 1x -> Servo BUKA (90°) & Pintu Resmi Dibuka
                      Serial.println("Tombol Ditekan: Servo BUKA (Alarm Magnet Nonaktif)");
                  } else {
                      pintuServo.write(0);  // Tekan 1x lagi -> Servo TUTUP (0°) & Kunci Aktif
                      Serial.println("Tombol Ditekan: Servo TUTUP (Alarm Magnet Siaga)");
                  }
              }
          }
      }
      lastButtonState = reading;

      // 2. KONTROL DARI WEBSITE (Hanya jalan jika tombol tidak dipakai/di-override)
      if (servoManual == 1 && statusPintuServo == true) {
          pintuServo.write(0);
          statusPintuServo = false;
          Serial.println("Website: Pintu Dikunci Paksa!");
      }

      // 3. ALARM MAGNET PINTU 
      // Alarm HANYA bunyi jika pintu terbuka fisik (Magnet HIGH) TANPA ada akses sah (!statusPintuServo)
      if (pintu_terbuka && !statusPintuServo) {
          digitalWrite(Bell, HIGH); // Pintu dibuka paksa / tanpa tombol!
      } else {
          digitalWrite(Bell, LOW);  // Aman (Akses sah atau pintu memang tertutup)
      }

      // --- PEMULIHAN DARI KONDISI DARURAT GAS ---
      if (gas_was_detected) {
          Serial.println("== AMAN == Mengembalikan perangkat & menutup pintu kembali.");
          digitalWrite(Lampu_depan, last_lampu_depan);
          digitalWrite(Lampu_tidur, last_lampu_tidur);
          digitalWrite(Lampu_tengah, last_lampu_tengah);
          digitalWrite(Lampu_belakang, last_lampu_belakang);
          digitalWrite(Kipas_angin, last_kipas_angin);
          
          pintuServo.write(0);
          statusPintuServo = false;
          gas_was_detected = false; 
      }
  }
  // ==========================================================

  // 2. KOMUNIKASI DATABASE (NON-BLOCKING)
  if(WiFi.status() == WL_CONNECTED){
      unsigned long currentMillis = millis();
      
      if (currentMillis - previousMillisHTTP >= intervalHTTP) {
          previousMillisHTTP = currentMillis;

          HTTPClient http;
          humi = dht.readHumidity();
          temp = dht.readTemperature();
          if (isnan(humi) || isnan(temp)) { humi = 0; temp = 0; } 
          
          int status_pintu = (digitalRead(Magnet) == HIGH) ? 1 : 0; 
          
          String url = serverName + "?suhu=" + String(temp) + 
                       "&humi=" + String(humi) + 
                       "&pintu=" + String(status_pintu) + 
                       "&gas=" + String(status_gas);
                       
          http.begin(url);
          int httpResponseCode = http.GET(); 
          
          if (httpResponseCode > 0) {
              String payload = http.getString();
              DynamicJsonDocument doc(1024);
              DeserializationError error = deserializeJson(doc, payload);

              if (!error) {
                  // Hanya terima update lampu/kipas jika tidak ada gas darurat
                  if (status_gas == 0) {
                      servoManual = doc["servo"].as<int>(); 
                      
                      digitalWrite(Lampu_depan, doc["lampu_depan"].as<int>());
                      digitalWrite(Lampu_tidur, doc["lampu_tidur"].as<int>());
                      digitalWrite(Lampu_tengah, doc["lampu_tengah"].as<int>());
                      digitalWrite(Lampu_belakang, doc["lampu_belakang"].as<int>());

                      if (temp >= 35) {
                          digitalWrite(Kipas_angin, HIGH);
                          timerKipas = millis(); 
                          statusKipas = true;
                      } 
                      else {
                          int interval = doc["kipas_interval"].as<int>() * 60000UL;

                          if (doc["kipas_auto"].as<int>() == 1) {
                              if (timerKipas == 0) {
                                  timerKipas = millis();
                                  statusKipas = true;
                                  digitalWrite(Kipas_angin, HIGH);
                              }
                              if (millis() - timerKipas >= interval) {
                                  timerKipas = millis();
                                  statusKipas = !statusKipas;
                                  digitalWrite(Kipas_angin, statusKipas);
                              }
                          } else {
                              timerKipas = 0;
                              statusKipas = false;
                              digitalWrite(Kipas_angin, doc["kipas_angin"].as<int>());
                          }
                      }
                  } 
              } 
          } 
          http.end();
      }
  }
}