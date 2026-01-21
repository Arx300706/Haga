public class DnsActivity {
    public static String lastQueries() {
        return CommandExecutor.execute("journalctl -u bind9 -n 20 --no-pager");
    }
}
