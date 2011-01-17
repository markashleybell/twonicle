<?php
require('config.local.php');

$config['twitter_username'] = $local_config['twitter_username'];

$config['app_base_path'] = $local_config['app_base_path'];
$config['app_anywhere_api_key'] = $local_config['app_anywhere_api_key'];
$config['app_update_interval_hours'] = 2;
$config['app_update_lock_timeout_minutes'] = 5;

$config['db_server'] = $local_config['db_server'];
$config['db_database'] = $local_config['db_database'];
$config['db_username'] = $local_config['db_username'];
$config['db_password'] = $local_config['db_password'];
$config['db_table_prefix'] = $local_config['db_table_prefix'];
?>