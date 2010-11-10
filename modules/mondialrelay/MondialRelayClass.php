<?php

/**
  * CMS class, CMS.php
  * CMS management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

class MondialRelayClass extends ObjectModel
{
	public $id_customer;
	public $id_method;
	public $id_cart;
	public $id_order = NULL;
	public $MR_poids = NULL;
	public $MR_Selected_Num;
	public $MR_Selected_LgAdr1;
	public $MR_Selected_LgAdr2 = NULL;
	public $MR_Selected_LgAdr3;
	public $MR_Selected_LgAdr4 = NULL;
	public $MR_Selected_CP;
	public $MR_Selected_Ville;
	public $MR_Selected_Pays;
	public $url_suivi= NULL;
	public $url_etiquette= NULL;
	public $exp_number= NULL;
	public $date_add;
	public $date_upd;
	

	protected $table = 'mr_selected';
	protected $identifier = 'id_mr_selected';


	public function getFields()
	{
		parent::validateFields();
		if (isset($this->id_mr_selected))
		$fields['id_mr_selected'] = intval($this->id_mr_selected);
		$fields['id_customer'] = intval($this->id_customer);
		$fields['id_method'] = intval($this->id_method);
		$fields['id_cart'] = intval($this->id_cart);
		$fields['id_order'] = is_null($this->id_order) ? 0 : intval($this->id_order);
		$fields['MR_Selected_Num'] = pSQL($this->MR_Selected_Num);
		$fields['MR_Selected_LgAdr1'] = pSQL($this->MR_Selected_LgAdr1);
		$fields['MR_Selected_LgAdr2'] = pSQL($this->MR_Selected_LgAdr2);
		$fields['MR_Selected_LgAdr3'] = pSQL($this->MR_Selected_LgAdr3);
		$fields['MR_Selected_LgAdr4'] = pSQL($this->MR_Selected_LgAdr4);
		$fields['MR_Selected_CP'] = intval($this->MR_Selected_CP);
		$fields['MR_Selected_Ville'] = pSQL($this->MR_Selected_Ville);
		$fields['MR_Selected_Pays'] = pSQL($this->MR_Selected_Pays);
		$fields['url_suivi'] = is_null($this->url_suivi) ? 0 : pSQL($this->url_suivi) ;
		$fields['url_etiquette'] = is_null($this->url_etiquette) ? 0 : pSQL($this->url_etiquette) ;
		$fields['exp_number'] = is_null($this->exp_number) ? 0 : pSQL($this->exp_number) ;
		$fields['date_add'] = pSQL($this->date_add);
		$fields['date_upd'] = pSQL($this->date_upd);
		$fields['MR_poids'] = is_null($this->MR_poids) ? 0 : pSQL($this->MR_poids) ;
		return $fields;
	}
	
	static function getOrders()
	{
		$id_order_state = Configuration::get('MONDIAL_RELAY_ORDER_STATE');
		$sql = '
			SELECT  o.`id_address_delivery` as id_address_delivery, o.`id_order` as id_order, o.`id_customer` as id_customer,
			    o.`id_cart` as id_cart, mrs.`id_mr_selected` as id_mr_selected,
				CONCAT(c.`firstname`, \' \', c.`lastname`) AS `customer`,
				o.`total_paid_real` as total, o.`total_shipping` as shipping,
				o.`date_add` as date, o.`id_currency` as id_currency, o.`id_lang` as id_lang,
				mrs.`MR_poids` as weight, mr.`mr_Name` as mr_Name, mrs.`MR_Selected_Num` as MR_Selected_Num,
				mrs.`MR_Selected_Pays` as MR_Selected_Pays, mrs.`exp_number` as exp_number,
				mr.`mr_ModeCol` as mr_ModeCol, mr.`mr_ModeLiv` as mr_ModeLiv, mr.`mr_ModeAss` as mr_ModeAss 
			FROM `'._DB_PREFIX_.'orders` o
				LEFT JOIN `'._DB_PREFIX_.'carrier` ca ON (ca.`id_carrier` = o.`id_carrier` AND ca.`external_module_name` = "mondialrelay")
				LEFT JOIN `'._DB_PREFIX_.'mr_selected` mrs ON (mrs.`id_cart` = o.`id_cart`)
				LEFT JOIN `'._DB_PREFIX_.'mr_method` mr ON (mr.`id_carrier` = ca.`id_carrier`)
				LEFT JOIN `'._DB_PREFIX_.'customer` c ON (c.`id_customer` = o.`id_customer`)
			WHERE (SELECT moh.`id_order_state` FROM `'._DB_PREFIX_.'order_history` moh WHERE moh.`id_order` = o.`id_order` ORDER BY moh.`date_add` DESC LIMIT 1) = '.intval($id_order_state).' 
			AND ca.`external_module_name` = "mondialrelay"
			GROUP BY o.`id_order`
			ORDER BY o.`date_add` ASC';
		return Db::getInstance()->ExecuteS($sql);
	}

}

?>
