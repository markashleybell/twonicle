<div id="header">
    <p><a href="/<?php echo $config['app_base_path']; ?>">Home Page</a>
    | <a href="/<?php echo $config['app_base_path']; ?>/picks">Picks</a>
    | <a href="/<?php echo $config['app_base_path']; ?>/picks/text">Picks (Plain Text)</a></p>
    <form id="search-form" action="/<?php echo $config['app_base_path']; ?>/search.php">
        <p><input type="text" name="q" value="<?php echo ((isset($_GET["q"])) ? $_GET["q"] : ''); ?>" /></p>
    </form>
</div>
