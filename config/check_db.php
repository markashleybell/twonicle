<?php 
$server = $_POST['db_server'];
$username = $_POST['db_username'];
$password = $_POST['db_password'];
$dbname = $_POST['db_name'];

$db = new mysqli($server, $username, $password);
    
$db_check = $db->select_db($dbname);

$exists = false;

if ($db_check) {
    $exists = true;
}  

echo '{ "exists": ' . (($exists) ? 'true' : 'false') . ' }';  
?>