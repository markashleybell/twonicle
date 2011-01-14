<?php
require('config.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head>
        <link rel="stylesheet" type="text/css" href="../css/updater.css" />
		<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js"></script>
	</head>
	<body>
        <h1>Setup Complete</h1>
        <div id="output">
            <p>Congratulations! You've successfully set up your installation of Twonicle.</p>
            <p><a href="<?php echo $config['app_base_path']; ?>update">Click here to import your tweets</a>.</p>
        </div>
	</body>
</html>