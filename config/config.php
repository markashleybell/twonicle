<?php
require('config.local.php');

$config['anywhere_api_key'] = $local_config['anywhere_api_key'];

$config['server'] = $local_config['server'];
$config['database'] = $local_config['database'];
$config['username'] = $local_config['username'];
$config['password'] = $local_config['password'];

$config['update_interval_hours'] = 2;

$config['app_base_path'] = '/twonicle';

?>