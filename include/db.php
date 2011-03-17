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
        
        $this->_db_time_offset = " " . (($offset >= 0) ? "+" . $offset : $offset);
        
    }
    
    public function runUpdate($locktimeout) {
        
        $lastupdatestarttime = strtotime("now");

        if ($result = $this->_db->query("select v as lastupdatestarted from " . $this->_prefix . "system where k = 'lastupdatestarted'"))  {
            
            $row = $result->fetch_object();
            if($row->lastupdatestarted) $lastupdatestarttime = $row->lastupdatestarted;
            $result->close();
            
        }
        
        // Check if there is already an update in progress
        $processing = false;
        
        if ($result = $this->_db->query("select v as processing from " . $this->_prefix . "system where k = 'processing'")) {
            
            $row = $result->fetch_object();
            if($row->processing) $processing = $row->processing;
            $result->close();
            
        }
        
        // If a certain amount of time has elapsed since the current update was started, chances are the processing flag got 'stuck'
        // for some reason (perhaps the user refreshed the page halfway through, there was a temporary communication error etc)
        // Here we check how long it's been and ignore the flag if enough time has passed (it will get reset if the process is
        // successful this time around anyway)
        $minutessinceupdatestarted = (strtotime("now") - $lastupdatestarttime) / 60;
        
        if($minutessinceupdatestarted > $locktimeout)
            $processing = false;
        
        // If another client is already performing the update process, hold off until that process is complete
        if(!$processing)
        {
            // Set the flag that tells everyone else there's an update in progress
            $this->_db->query("update " . $this->_prefix . "system set v = 1 where k = 'processing'");
            // Set the start time for this update in case it gets interrupted
            $this->_db->query("update " . $this->_prefix . "system set v = " . strtotime("now") . " where k = 'lastupdatestarted'");
            
            // Do the update
            return true;
        }
        
        return false;
    }
    
    public function getLastStoredStatusId()
    {
        // Get the last status id stored, so that we only retrieve new tweets
        $since = 100;
        
        if ($result = $this->_db->query("select max(statusid) as since from " . $this->_prefix . "statuses")) {
            
            $row = $result->fetch_object();
            if($row->since) $since = $row->since;
            $result->close();
            
        }
        
        return $since;
    }

    public function archiveNeedsUpdate($hours) {
        
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
    
    public function getMaxTweetsInDayForMonth($y, $m)
    {
        $sql = "select max(c) from (select DAY(FROM_UNIXTIME(`time`" . $this->_db_time_offset . ")) as day, count(id) as c from " . $this->_prefix . "statuses  " .
               "where YEAR(FROM_UNIXTIME(`time`" . $this->_db_time_offset . ")) = ? and MONTH(FROM_UNIXTIME(`time`" . $this->_db_time_offset . ")) = ? " .
               "group by DAY(FROM_UNIXTIME(`time`" . $this->_db_time_offset . "))) as counts";
        
        $cmd = $this->_db->stmt_init();
        $cmd->prepare($sql);
        $cmd->bind_param("ii", $y, $m);
        $cmd->execute();

        $cmd->bind_result($_max);
        
        $max = 0;

        if ($row = $cmd->fetch())
            $max = $_max;

        $cmd->close();
        
        return $max;
    }
    
    public function getNewTweets($since) {
        
        $sql = "select s.statusid, s.rtstatusid, s.inreplytoid, s.inreplytouser, s.time, s.text, p.screenname, p.realname, p.profileimage, s.pick " .
               "from " . $this->_prefix  . "statuses as s " .
               "inner join " . $this->_prefix  . "people as p on p.userid = s.userid " .
               "where s.statusid > ? order by s.statusid";
                
        // echo $sql . ' - ' . $since;
        
        $cmd = $this->_db->stmt_init();
        $cmd->prepare($sql);
        $cmd->bind_param("s", $since);
        $cmd->execute();
        
        $cmd->bind_result($_statusid, $_rtstatusid, $_inreplytoid, $_inreplytouser, $_time, $_text, $_screenname, $_realname, $_profileimage, $_pick);
        
        $result = array();
        
        while ($row = $cmd->fetch()) {
            
            $s = new Status();
            
            $s->id = $_statusid;
            $s->rtid = $_rtstatusid;
            $s->inreplytoid = $_inreplytoid;
            $s->inreplytouser = $_inreplytouser;
            
            $s->time = date('d/m/Y H:i', $_time);
            $s->text = linkify($_text);
            $s->screenname = $_screenname;
            $s->realname = $_realname; 
            $s->profileimage = $_profileimage;
            
            $s->pick = $_pick;
        
            $result[] = $s;
            
        }
        
        $cmd->close();
        
        return $result;
    
    }
    
    public function getDailyTweetCountsForMonth($y, $m) {
        
        $sql = "select DAY(FROM_UNIXTIME(`time`" . $this->_db_time_offset . ")) as day, count(id) as count from " . $this->_prefix . "statuses  " . 
               "where YEAR(FROM_UNIXTIME(`time`" . $this->_db_time_offset . ")) = ? and MONTH(FROM_UNIXTIME(`time`" . $this->_db_time_offset . ")) = ? " .
               "group by DAY(FROM_UNIXTIME(`time`" . $this->_db_time_offset . ")) " . 
               "order by DAY(FROM_UNIXTIME(`time`" . $this->_db_time_offset . "))"; 
               
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

        $where = ($all) ? "" : "where YEAR(FROM_UNIXTIME(`time`" . $this->_db_time_offset . ")) = ? ";

        $sql = "select MONTH(FROM_UNIXTIME(`time`" . $this->_db_time_offset . ")) as month, YEAR(FROM_UNIXTIME(`time`" . $this->_db_time_offset . ")) as year, count(id) as count from " . $this->_prefix . "statuses " . $where .
               "group by YEAR(FROM_UNIXTIME(`time`" . $this->_db_time_offset . ")), MONTH(FROM_UNIXTIME(`time`" . $this->_db_time_offset . ")) " . 
               "order by YEAR(FROM_UNIXTIME(`time`" . $this->_db_time_offset . ")) desc, MONTH(FROM_UNIXTIME(`time`" . $this->_db_time_offset . ")) desc;";

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

    public function search($query) {
        
        $sql = "select s.statusid, s.rtstatusid, s.inreplytoid, s.inreplytouser, s.time, s.text, p.screenname, p.realname, p.profileimage, s.pick, " .
               "MATCH(s.text) AGAINST (? IN BOOLEAN MODE) AS rank " .
               "from " . $this->_prefix . "statuses as s " .
               "inner join " . $this->_prefix . "people as p " .
               "on p.userid = s.userid " .
               "where MATCH(s.text) AGAINST (? IN BOOLEAN MODE) > 0 " .
               "order by rank desc, time desc limit 2000";

        $cmd = $this->_db->stmt_init();
        $cmd->prepare($sql);
        $cmd->bind_param("ss", $query, $query);
        $cmd->execute();

        $cmd->bind_result($_statusid, $_rtstatusid, $_inreplytoid, $_inreplytouser, $_time, $_text, $_screenname, $_realname, $_profileimage, $_pick, $_rank);

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
            
            $s->pick = $_pick;

            $s->rank = $_rank;

            $result[] = $s;
            
        }

        $cmd->close();
        
        return $result;
    
    }
    
    public function getPicks() {
        
        $sql = "select s.statusid, s.rtstatusid, s.inreplytoid, s.inreplytouser, s.time, s.text, p.screenname, p.realname, p.profileimage, s.pick " .
               "from " . $this->_prefix . "statuses as s " .
               "inner join " . $this->_prefix . "people as p " .
               "on p.userid = s.userid " .
               "where s.pick = 1 " .
               "order by time desc limit 2000";

        $cmd = $this->_db->stmt_init();
        $cmd->prepare($sql);
        $cmd->execute();

        $cmd->bind_result($_statusid, $_rtstatusid, $_inreplytoid, $_inreplytouser, $_time, $_text, $_screenname, $_realname, $_profileimage, $_pick);

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
            
            $s->pick = $_pick;

            $result[] = $s;
            
        }

        $cmd->close();
        
        return $result;
    
    }

    public function getRecentTweets() {
        
        $sql = "select s.statusid, s.rtstatusid, s.inreplytoid, s.inreplytouser, s.time, s.text, p.screenname, p.realname, p.profileimage, s.pick " .
               "from " . $this->_prefix . "statuses as s " .
               "inner join " . $this->_prefix . "people as p " .
               "on p.userid = s.userid " .
               "order by time desc limit 100";

        $cmd = $this->_db->stmt_init();
        $cmd->prepare($sql);
        $cmd->execute();

        $cmd->bind_result($_statusid, $_rtstatusid, $_inreplytoid, $_inreplytouser, $_time, $_text, $_screenname, $_realname, $_profileimage, $_pick);

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
            
            $s->pick = $_pick;

            $result[] = $s;
            
        }

        $cmd->close();
        
        return $result;
    
    }

    public function getTweetsByMonth($y, $m) {
        
        $sql = "select s.statusid, s.rtstatusid, s.inreplytoid, s.inreplytouser, s.time, s.text, p.screenname, p.realname, p.profileimage, s.pick " .
               "from " . $this->_prefix . "statuses as s " .
               "inner join " . $this->_prefix . "people as p " .
               "on p.userid = s.userid " .
               "where YEAR(FROM_UNIXTIME(`time`" . $this->_db_time_offset . ")) = ? AND MONTH(FROM_UNIXTIME(`time`" . $this->_db_time_offset . ")) = ? " . 
               "order by time desc";

        $cmd = $this->_db->stmt_init();
        $cmd->prepare($sql);
        $cmd->bind_param("ii", $y, $m);
        $cmd->execute();

        $cmd->bind_result($_statusid, $_rtstatusid, $_inreplytoid, $_inreplytouser, $_time, $_text, $_screenname, $_realname, $_profileimage, $_pick);

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
            
            $s->pick = $_pick;

            $result[] = $s;
            
        }

        $cmd->close();

        return $result;
    
    }

    public function getTweetsByDay($y, $m, $d) {
        
        $sql = "select s.statusid, s.rtstatusid, s.inreplytoid, s.inreplytouser, s.time, s.text, p.screenname, p.realname, p.profileimage, s.pick " .
               "from " . $this->_prefix . "statuses as s " .
               "inner join " . $this->_prefix . "people as p " .
               "on p.userid = s.userid " .
               "where YEAR(FROM_UNIXTIME(`time`" . $this->_db_time_offset . ")) = ? AND MONTH(FROM_UNIXTIME(`time`" . $this->_db_time_offset . ")) = ? AND DAY(FROM_UNIXTIME(`time`" . $this->_db_time_offset . ")) = ? " . 
               "order by time desc";

        $cmd = $this->_db->stmt_init();
        $cmd->prepare($sql);
        $cmd->bind_param("iii", $y, $m, $d);
        $cmd->execute();

        $cmd->bind_result($_statusid, $_rtstatusid, $_inreplytoid, $_inreplytouser, $_time, $_text, $_screenname, $_realname, $_profileimage, $_pick);

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
            
            $s->pick = $_pick;

            $result[] = $s;
            
        }

        $cmd->close();

        return $result;
    
    }
    
}

?>
