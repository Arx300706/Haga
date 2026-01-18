import com.sun.net.httpserver.HttpServer;
import com.sun.net.httpserver.HttpHandler;
import com.sun.net.httpserver.HttpExchange;

import java.io.*;
import java.net.InetSocketAddress;
import java.nio.file.Files;
import java.util.stream.Collectors;

public class DnsWebServer {

    public static void main(String[] args) throws Exception {
        HttpServer server = HttpServer.create(new InetSocketAddress(8080), 0);
        server.createContext("/", new RootHandler());
        server.setExecutor(null);
        server.start();

        System.out.println("Serveur Web démarré sur le port 8080");
    }

    static class RootHandler implements HttpHandler {
        @Override
        public void handle(HttpExchange exchange) throws IOException {

            String bindStatus = CommandExecutor.execute("systemctl is-active bind9").trim();
            boolean isRunning = bindStatus.equals("active");

            String zones = ZoneManager.getZones();

            String html = """
                <!DOCTYPE html>
                <html lang="fr">
                <head>
                    <meta charset="UTF-8">
                    <title>État Serveur DNS</title>
                    <style>
                        body { font-family: Arial; background: #f4f4f4; padding: 20px; }
                        .box { background: white; padding: 20px; border-radius: 8px; }
                        .ok { color: green; font-weight: bold; }
                        .ko { color: red; font-weight: bold; }
                        pre { background: #eee; padding: 10px; }
                    </style>
                </head>
                <body>
                    <div class="box">
                        <h1>Supervision Serveur DNS</h1>
                        <p>État du serveur BIND9 :
                            <span class="%s">%s</span>
                        </p>

                        <h2>Domaines (zones DNS)</h2>
                        <pre>%s</pre>
                    </div>
                </body>
                </html>
            """.formatted(
                    isRunning ? "ok" : "ko",
                    isRunning ? "SERVEUR ALLUMÉ" : "SERVEUR ÉTEINT",
                    zones
            );

            exchange.sendResponseHeaders(200, html.getBytes().length);
            OutputStream os = exchange.getResponseBody();
            os.write(html.getBytes());
            os.close();
        }
    }
}
