<?php

require('linkify.php');

class DB
{
    private $_db = null;

    public function __construct($server, $username, $password, $database)
    {
        $this->_db = new mysqli($server, $username, $password, $database);		

        if (mysqli_connect_errno()) {
            printf("Connect failed: %s\n", mysqli_connect_error());
            exit();
        }

        $this->_db->set_charset("utf8");
	}

    public function needsUpdate($hours)
    {
        // Get the last time the archive was updated
        $_lastupdate = 1000;

        if ($_result = $this->_db->query("select v as lastupdate from system where k = 'lastupdated'")) {
            $_row = $_result->fetch_object();
            if($_row->lastupdate) $_lastupdate = $_row->lastupdate;
            $_result->close();
        }

        $_now = time();
        $_hours = ($_now - $_lastupdate) / 3600;
        $_updateneeded = false;

        // If the archive hasn't been updated for our specified number of hours
        if($_hours > $hours)
        {
            // Do an update (showing progress with AJAX dialog) and load in new data (also AJAX)
            // Eventually we'll do this automatically, but for now just show a warning and a link to the updater
            $_updateneeded = true;
        }

        return $_updateneeded;
    }

    public function getRecentTweets()
    {
        $sql = "select s.id, s.time, s.text, p.screenname, p.realname, p.profileimage " .
               "from statuses as s " .
               "inner join people as p " .
               "on p.userid = s.userid " .
               "order by time desc limit 100";

        $cmd = $this->_db->stmt_init();
        $cmd->prepare($sql);
        $cmd->execute();

        $cmd->bind_result($_id, $_time, $_text, $_screenname, $_realname, $_profileimage);

        $result = array();

        while ($row = $cmd->fetch()) {

            $s = new Status();
            
            $s->id = $_id;
            $s->time = $_time;
            $s->text = linkify($_text);
            $s->screenname = $_screenname;
            $s->realname = $_realname; 
            $s->profileimage = $_profileimage;

            $result[] = $s;
        }

        $cmd->close();
        
        return $result;
    }
    
    public function getYearNavigation($y, $all)
    {
        $months = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");

        $where = ($all) ? "" : "where YEAR(FROM_UNIXTIME(time)) = ? ";

    	$sql = "select MONTH(FROM_UNIXTIME(time)) as month, YEAR(FROM_UNIXTIME(time)) as year, count(id) as count from statuses " . $where .
	           "group by YEAR(FROM_UNIXTIME(time)), MONTH(FROM_UNIXTIME(time)) " . 
	           "order by YEAR(FROM_UNIXTIME(time)) desc, MONTH(FROM_UNIXTIME(time)) desc;";

        $cmd = $this->_db->stmt_init();
        $cmd->prepare($sql);

        if(!$all)
            $cmd->bind_param("i", $y);

        $cmd->execute();
	
        $cmd->bind_result($_month, $_year, $_count);
        
        $result = array();

        while ($row = $cmd->fetch()) {
            
            $m = new Month();
            $m->year = $_year;            
            $m->number = $_month;
            $m->name = $months[($_month - 1)];
            $m->count = $_count;
            
            $result[] = $m;
        }

        $cmd->close();

        return $result;
    }

    public function getTweetsByMonth($y, $m)
    {
        $sql = "select s.id, s.time, s.text, p.screenname, p.realname, p.profileimage " .
               "from statuses as s " .
               "inner join people as p " .
               "on p.userid = s.userid " .
               "where YEAR(FROM_UNIXTIME(time)) = ? AND MONTH(FROM_UNIXTIME(time)) = ? " . 
               "order by time desc";

        $cmd = $this->_db->stmt_init();
        $cmd->prepare($sql);
        $cmd->bind_param("ii", $y, $m);
        $cmd->execute();

        $cmd->bind_result($_id, $_time, $_text, $_screenname, $_realname, $_profileimage);

        $result = array();

        while ($row = $cmd->fetch()) {

            $s = new Status();
            
            $s->id = $_id;
            $s->time = $_time;
            $s->text = linkify($_text);
            $s->screenname = $_screenname;
            $s->realname = $_realname; 
            $s->profileimage = $_profileimage;

            $result[] = $s;
        }

        $cmd->close();

        return $result;
    }

    public function getTweetsByDay($y, $m, $d)
    {
        $sql = "select s.id, s.time, s.text, p.screenname, p.realname, p.profileimage " .
               "from statuses as s " .
               "inner join people as p " .
               "on p.userid = s.userid " .
               "where YEAR(FROM_UNIXTIME(time)) = ? AND MONTH(FROM_UNIXTIME(time)) = ? AND DAY(FROM_UNIXTIME(time)) = ? " . 
               "order by time desc";

        $cmd = $this->_db->stmt_init();
        $cmd->prepare($sql);
        $cmd->bind_param("iii", $y, $m, $d);
        $cmd->execute();

        $cmd->bind_result($_id, $_time, $_text, $_screenname, $_realname, $_profileimage);

        $result = array();

        while ($row = $cmd->fetch()) {

            $s = new Status();
            
            $s->id = $_id;
            $s->time = $_time;
            $s->text = linkify($_text);
            $s->screenname = $_screenname;
            $s->realname = $_realname; 
            $s->profileimage = $_profileimage;

            $result[] = $s;
        }

        $cmd->close();

        return $result;
    }
}

?>