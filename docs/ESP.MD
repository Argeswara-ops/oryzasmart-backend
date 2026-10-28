#include <WiFi.h>
#include <PubSubClient.h>
#include "DHT.h"

// =====================================================
// KONFIGURASI WIFI
// =====================================================
const char* WIFI_SSID = "NAMA_WIFI_KAMU";
const char* WIFI_PASSWORD = "PASSWORD_WIFI_KAMU";

// =====================================================
// KONFIGURASI MQTT
// =====================================================
const char* MQTT_HOST = "broker.hivemq.com";
const uint16_t MQTT_PORT = 1883;

const char* DEVICE_ID = "ESP32-01";

const char* SENSOR_TOPIC =
    "oryza-smart/ESP32-01/sensors";

const char* STATUS_TOPIC =
    "oryza-smart/ESP32-01/status";

// =====================================================
// KONFIGURASI SENSOR
// =====================================================
#define DHTPIN   4
#define DHTTYPE  DHT11

const unsigned long INTERVAL_SENSOR = 5000;  // ms

// =====================================================
// OBJEK & VARIABEL
// =====================================================
DHT dht(DHTPIN, DHTTYPE);
WiFiClient wifiClient;
PubSubClient mqtt(wifiClient);

unsigned long lastSensor = 0;
unsigned long lastReconnect = 0;

// =====================================================
// WIFI & MQTT
// =====================================================
void connectWiFi() {
  Serial.print("Menyambung WiFi");
  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);

  int tries = 0;
  while (WiFi.status() != WL_CONNECTED && tries < 40) {
    delay(500);
    Serial.print(".");
    tries++;
  }

  if (WiFi.status() == WL_CONNECTED) {
    Serial.print("\nWiFi tersambung, IP: ");
    Serial.println(WiFi.localIP());
  } else {
    Serial.println("\nWiFi gagal, akan dicoba lagi");
  }
}

bool connectMQTT() {
  String clientId = String(DEVICE_ID) + "-" + String((uint32_t)ESP.getEfuseMac(), HEX);

  // Last Will: otomatis kirim "offline" kalau perangkat putus
  if (mqtt.connect(clientId.c_str(), NULL, NULL, STATUS_TOPIC, 1, true, "offline")) {
    Serial.println("MQTT tersambung");
    mqtt.publish(STATUS_TOPIC, "online", true);
    return true;
  }

  Serial.print("MQTT gagal, rc=");
  Serial.println(mqtt.state());
  return false;
}

// =====================================================
// BACA & KIRIM SENSOR
// =====================================================
void publishSensor() {
  float h = dht.readHumidity();
  float t = dht.readTemperature();

  if (isnan(h) || isnan(t)) {
    Serial.println("Gagal membaca DHT11");
    return;
  }

  char payload[128];
  snprintf(payload, sizeof(payload),
           "{\"device\":\"%s\",\"temperature\":%.1f,\"humidity\":%.1f}",
           DEVICE_ID, t, h);

  Serial.print("Suhu: ");
  Serial.print(t);
  Serial.print(" C | Kelembapan: ");
  Serial.print(h);
  Serial.println(" %");

  mqtt.publish(SENSOR_TOPIC, payload);
}

// =====================================================
// SETUP & LOOP
// =====================================================
void setup() {
  Serial.begin(115200);
  dht.begin();

  connectWiFi();
  mqtt.setServer(MQTT_HOST, MQTT_PORT);
}

void loop() {
  if (WiFi.status() != WL_CONNECTED) {
    connectWiFi();
  }

  if (WiFi.status() == WL_CONNECTED && !mqtt.connected()) {
    if (millis() - lastReconnect > 5000) {
      lastReconnect = millis();
      connectMQTT();
    }
  }

  mqtt.loop();

  if (mqtt.connected() && millis() - lastSensor >= INTERVAL_SENSOR) {
    lastSensor = millis();
    publishSensor();
  }
}
