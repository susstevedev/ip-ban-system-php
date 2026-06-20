// ip ban system
// checks for blacklisted ip address
function check_blacklisted_ip() {
    $soft_ver = 'alpha 0.1.2 (06/20/26)';
    $app_name = 'This really cool website';
    $repo_name = 'susstevedev/ip-ban-system-php';
    $repo_url = 'https://github.com/susstevedev/ip-ban-system-php';
    $piko_url = 'https://cdn.jsdelivr.net/npm/@picocss/pico@latest/css/pico.min.css';
    
    $db = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);
    if ($db->connect_error) {
        exit($db->connect_error);
    }
    
    $ip = $_SERVER['REMOTE_ADDR'];
    
    if (!isset($_SESSION['country_code']) || !isset($_SESSION['region'])) {
        $geo_url = 'https://get.geojs.io/v1/ip/geo/' . $ip . '.json';
        $geo_response = @file_get_contents($geo_url);

        if ($geo_response !== false) {
            $geo_data = json_decode($geo_response);
            $_SESSION['country_code'] = isset($geo_data->country_code) ? $geo_data->country_code : 'UNKNOWN';
            $_SESSION['region'] = isset($geo_data->region) ? $geo_data->region : 'UNKNOWN';
        } else {
            $_SESSION['country_code'] = 'UNKNOWN';
        }
    }

    $geo_banned = false;
    $restricted_countries = ['GB', 'AU'];
	$restricted_states = [
    	'Connecticut', 'Florida', 'Idaho', 'Louisiana', 
    	'Mississippi', 'Nebraska', 'Tennessee', 'Utah'
	];

	if (in_array($_SESSION['country_code'], $restricted_countries, true) || in_array($_SESSION['region'], $restricted_states, true)) {
        $geo_banned = true;
        $ban_until = '[unknown]';
        $ban_at = '[unknown]';
        $reason = 'Users from a province with "age verification" laws are not allowed to use our services.';
    } else {
        $stmt = $db->prepare("SELECT id, ban_at, ban_until, reason FROM ip_bans WHERE ip = ?");
        $stmt->bind_param("s", $ip);
        $stmt->execute();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $ban_at, $ban_until, $reason);
            $stmt->fetch();
            $geo_banned = true;
        }
        $stmt->close();
    }
    
    if(isset($id) && $ban_until > date("Y-m-d H:i:s") || $geo_banned === true) {
        http_response_code(403);
        echo "<html><head><title>IP address banned - " . $app_name . "</title><link rel='stylesheet' href='" . $piko_url . "'></head>";
        echo "<body><center><div id='root'><br /><h1>Your IP address has been banned!</h1>";
        echo "<b>" . $reason . "</b>";
        echo "<p>Banned at <b>" . $ban_at . "</b>, until <b>" . $ban_until . "</b>.</p>";
        echo "<p>To get unbanned, you will have to contact <b><a href='mailto:" . DB_MAIL . "'>" . DB_MAIL . "</a></b> and provide the reason you got banned along with why you should be unbanned.</p>";
        echo "<p>Additionally, you can ask for your account to be deleted.</p>";
        echo "<p><small><a href='" . $repo_url . "'>" . $repo_name . "</a> " . $soft_ver . ".</small></p>";
        echo "</div></center></body></html>";
        exit;
    }
}
check_blacklisted_ip();
