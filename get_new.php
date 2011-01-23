<?php

require('config/config.php');
require('include/status.php');
require('include/linkify.php');

$db = new mysqli($config['db_server'], $config['db_username'], $config['db_password'], $config['db_database']);

if (mysqli_connect_errno()) {
    
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
    
}

$db->set_charset("utf8");

$sql = "select s.statusid, s.rtstatusid, s.inreplytoid, s.inreplytouser, s.time, s.text, p.screenname, p.realname, p.profileimage " .
       "from " . $config['db_table_prefix']  . "statuses as s " .
       "inner join " . $config['db_table_prefix']  . "people as p " .
       "on p.userid = s.userid where `statusid` > ? and `statusid` != ? order by `statusid`";
       
// Note: I do not understand why we have to do this in the WHERE clause, but for some reason MySQL thinks that

$cmd = $db->stmt_init();
$cmd->prepare($sql);
$cmd->bind_param("is", $_POST['since'], $_POST['since']);
$cmd->execute();

$cmd->bind_result($_statusid, $_rtstatusid, $_inreplytoid, $_inreplytouser, $_time, $_text, $_screenname, $_realname, $_profileimage);

$result = array();

while ($row = $cmd->fetch()) {
    
    $s = new Status();
    
    $s->id = $_statusid;
    $s->rtid = $_rtstatusid;
    $s->inreplytoid = $_inreplytoid;
    $s->inreplytouser = $_inreplytouser;
    
    $s->time = date('d/m/y H:i', $_time);
    $s->text = linkify($_text);
    $s->screenname = $_screenname;
    $s->realname = $_realname; 
    $s->profileimage = $_profileimage;

    $result[] = $s;
    
}

$cmd->close();


header("Content-type: application/json; charset=utf-8");
header("Content-Transfer-Encoding: 8bit");

print json_encode($result);

?>