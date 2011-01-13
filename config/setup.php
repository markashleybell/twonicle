<?php
if(isset($_POST['server']))
{
    $server = $_POST['server'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $dbname = $_POST['dbname'];
    $basepath = $_POST['basepath'];

    $db = new mysqli($server, $username, $password);

    if (mysqli_connect_errno()) {
        printf("Connect failed: %s\n", mysqli_connect_error());
        exit();
    }

    $db->set_charset("utf8");

    // Load the schema and replace all instances of db name with specified name
    $sql = file_get_contents("schema.sql");
    $sql = preg_replace('/twonicle/', $dbname, $sql);

    $db->multi_query($sql);

    // Write the local config file with user-supplied values
    $config = <<<CONFIG
<?php
\$local_config['anywhere_api_key'] = 'idyTlCoEihlkLSC0ezJ1Q';

\$local_config['server'] = '$server';
\$local_config['database'] = '$dbname';
\$local_config['username'] = '$username';
\$local_config['password'] = '$password';

\$local_config['app_base_path'] = '$basepath';
?>
CONFIG;

    $f  = fopen("config.local.php", "w+");

    if($f){
    	if(fwrite($f, $config)){
    		fclose($f);
    	} else {
    		// Return non-writable error
    	}
    } else {
    	// Return file find error
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head>
        <link rel="stylesheet" type="text/css" href="../css/updater.css" />
		<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js"></script>
	</head>
	<body>
        <h1>Setup</h1>
        <div id="output">
            <form action="setup.php" method="post">
                <p><label for="server">Database Server</label>
                <input type="text" id="server" name="server" value="localhost" /></p>
                <p><label for="username">Database User Name</label>
                <input type="text" id="username" name="username" value="twonicle" /></p>
                <p><label for="password">Database Password</label>
                <input type="text" id="password" name="password" value="twonicle" /></p>
                <p><label for="dbname">Database Name</label>
                <input type="text" id="dbname" name="dbname" value="twonicle" /></p>
                <p><label for="basepath">App Base Path</label>
                <input type="text" id="basepath" name="basepath" value="/" /></p>
                <p><input type="submit" value="Set Up Now" /></p>
            </form>
        </div>
	</body>
</html>