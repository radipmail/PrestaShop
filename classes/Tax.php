<?php

/**
  * Taxes class, Tax.php
  * Taxes management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.3
  *
  */

class Tax extends ObjectModel
{
 	/** @var string Name */
	public 		$name;

	/** @var float Rate (%) */
	public 		$rate;
	
	/** @var bool active state */
	public 		$active;

 	protected 	$fieldsRequired = array('rate');
 	protected 	$fieldsValidate = array('rate' => 'isFloat');
 	protected 	$fieldsRequiredLang = array('name');
 	protected 	$fieldsSizeLang = array('name' => 32);
 	protected 	$fieldsValidateLang = array('name' => 'isGenericName');

	protected 	$table = 'tax';
	protected 	$identifier = 'id_tax';

	public		$noZeroObject = 'getTaxes';

	public function getFields()
	{
		parent::validateFields();
		$fields['rate'] = floatval($this->rate);
		$fields['active'] = intval($this->active);
		return $fields;
	}
	
	/** @var array Tax zones cache */
	private static $_TAX_ZONES;

	/**
	* Check then return multilingual fields for database interaction
	*
	* @return array Multilingual fields
	*/
	public function getTranslationsFieldsChild()
	{
		parent::validateFieldsLang();
		return parent::getTranslationsFields(array('name'));
	}

