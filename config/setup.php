<?php
require('config.php');

$basepath = ($config['app_base_path'] == '') ? '' : $config['app_base_path'] . '/';

if(isset($_POST['server']))
{
    $server = $_POST['server'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $dbname = $_POST['dbname'];
    $basepath = $_POST['basepath'];
    $tableprefix = $_POST['tableprefix'];
    $anywhereapikey = $_POST['anywhereapikey'];

    $db = new mysqli($server, $username, $password);

    if (mysqli_connect_errno()) {
        printf("Connect failed: %s\n", mysqli_connect_error());
        exit();
    }
    
    $db->set_charset("utf8");
    
    $db_check = $db->select_db($dbname);
    
    if (!$db_check) {
        $db->query('CREATE DATABASE ' . $dbname . ' CHARACTER SET utf8 COLLATE utf8_unicode_ci');
    }  

    // Load the schema and replace all instances of db name with specified name
    $sql = file_get_contents("schema.sql");
    $sql = preg_replace('/twonicle/', $dbname, $sql);
    $sql = preg_replace('/people/', $tableprefix . 'people', $sql);
    $sql = preg_replace('/system/', $tableprefix . 'system', $sql);
    $sql = preg_replace('/statuses/', $tableprefix . 'statuses', $sql);

    $db->multi_query($sql);

    // Write the local config file with user-supplied values
    $config = <<<CONFIG
<?php
\$local_config['anywhere_api_key'] = '$anywhereapikey';

\$local_config['server'] = '$server';
\$local_config['database'] = '$dbname';
\$local_config['username'] = '$username';
\$local_config['password'] = '$password';

\$local_config['app_base_path'] = '$basepath';

\$local_config['table_prefix'] = '$tableprefix';
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
    header( 'Location: setup/complete' ) ;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head>
        <link rel="stylesheet" type="text/css" href="/<?php echo $basepath; ?>css/updater.css" />
        <link rel="shortcut icon" href="/<?php echo $basepath; ?>img/site/favicon.ico" />
		<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js"></script>
        <script type="text/javascript">
            
            function checkIfDatabaseExists(name)
            {
                $.ajax({
                    url: 'setup/check_db',
                    data: $('#setup-form').serialize(),
                    dataType: 'json',
                    type: 'POST',
                    success: function(data, status, request) { 
                        
                        if(data.exists)
                            $('#dbname').after('<br /><span>WARNING: The specified database already exists. If any tables in the existing database have the same names as those being created they will be dropped and all data in them will be lost!</span>');
                        
                    },
                    error: function(request, status, error) {
                        
                        // TODO: Handle conn error
                        
                    }
                });
            }
            
            $(function(){
                
                $('#dbname').bind('blur', function() { checkIfDatabaseExists($(this).val()); });
                
            });
            
        </script>
	</head>
	<body>
        <h1>Setup</h1>
        <div id="output">
            <form action="setup" method="post" id="setup-form">
                <p><label for="server">Database Server</label>
                <input type="text" id="server" name="server" value="localhost" /></p>
                <p><label for="username">Database User Name</label>
                <input type="text" id="username" name="username" value="twonicle" /></p>
                <p><label for="password">Database Password</label>
                <input type="text" id="password" name="password" value="twonicle" /></p>
                <p><label for="dbname">Database Name</label>
                <input type="text" id="dbname" name="dbname" value="twonicle" /></p>
                <p><label for="tableprefix">Twonicle Table Prefix</label>
                <input type="text" id="tableprefix" name="tableprefix" value="twonicle_" /></p>
                <p><label for="basepath">App Base Path</label>
                <input type="text" id="basepath" name="basepath" value="" /> (e.g twonicle, leave blank for root) </p>
                <p><label for="anywhereapikey">@Anywhere API Key</label>
                <input type="text" id="anywhereapikey" name="anywhereapikey" value="" /></p>
                <p><input type="submit" value="Set Up Now" /></p>
            </form>
        </div>
	</body>
</html>