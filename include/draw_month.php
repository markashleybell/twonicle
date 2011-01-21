<?php

// Code adapted from David Walsh's calendar function
// http://davidwalsh.name/php-calendar

function draw_month($year, $month, $highlight_day, $base_path, $counts, $max) {

    /* draw table */
    $calendar = '<table class="calendar">';
    
    /* table headings */
    $headings = array('S','M','T','W','T','F','S');
    $calendar .= '<tr><th>' . implode('</th><th>', $headings) . '</th></tr>';
    
    /* days and weeks vars now ... */
    $running_day = date('w', mktime(0, 0, 0, $month, 1, $year));
    $days_in_month = date('t', mktime(0, 0, 0, $month, 1, $year));
    $days_in_this_week = 1;
    $day_counter = 0;
    $dates_array = array();
    
    $rows = 1;
    $extra_row = '<tr>';
    
    for($x = 0; $x < 7; $x++)
    {
        
        $extra_row .= '<td><span class="noday">&nbsp;</span></td>';
        
    }
    
    $extra_row .= '</tr>';
    
    /* row for week one */
    $calendar .= '<tr>';
    
    /* print "blank" days until the first of the current week */
    for($x = 0; $x < $running_day; $x++) {
        
        $calendar .= '<td><span class="noday">&nbsp;</span></td>';
        $days_in_this_week++;
        
    }
    
    /* keep going with days.... */
    for($list_day = 1; $list_day <= $days_in_month; $list_day++) {
        
        $calendar .= '<td>';
        /* add in the day number */
        
        $tweettotal = getTweetCountForDay($counts, $list_day);
        
        if($tweettotal > 0)
            $calendar .= '<a title="' . $tweettotal . ' tweet' . (($tweettotal == 1) ? '' : 's') . '" style="background-position: 0 ' . (30 - round((($tweettotal / $max) * 30), 2)) . 'px;" href="' . $base_path . $year .'/' . $month . '/' . str_pad($list_day, 2, "0", STR_PAD_LEFT) . '"><span class="day ' . (($highlight_day == $list_day) ? 'current' : '') . '">' . $list_day . '</span></a>';
        else
            $calendar .= '<span class="day ' . (($highlight_day == $list_day) ? 'current' : '') . '">' . $list_day . '</span>';
            
        $calendar .= '</td>';
        
        if($running_day == 6) {
            
            $calendar .= '</tr>';
            $rows ++;
            
            if(($day_counter+1) != $days_in_month)
                $calendar .= '<tr>';
            
            $running_day = -1;
            $days_in_this_week = 0;
            
        }
        
        $days_in_this_week++;
        $running_day++;
        $day_counter++;
        
    }
    
    /* finish the rest of the days in the week */
    if($days_in_this_week < 8) {
        
        for($x = 1; $x <= (8 - $days_in_this_week); $x++)
        {
            
            $calendar.= '<td><span class="noday">&nbsp;</span></td>';
            
        }
        
    }
    
    /* final row */
    $calendar .= '</tr>';
    
    if($rows < 6)
        $calendar .= $extra_row;
    
    /* end the table */
    $calendar .= '</table>';
    
    /* all done, return result */
    return $calendar;

}

function getTweetCountForDay($counts, $day) {
    
    foreach($counts as $d)
    {
        
        if($d->day == $day)
            return $d->count;
        
    }
    
    return 0;
    
}

?>