	public function delete()
	{
		if (!Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'tax_state` WHERE `id_tax` = '.intval($this->id)))
			return false;
		return parent::delete();
	}

	public static function checkTaxZone($id_tax, $id_zone)
	{
		return isset(self::$_TAX_ZONES[intval($id_zone)][intval($id_tax)]);
	}

	public function getStates()
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('SELECT `id_state`, `id_tax` FROM `'._DB_PREFIX_.'tax_state` WHERE `id_tax` = '.intval($this->id));
	}

	public function getState($id_state)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('SELECT `id_state` FROM `'._DB_PREFIX_.'tax_state` WHERE `id_tax` = '.intval($this->id).' AND `id_state` = '.intval($id_state));
	}

	public function addState($id_state)
	{
		return Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'tax_state` (`id_tax`, `id_state`) VALUES ('.intval($this->id).', '.intval($id_state).')');
	}

	public function deleteState($id_state)
	{
		return Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'tax_state` WHERE `id_tax` = '.intval($this->id).' AND `id_state` = '.intval($id_state));
	}

	/**
	 * Get all zones
	 *
	 * @return array Zones
	 */
	public function getZones()
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
			SELECT *
			FROM `'._DB_PREFIX_.'tax_zone`
			WHERE `id_tax` = '.intval($this->id));
	}

	/**
	 * Get a specific zones
	 *
	 * @return array Zone
	 */
	public function getZone($id_zone)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
			SELECT *
			FROM `'._DB_PREFIX_.'tax_zone`
			WHERE `id_tax` = '.intval($this->id).'
			AND `id_zone` = '.intval($id_zone));
	}

	/**
	 * Add zone
	 */
	public function addZone($id_zone)
	{
		return Db::getInstance()->Execute('
			INSERT INTO `'._DB_PREFIX_.'tax_zone` (`id_tax` , `id_zone`)
			VALUES ('.intval($this->id).', '.intval($id_zone).')');
	}

	/**
	 * Delete zone
	 */
	public function deleteZone($id_zone)
	{
		return Db::getInstance()->Execute('
			DELETE FROM `'._DB_PREFIX_.'tax_zone`
			WHERE `id_tax` = '.intval($this->id).'
			AND `id_zone` = '.intval($id_zone).' LIMIT 1');
	}

	/**
	* Get all available taxes
	*
	* @return array Taxes
	*/
	static public function getTaxes($id_lang = false, $active = 1)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT t.id_tax, t.rate'.(intval($id_lang) ? ', tl.name, tl.id_lang ' : '').'
		FROM `'._DB_PREFIX_.'tax` t
		'.(intval($id_lang) ? 'LEFT JOIN `'._DB_PREFIX_.'tax_lang` tl ON (t.`id_tax` = tl.`id_tax` AND tl.`id_lang` = '.intval($id_lang).')'
		.($active == 1 ? 'WHERE t.`active` = 1' : '').'
		ORDER BY `name` ASC' : ''));
	}

	static public function excludeTaxeOption()
	{
		return !Configuration::get('PS_TAX');
	}

	static public function zoneHasTax($id_tax, $id_zone)
	{
		return Tax::checkTaxZone(intval($id_tax), intval($id_zone));
	}

	static public function getRateByState($id_state, $active = 1)
	{
		$tax = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
			SELECT ts.`id_tax`, t.`rate`
			FROM `'._DB_PREFIX_.'tax_state` ts
			LEFT JOIN `'._DB_PREFIX_.'tax` t ON (t.`id_tax` = ts.`id_tax`)
			WHERE `id_state` = '.intval($id_state).
			($active == 1 ? ' AND t.`active` = 1' : ''));
		return $tax ? floatval($tax['rate']) : false;
	}

	static public function getApplicableTax($id_tax, $productTax, $id_address = NULL)
	{
		global $cart, $cookie, $defaultCountry;

		if (!is_object($cart))
			die(Tools::displayError());
		if (!$id_address)
			$id_address = $cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')};
		/* If customer has an address (implies that he is registered and logged) */
		if ($id_address AND $address_ids = Address::getCountryAndState($id_address))
		{
			$id_zone_country = Country::getIdZone(intval($address_ids['id_country']));
			if (!empty($address_ids['vat_number']) AND $address_ids['id_country'] != Configuration::get('VATNUMBER_COUNTRY') AND Configuration::get('VATNUMBER_MANAGEMENT'))
				return 0;
			/* If customer's invoice address is inside a state */
			if ($address_ids['id_state'])
			{
				$state = new State(intval($address_ids['id_state']));
				if (!Validate::isLoadedObject($state))
					die(Tools::displayError());
				/* Return tax value depending to the tax behavior */
				$tax_behavior = intval($state->tax_behavior);
				if ($tax_behavior == PS_PRODUCT_TAX)
					return $productTax * Tax::zoneHasTax(intval($id_tax), intval($id_zone_country));
				if ($tax_behavior == PS_STATE_TAX)
					return Tax::getRateByState(intval($address_ids['id_state']));
				if ($tax_behavior == PS_BOTH_TAX)
					return ($productTax * Tax::zoneHasTax(intval($id_tax), intval($id_zone_country))) + Tax::getRateByState(intval($address_ids['id_state']));
				/* Unknown behavior */
				die(Tools::displayError('Unknown tax behavior!'));
			}
			/* Else getting country zone tax */
			if (!$id_zone = Address::getZoneById($id_address))
				die(Tools::displayError());
			return $productTax * Tax::zoneHasTax(intval($id_tax), intval($id_zone));
		}
		/* Default tax application */
		if (!Validate::isLoadedObject($defaultCountry))
			die(Tools::displayError());
		return $productTax * Tax::zoneHasTax(intval($id_tax), intval($defaultCountry->id_zone));
	}

	/**
	  * Load all tax/zones relations in memory for caching
	  */
	static public function loadTaxZones($active = 1)
	{
		self::$_TAX_ZONES = array();
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT tz.`id_tax`, tz.`id_zone` FROM `'._DB_PREFIX_.'tax_zone` tz 
		JOIN `'._DB_PREFIX_.'tax` t ON (t.`id_tax` = tz.`id_tax`)'.
		($active == 1 ? ' WHERE t.`active` = 1' : ''));
		if ($result === false)
			die(Tools::displayError('Invalid loadTaxZones() SQL query!'));
		foreach ($result AS $row)
			self::$_TAX_ZONES[intval($row['id_zone'])][intval($row['id_tax'])] = true;
	}
	
	static public function getTaxIdByRate($rate, $active = 1)
	{
		$tax = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
			SELECT `id_tax`
			FROM `'._DB_PREFIX_.'tax`
			WHERE `rate` = '.floatval($rate).
			($active == 1 ? ' AND `active` = 1' : ''));
		return $tax ? intval($tax['id_tax']) : false;
	}
}
