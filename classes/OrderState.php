<?php

/**
  * Order states class, OrderState.php
  * Order states management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.4
  *
  */

class OrderStateCore extends ObjectModel
{
 	/** @var string Name */
	public 		$name;
	
	/** @var string Template name if there is any e-mail to send */	
	public 		$template;
	
	/** @var boolean Send an e-mail to customer ? */
	public 		$send_email;
	
	/** @var boolean Allow customer to view and download invoice when order is at this state */
	public		$invoice;
	
	/** @var string Display state in the specified color */
	public		$color;
	
	public		$unremovable;

	/** @var boolean Log authorization */
	public		$logable;
	
	/** @var boolean Delivery */
	public		$delivery;

	/** @var boolean Hidden */
	public		$hidden;

 	protected 	$fieldsValidate = array('send_email' => 'isBool', 'invoice' => 'isBool', 'color' => 'isColor', 'logable' => 'isBool');
	protected 	$fieldsRequiredLang = array('name');
 	protected 	$fieldsSizeLang = array('name' => 64, 'template' => 64);
 	protected 	$fieldsValidateLang = array('name' => 'isGenericName', 'template' => 'isTplName');
	
	protected 	$table = 'order_state';
	protected 	$identifier = 'id_order_state';
	
	protected	$webserviceParameters = array(
		'fields' => array(
			'unremovable' => array('sqlId' => 'unremovable'),
			'delivery' => array('sqlId' => 'delivery'),
			'hidden' => array('sqlId' => 'hidden'),
		),
	);
	
	public function getFields()
	{
		parent::validateFields();
		$fields['send_email'] = (int)($this->send_email);
		$fields['invoice'] = (int)($this->invoice);
		$fields['color'] = pSQL($this->color);
		$fields['unremovable'] = (int)($this->unremovable);
		$fields['logable'] = (int)($this->logable);
		$fields['delivery'] = (int)($this->delivery);
		$fields['hidden'] = (int)($this->hidden);
		return $fields;
	}
	
	/**
	* Check then return multilingual fields for database interaction
	*
	* @return array Multilingual fields
	*/
	public function getTranslationsFieldsChild()
	{
		parent::validateFieldsLang();
		return parent::getTranslationsFields(array('name', 'template'));
	}
	
	/**
	* Get all available order states
	*
	* @param integer $id_lang Language id for state name
	* @return array Order states
	*/
	static public function getOrderStates($id_lang)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT *
		FROM `'._DB_PREFIX_.'order_state` os
		LEFT JOIN `'._DB_PREFIX_.'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state` AND osl.`id_lang` = '.(int)($id_lang).')
		ORDER BY `name` ASC');
	}

	/**
	* Check if we can make a facture when order is in this state
	*
	* @param integer $id_order_state State ID
	* @return boolean availability
	*/
	static public function invoiceAvailable($id_order_state)
	{
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
		SELECT `invoice` AS ok
		FROM `'._DB_PREFIX_.'order_state`
		WHERE `id_order_state` = '.(int)($id_order_state));
		return $result['ok'];
	}
	
	public function isRemovable()
	{
	 	return !($this->unremovable);
	}
}


