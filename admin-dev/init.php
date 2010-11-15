<?php

/**
  * Admin panel initialization file, init.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.4
  *
  */

ob_start();
$timerStart = microtime(true);

$destination = substr($_SERVER['REQUEST_URI'], strlen(dirname($_SERVER['SCRIPT_NAME'])) + 1);
$url_redirect = '?redirect='.(empty($destination) ? 'index.php' : $destination);

/* Getting cookie or logout */
if (!class_exists('Cookie'))
	exit();

// Required for BO cookie path
$currentFileName = array_reverse(explode("/", $_SERVER['SCRIPT_NAME']));

$cookie = new Cookie('psAdmin', substr($_SERVER['SCRIPT_NAME'], strlen(__PS_BASE_URI__), -strlen($currentFileName['0'])));
if (isset($_GET['logout'])) {
	$url_redirect = '';
	$cookie->logout();
}

/* logged or not */
if (!$cookie->isLoggedBack())
	Tools::redirectLink('login.php'.$url_redirect);

/* link */
$link = new Link();


/* Current tab and current URL */
$tab = Tools::getValue('tab');
$currentIndex = __PS_BASE_URI__.substr($_SERVER['SCRIPT_NAME'], strlen(__PS_BASE_URI__)).($tab ? '?tab='.$tab : '');
if ($back = Tools::getValue('back'))
	$currentIndex .= '&back='.urlencode($back);

/* Server Params */
$server_host = Tools::getHttpHost(false, true);
$protocol = 'http://';
$protocol_ssl = 'https://';
$protocol_link = (Configuration::get('PS_SSL_ENABLED')) ? $protocol_ssl : $protocol;
$protocol_content = (isset($useSSL) AND $useSSL AND Configuration::get('PS_SSL_ENABLED')) ? $protocol_ssl : $protocol;
define('_PS_BASE_URL_', $protocol.$server_host);
define('_PS_BASE_URL_SSL_', $protocol_ssl.$server_host);

$cookie->id_lang = Db::getInstance()->getValue('SELECT id_lang FROM '._DB_PREFIX_.'employee WHERE id_employee = '.(int)$cookie->id_employee);
$iso = strtolower(Language::getIsoById($cookie->id_lang ? $cookie->id_lang : Configuration::get('PS_LANG_DEFAULT')));
include(_PS_TRANSLATIONS_DIR_.$iso.'/errors.php');
include(_PS_TRANSLATIONS_DIR_.$iso.'/fields.php');
include(_PS_TRANSLATIONS_DIR_.$iso.'/admin.php');

/* Database connection (singleton) */
Db::getInstance();

/* attribute id_lang is often needed, so we create a constant for performance reasons */
define('_USER_ID_LANG_', intval($cookie->id_lang));

$employee = new Employee((int)$cookie->id_employee);
$path = dirname(__FILE__).'/themes/';
if (empty($employee->bo_theme) OR !file_exists($path.$employee->bo_theme.'/admin.css'))
{
	if (file_exists($path.'origins/admin.css'))
		$employee->bo_theme = 'origins';
	else
	foreach (scandir($path) as $theme)
		if ($theme[0] != '.' AND file_exists($path.$theme.'/admin.css'))
		{
			$employee->bo_theme = $theme;
			break;
		}
	$employee->update();
}

?>
