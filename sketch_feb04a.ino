#include <SPI.h>
#include <Ethernet2.h>
#include <EthernetClient.h>
#include <SSLClient.h>

// Ethernet konfiguratsiyasi
byte mac[] = { 0xDE, 0xAD, 0xBE, 0xEF, 0xFE, 0xED };  // MAC manzilingiz
IPAddress ip(172, 17, 39, 177);  // Statik IP manzili (o'zingizga moslashtiring)

// Server ma'lumotlari
const char* host = "firesecurity.ngmk.uz";  // Server domeni
const int httpsPort = 443;  // HTTPS port

SSLClient client;  // SSL qo'llab-quvvatlash uchun SSLClient obyekti

void setup() {
  // Serial monitorni ishga tushirish
  Serial.begin(9600);

  // Ethernet ulanishini boshlash
  Ethernet.begin(mac, ip);
  delay(1000);  // Tarmoqning to'liq ulanishini kutish

  Serial.println("Ethernetga ulanish...");

  // IP manzilingizni tekshirish
  Serial.print("IP manzilingiz: ");
  Serial.println(Ethernet.localIP());

  // HTTPS serverga ulanish
  Serial.println("Serverga ulanish...");
  if (client.connect(host, httpsPort)) {
    Serial.println("Ulanish muvaffaqiyatli.");
    
    // HTTP so'rov yuborish
    client.println("POST /sav.php HTTP/1.1");
    client.println("Host: firesecurity.ngmk.uz");
    client.println("Content-Type: application/x-www-form-urlencoded");
    
    String postData = "username=Arduino&password=12345";  // Yuboriladigan ma'lumotlar
    client.print("Content-Length: ");
    client.println(postData.length());
    client.println();
    client.println(postData);  // Ma'lumot yuborish

    Serial.println("POST so'rovi yuborildi.");
  } else {
    Serial.println("Serverga ulanishda xato.");
  }
}

void loop() {
  // So'rov yuborilgandan keyin serverdan javob olish
  if (client.connected()) {
    while (client.available()) {
      char c = client.read();
      Serial.print(c);
    }
  }

  // Ulanishni yopish
  if (!client.connected()) {
    Serial.println();
    Serial.println("Ulanish yopildi.");
    client.stop();
  }
}

