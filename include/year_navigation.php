<ul id="year-navigation">
    <?php
    $year = (isset($_GET["y"])) ? $_GET["y"] : 0;
    $showall = ($year == 0) ? true : false;

    $result = $db->getYearNavigation($year, $showall);
    
    foreach ($result as $month) {
        echo '<li><a href="' . $config['app_base_path'] . '/' . $month->year . '/' . $month->number . '"><span>' . $month->name . ' ' . $month->year . ' (' . $month->count . ')</span></a></li>';
    }
    ?>
</ul>
