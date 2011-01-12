<ul id="year-navigation">
    <?php
    $year = (isset($_GET["y"])) ? $_GET["y"] : 0;
    $showall = ($year == 0) ? true : false;

    $result = $db->getYearNavigation($year, $showall);
    
    foreach ($result as $month) {
        echo '<li><a href="' . $config['app_base_path'] . $month->year . '/' . str_pad($month->number, 2, "0", STR_PAD_LEFT) . '"><span>' . $month->name . ' ' . $month->year . ' (' . $month->count . ')</span></a></li>';
    }
    ?>
</ul>
