<?php

/**
  * Toolbar bar for admin panel
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.0
  *
  */
  
function recursiveTab($id_tab)
{
	global $cookie, $tabs;
	
	$adminTab = Tab::getTab(intval($cookie->id_lang), $id_tab);
	$tabs[]= $adminTab;
	if ($adminTab['id_parent'] > 0)
		recursiveTab($adminTab['id_parent']);
}

function checkingTab($tab)
{
	global $adminObj;
	
	if (!Validate::isTabName($tab))
		return false;
	if (file_exists(PS_ADMIN_DIR.'/tabs/'.$tab.'.php'))
		include_once(PS_ADMIN_DIR.'/tabs/'.$tab.'.php');
	$id_tab = Tab::getIdFromClassName($tab);
	if (!class_exists($tab, false) OR !$id_tab)
	{
		echo Tools::displayError('Tab does not exist');
		return false;
	}
	$adminObj = new $tab;
	if (!$adminObj->viewAccess())
	{
		echo Tools::displayError('access denied');
		return false;
	}
	return ($id_tab);
}

function checkTabRights($id_tab)
{
	global $cookie;
	
	$tabAccess = Profile::getProfileAccess($cookie->profile, intval($id_tab));
	if ($tabAccess['view'] === '1')
		return (true);
	return (false);
}

?>
