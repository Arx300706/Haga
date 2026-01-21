import com.sun.net.httpserver.HttpServer;
import java.io.File;
import java.nio.file.Files;
import java.net.InetSocketAddress;

public class DnsWebServer {
    public static void main(String[] args) throws Exception {
        HttpServer server = HttpServer.create(new InetSocketAddress(8080), 0);

        // /status
        server.createContext("/status", exchange -> {
            String status = BindService.getStatus();
            exchange.sendResponseHeaders(200, status.getBytes().length);
            exchange.getResponseBody().write(status.getBytes());
            exchange.close();
        });

        // /zones
        server.createContext("/zones", exchange -> {
            String zones = ZoneManager.getZones();
            exchange.sendResponseHeaders(200, zones.getBytes().length);
            exchange.getResponseBody().write(zones.getBytes());
            exchange.close();
        });

        // /logs
        server.createContext("/logs", exchange -> {
            String logs = DnsActivity.lastQueries();
            exchange.sendResponseHeaders(200, logs.getBytes().length);
            exchange.getResponseBody().write(logs.getBytes());
            exchange.close();
        });

        // /
        server.createContext("/", exchange -> {
            File file = new File("/var/www/html/index.php"); // Chemin correct
            if (!file.exists()) {
                String error = "index.php introuvable";
                exchange.sendResponseHeaders(404, error.length());
                exchange.getResponseBody().write(error.getBytes());
                exchange.close();
                return;
            }
            byte[] content = Files.readAllBytes(file.toPath());
            exchange.getResponseHeaders().add("Content-Type", "text/html; charset=UTF-8");
            exchange.sendResponseHeaders(200, content.length);
            exchange.getResponseBody().write(content);
            exchange.close();
        });

        server.start();
        System.out.println("Serveur DNS Java démarré sur le port 8080");
    }
}
