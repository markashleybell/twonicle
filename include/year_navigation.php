<ul id="year-navigation">
    <?php
    $year = (isset($_GET["y"])) ? $_GET["y"] : 2011;
    
    $result = $db->getYearNavigation($year);
    
    foreach ($result as $month) {
        echo '<li><a href="' . $config['app_base_path'] . '/' . $year . '/' . $month->number . '"><span>' . $month->name . ' (' . $month->count . ')</span></a></li>';
    }
    ?>
</ul>