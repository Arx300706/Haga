public class BindService {

    public static String getStatus() {
        return CommandExecutor.execute("systemctl is-active bind9");
    }

    public static String getFullStatus() {
        return CommandExecutor.execute("systemctl status bind9 --no-pager");
    }
}
