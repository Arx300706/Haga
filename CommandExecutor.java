import java.io.BufferedReader;
import java.io.InputStreamReader;

public class CommandExecutor {

    public static String execute(String command) {
        StringBuilder result = new StringBuilder();
        try {
            Process process = new ProcessBuilder("bash", "-c", command).start();
            BufferedReader reader =
                    new BufferedReader(new InputStreamReader(process.getInputStream()));
            String line;
            while ((line = reader.readLine()) != null) {
                result.append(line).append("\n");
            }
        } catch (Exception e) {
            return "Erreur : " + e.getMessage();
        }
        return result.toString();
    }
}
