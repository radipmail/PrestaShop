<?php

ini_set('display_errors', 'On');
include(dirname(__FILE__).'/../../../config/config.inc.php');
ini_set('display_errors', 'On');
include(dirname(__FILE__).'/../treepodia.php');

ini_set('display_errors', 'On');

$treepodia = new Treepodia();
$treepodia->generateXmlFlow();

?>