<?php
$basepath = './';

if(isset($_POST['db_server']))
{
    $twitter_username = $_POST['twitter_username'];
    
    $db_server = $_POST['db_server'];
    $db_username = $_POST['db_username'];
    $db_password = $_POST['db_password'];
    $db_name = $_POST['db_name'];
    $db_tableprefix = $_POST['db_tableprefix'];
    
    $app_basepath = $_POST['app_basepath'];
    $app_anywhereapikey = $_POST['app_anywhereapikey'];

    $db = new mysqli($db_server, $db_username, $db_password);

    if (mysqli_connect_errno()) {
        printf("Connect failed: %s\n", mysqli_connect_error());
        exit();
    }
    
    $db->set_charset("utf8");
    
    $db_check = $db->select_db($db_name);
    
    if (!$db_check) {
        $db->query('CREATE DATABASE ' . $db_name . ' CHARACTER SET utf8 COLLATE utf8_unicode_ci');
    }  

    // Load the schema and replace all instances of db name with specified name
    $sql = file_get_contents("schema.sql");
    $sql = preg_replace('/twonicle/', $db_name, $sql);
    $sql = preg_replace('/people/', $db_tableprefix . 'people', $sql);
    $sql = preg_replace('/system/', $db_tableprefix . 'system', $sql);
    $sql = preg_replace('/statuses/', $db_tableprefix . 'statuses', $sql);

    $db->multi_query($sql);

    // Write the local config file with user-supplied values
    $config = <<<CONFIG
<?php
\$local_config['twitter_username'] = '$twitter_username';

\$local_config['app_base_path'] = '$app_basepath';
\$local_config['app_anywhere_api_key'] = '$app_anywhereapikey';

\$local_config['db_server'] = '$db_server';
\$local_config['db_database'] = '$db_name';
\$local_config['db_username'] = '$db_username';
\$local_config['db_password'] = '$db_password';
\$local_config['db_table_prefix'] = '$db_tableprefix';
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
        <link rel="stylesheet" type="text/css" href="<?php echo $basepath; ?>css/updater.css" />
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
                            $('#db_name').after('<br /><span>WARNING: The specified database already exists. If any tables in the existing database have the same names as those being created they will be dropped and all data in them will be lost!</span>');
                        
                    },
                    error: function(request, status, error) {
                        
                        // TODO: Handle conn error
                        
                    }
                });
            }
            
            $(function(){
                
                $('#db_name').bind('blur', function() { checkIfDatabaseExists($(this).val()); });
                
            });
            
        </script>
	</head>
	<body>
        <h1>Setup</h1>
        <div id="output">
            <form action="setup" method="post" id="setup-form">
                <p><label for="twitter_username">Twitter User Name</label>
                <input type="text" id="twitter_username" name="twitter_username" value="" /></p>
                <p><label for="db_server">Database Server</label>
                <input type="text" id="db_server" name="db_server" value="localhost" /></p>
                <p><label for="db_username">Database User Name</label>
                <input type="text" id="db_username" name="db_username" value="twonicle" /></p>
                <p><label for="db_password">Database Password</label>
                <input type="text" id="db_password" name="db_password" value="twonicle" /></p>
                <p><label for="db_name">Database Name</label>
                <input type="text" id="db_name" name="db_name" value="twonicle" /></p>
                <p><label for="db_tableprefix">Twonicle Table Prefix</label>
                <input type="text" id="db_tableprefix" name="db_tableprefix" value="twonicle_" /></p>
                <p><label for="app_basepath">App Base Path</label>
                <input type="text" id="app_basepath" name="app_basepath" value="" /> (e.g twonicle, leave blank for root) </p>
                <p><label for="app_anywhereapikey">@Anywhere API Key</label>
                <input type="text" id="app_anywhereapikey" name="app_anywhereapikey" value="" /></p>
                <p><input type="submit" value="Set Up Now" /></p>
            </form>
        </div>
	</body>
</html>