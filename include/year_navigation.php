<ul id="year-navigation">
    <?php
    $nav_basepath = ($config['app_base_path'] == '') ? '' : $config['app_base_path'] . '/';
    $nav_year = (isset($_GET["y"])) ? $_GET["y"] : 0;
    $nav_month = (isset($_GET["m"])) ? $_GET["m"] : 0;

    $result = $db->getYearNavigation($nav_year, true);
    
    foreach ($result as $month) {
        echo '<li' . (($nav_month == $month->number && $nav_year == $month->year) ? ' class="current-month"' : '') .  '><a href="/' . $nav_basepath . $month->year . '/' . str_pad($month->number, 2, "0", STR_PAD_LEFT) . '"><span>' . $month->name . ' ' . $month->year . ' (' . $month->count . ')</span></a></li>';
    }
    ?>
</ul>
