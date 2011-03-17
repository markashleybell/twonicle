<div id="header">
    <p><a href="/<?php echo $config['app_base_path']; ?>">Home Page</a> | <a href="/<?php echo $config['app_base_path']; ?>/picks">Picks</a></p>
    <form id="search-form" action="search.php">
        <p><input type="text" name="q" value="<?php echo ((isset($_GET["q"])) ? $_GET["q"] : ''); ?>" /></p>
    </form>
</div>
