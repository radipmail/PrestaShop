<?php

require(dirname(__FILE__).'/config/config.inc.php');
$controller = ControllerFactory::getController('MyAccountController');
$controller->run();

