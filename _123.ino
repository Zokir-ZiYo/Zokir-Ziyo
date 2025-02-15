#include <SPI.h>
#include <Ethernet2.h>

// Ethernet interfeysini sozlash
int buttonState = 0; // Tugma holatini saqlash
byte mac[] = { 0xDE, 0xAD, 0xBE, 0xEF, 0xFE, 0xED }; // MAC manzilingiz
IPAddress ip(172, 17, 39, 178); // IP manzilingiz
EthernetServer server(80); // Serverni port 80da ishlashga sozlash (HTTP uchun)

void setup() {
  Serial.begin(9600); // Serial port orqali aloqani boshlash
  while (!Serial) {
    ; // Serial portga ulanishni kutish
  }

  Ethernet.begin(mac, ip); // Ethernetni boshlash
  server.begin(); // Serverni ishga tushurish
  pinMode(6, INPUT); // Pin 6 ni kirish (input) sifatida belgilash
}
 
void loop() {
  // Tarmoqdagi mijozdan so'rovni kutish
  EthernetClient client = server.available();
  
  if (client) {
    boolean currentLineIsBlank = true;
    while (client.connected()) {
      if (client.available()) {
        char c = client.read();

        // HTML javobini yuborish
        if (c == '\n' && currentLineIsBlank) {
          // HTTP header va HTML kontentini yuborish
        client.println("HTTP/1.1 200 OK");
            client.println("Content-Type: text/html");
            client.println();
            client.println("<!DOCTYPE HTML>");
            client.println("<html>");
            client.println("<head>");
            client.println("<title>Arduino Web Server</title>");
            client.println("<meta name='viewport' content='width=device-width, initial-scale=1'>");
            client.println("<link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css'>");
            client.println("<script src='https://cdn.jsdelivr.net/npm/chart.js'></script>");
            client.println("<style>");
            client.println("body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f9; }");
            client.println("header { background-color: #333; color: white; text-align: center; padding: 20px; }");
            client.println("h1 { margin: 0; font-size: 36px; }");
            client.println("nav { background-color: #444; padding: 10px; text-align: center; }");
            client.println("nav a { color: white; margin: 10px; text-decoration: none; font-size: 18px; }");
            client.println("main { padding: 20px; text-align: center; }");
            client.println("footer { background-color: #333; color: white; text-align: center; padding: 10px; position: fixed; bottom: 0; width: 100%; }");
            client.println("canvas { width: 100%; height: 400px; margin-top: 30px; }");
            client.println("@media (max-width: 600px) { h1 { font-size: 28px; } }");
            client.println("</style>");
            client.println("</head>");
            client.println("<body>");
            
            // Header
            client.println("<header>");
            client.println("<h1>Welcome to the Arduino Web Server</h1>");
            client.println("</header>");
            
            // Navigation Bar
            client.println("<nav>");
            client.println("<a href='#'>Home</a>");
            client.println("<a href='#'>About</a>");
            client.println("<a href='#'>Contact</a>");
            client.println("</nav>");
            
            
            
            // Footer
            client.println("<footer>");
            client.println("<p>&copy; 2025 Arduino Web Server | All Rights Reserved</p>");
            client.println("</footer>");
            
            client.println("</body>");
            client.println("</html>");
            break;
        }
        
        // So'rovning qismiy qatorlarini o'qish
        if (c == '\n') {
          currentLineIsBlank = true;
        } 
        else if (c != '\r') {
          currentLineIsBlank = false;
        }
      }
    }
    // Mijozni to'xtatish
    client.stop();
  }
}

