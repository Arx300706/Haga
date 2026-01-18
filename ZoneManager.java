import java.io.BufferedReader;
import java.io.FileReader;

public class ZoneManager {

    public static String getZones() {
        StringBuilder zones = new StringBuilder();
        try (BufferedReader br =
                     new BufferedReader(new FileReader("/etc/bind/named.conf.local"))) {

            String line;
            while ((line = br.readLine()) != null) {
                if (line.trim().startsWith("zone")) {
                    zones.append(line.trim()).append("\n");
                }
            }
        } catch (Exception e) {
            return "Impossible de lire les zones DNS";
        }
        return zones.toString();
    }
}
