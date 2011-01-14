<?php 
$server = $_POST['server'];
$username = $_POST['username'];
$password = $_POST['password'];
$dbname = $_POST['dbname'];

$db = new mysqli($server, $username, $password);
    
$db_check = $db->select_db($dbname);

$exists = false;

if ($db_check) {
    $exists = true;
}  

echo '{ "exists": ' . (($exists) ? 'true' : 'false') . ' }';  
?>