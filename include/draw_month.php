<?php

// Code adapted from David Walsh's calendar function
// http://davidwalsh.name/php-calendar

function draw_month($year, $month, $highlight_day, $base_path) {

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
    
    /* row for week one */
    $calendar .= '<tr class="calendar-row">';
    
    /* print "blank" days until the first of the current week */
    for($x = 0; $x < $running_day; $x++) {
        
        $calendar .= '<td>&nbsp;</td>';
        $days_in_this_week++;
        
    }
    
    /* keep going with days.... */
    for($list_day = 1; $list_day <= $days_in_month; $list_day++) {
        
        $calendar .= '<td>';
        /* add in the day number */
        $calendar .= '<a href="' . $base_path . $year .'/' . $month . '/' . str_pad($list_day, 2, "0", STR_PAD_LEFT) . '"><span class="day ' . (($highlight_day == $list_day) ? 'current' : '') . '">'.$list_day.'</span></a>';
            
        $calendar .= '</td>';
        
        if($running_day == 6) {
            
            $calendar .= '</tr>';
            
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
            
            $calendar.= '<td>&nbsp;</td>';
            
        }
        
    }
    
    /* final row */
    $calendar .= '</tr>';
    
    /* end the table */
    $calendar .= '</table>';
    
    /* all done, return result */
    return $calendar;

}

?>