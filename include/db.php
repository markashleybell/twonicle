<?php

require('linkify.php');

class DB {
    
    private $_db = null;
    private $_prefix = "";
    public $_db_time_offset = 0;

    public function __construct($server, $username, $password, $database, $tableprefix) {
        
        $this->_db = new mysqli($server, $username, $password, $database);

        if (mysqli_connect_errno())
        {
            printf("Connect failed: %s\n", mysqli_connect_error());
            exit();
        }

        $this->_db->set_charset("utf8");
        
        $this->_prefix = $tableprefix;
        
        $diff = $this->_db->query("SELECT TIME_FORMAT(NOW() - UTC_TIMESTAMP(), '%H%i') AS `diff`");
        $result = $diff->fetch_object();
        $offset = date("Z") - ($result->diff * 36);
        
        if(!is_numeric($offset)) $offset = 0;
        
        $this->_db_time_offset = " " . (($offset >= 0) ? "+ " . $offset : $offset);
        
    }

    public function needsUpdate($hours) {
        
        // Get the last time the archive was updated
        $_lastupdate = 1000;

        if ($_result = $this->_db->query("select v as lastupdate from " . $this->_prefix . "system where k = 'lastupdatecompleted'")) {
            
            $_row = $_result->fetch_object();
            if($_row->lastupdate) $_lastupdate = $_row->lastupdate;
            $_result->close();
            
        }

        $_now = time();
        $_hours = ($_now - $_lastupdate) / 3600;
        $_updateneeded = false;

        // If the archive hasn't been updated for our specified number of hours
        if($_hours > $hours) {
            
            // Do an update (showing progress with AJAX dialog) and load in new data (also AJAX)
            // Eventually we'll do this automatically, but for now just show a warning and a link to the updater
            $_updateneeded = true;
            
        }

        return $_updateneeded;
    
    }
    
    public function getDailyTweetCountsForMonth($y, $m) {
        
        $sql = "select DAY(FROM_UNIXTIME(time)" . $this->_db_time_offset . ") as day, count(id) as count from " . $this->_prefix . "statuses  " . 
               "where YEAR(FROM_UNIXTIME(time)" . $this->_db_time_offset . ") = ? and MONTH(FROM_UNIXTIME(time)" . $this->_db_time_offset . ") = ? " .
               "group by DAY(FROM_UNIXTIME(time)" . $this->_db_time_offset . ") " . 
               "order by DAY(FROM_UNIXTIME(time)" . $this->_db_time_offset . ")"; 
               
        $cmd = $this->_db->stmt_init();
        $cmd->prepare($sql);
        $cmd->bind_param("ii", $y, $m);
        $cmd->execute();

        $cmd->bind_result($_day, $_count);
        
        $result = array();

        while ($row = $cmd->fetch()) {
            
            $d = new Day();
            
            $d->day = $_day;
            $d->count = $_count;
            
            $result[] = $d;
            
        }

        $cmd->close();
        
        return $result;
        
    }

    public function getYearNavigation($y, $all) {
        
        $months = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");

        $where = ($all) ? "" : "where YEAR(FROM_UNIXTIME(time)" . $this->_db_time_offset . ") = ? ";

        $sql = "select MONTH(FROM_UNIXTIME(time)" . $this->_db_time_offset . ") as month, YEAR(FROM_UNIXTIME(time)" . $this->_db_time_offset . ") as year, count(id) as count from " . $this->_prefix . "statuses " . $where .
               "group by YEAR(FROM_UNIXTIME(time)" . $this->_db_time_offset . "), MONTH(FROM_UNIXTIME(time)" . $this->_db_time_offset . ") " . 
               "order by YEAR(FROM_UNIXTIME(time)" . $this->_db_time_offset . ") desc, MONTH(FROM_UNIXTIME(time)" . $this->_db_time_offset . ") desc;";

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
    
    public function getRecentTweets() {
        
        $sql = "select s.statusid, s.rtstatusid, s.inreplytoid, s.inreplytouser, s.time, s.text, p.screenname, p.realname, p.profileimage " .
               "from " . $this->_prefix . "statuses as s " .
               "inner join " . $this->_prefix . "people as p " .
               "on p.userid = s.userid " .
               "order by time desc limit 100";

        $cmd = $this->_db->stmt_init();
        $cmd->prepare($sql);
        $cmd->execute();

        $cmd->bind_result($_statusid, $_rtstatusid, $_inreplytoid, $_inreplytouser, $_time, $_text, $_screenname, $_realname, $_profileimage);

        $result = array();

        while ($row = $cmd->fetch()) {
            
            $s = new Status();
            
            $s->id = $_statusid;
            $s->rtid = $_rtstatusid;
            $s->inreplytoid = $_inreplytoid;
            $s->inreplytouser = $_inreplytouser;
            
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

    public function getTweetsByMonth($y, $m) {
        
        $sql = "select s.statusid, s.rtstatusid, s.inreplytoid, s.inreplytouser, s.time, s.text, p.screenname, p.realname, p.profileimage " .
               "from " . $this->_prefix . "statuses as s " .
               "inner join " . $this->_prefix . "people as p " .
               "on p.userid = s.userid " .
               "where YEAR(FROM_UNIXTIME(time)" . $this->_db_time_offset . ") = ? AND MONTH(FROM_UNIXTIME(time)" . $this->_db_time_offset . ") = ? " . 
               "order by time desc";

        $cmd = $this->_db->stmt_init();
        $cmd->prepare($sql);
        $cmd->bind_param("ii", $y, $m);
        $cmd->execute();

        $cmd->bind_result($_statusid, $_rtstatusid, $_inreplytoid, $_inreplytouser, $_time, $_text, $_screenname, $_realname, $_profileimage);

        $result = array();

        while ($row = $cmd->fetch()) {
            
            $s = new Status();
            
            $s->id = $_statusid;
            $s->rtid = $_rtstatusid;
            $s->inreplytoid = $_inreplytoid;
            $s->inreplytouser = $_inreplytouser;
            
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

    public function getTweetsByDay($y, $m, $d) {
        
        $sql = "select s.statusid, s.rtstatusid, s.inreplytoid, s.inreplytouser, s.time, s.text, p.screenname, p.realname, p.profileimage " .
               "from " . $this->_prefix . "statuses as s " .
               "inner join " . $this->_prefix . "people as p " .
               "on p.userid = s.userid " .
               "where YEAR(FROM_UNIXTIME(time)" . $this->_db_time_offset . ") = ? AND MONTH(FROM_UNIXTIME(time)" . $this->_db_time_offset . ") = ? AND DAY(FROM_UNIXTIME(time)" . $this->_db_time_offset . ") = ? " . 
               "order by time desc";

        $cmd = $this->_db->stmt_init();
        $cmd->prepare($sql);
        $cmd->bind_param("iii", $y, $m, $d);
        $cmd->execute();

        $cmd->bind_result($_statusid, $_rtstatusid, $_inreplytoid, $_inreplytouser, $_time, $_text, $_screenname, $_realname, $_profileimage);

        $result = array();

        while ($row = $cmd->fetch()) {
            
            $s = new Status();
            
            $s->id = $_statusid;
            $s->rtid = $_rtstatusid;
            $s->inreplytoid = $_inreplytoid;
            $s->inreplytouser = $_inreplytouser;
            
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
