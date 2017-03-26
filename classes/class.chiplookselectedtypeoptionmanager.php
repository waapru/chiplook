<?php

class chiplookSelectedTypeOptionManager extends chiplookTypeOptionManager {

	protected $_option_name_prefix = 'chiptype_selected_';
	protected $_selected_objects = array();
	
	/**
	 * Добавление в качестве варианта хар-ки выбранные элементы
	 * $objects = array(
	 *     positionID => objectID,
	 *     positionID => objectID,
	 * );
	 */
	public function setVariant($objects,$productID)
	{
		$variantID = 0;
		$extra_price = 0;
		
		$this->_selected_objects = array();
		if ( is_array($objects) )
		{
			$chiplookObjectLister = new chiplookObjectLister;
			$chiplookPositionLister = new chiplookPositionLister;
			$i = 0;
			
			foreach ( $objects as $positionID => $objectID )
			{
				$this->_selected_objects[$i]['object'] = $chiplookObjectLister->getByID($objectID);
				$this->_selected_objects[$i]['position'] = $chiplookPositionLister->getByID($positionID);
				$i++;
			}
		}
		// фильтруем по типу
		if ( $this->_type_id )
			foreach ( $this->_selected_objects as $k=>$v )
				if ( $v['position']['typeID'] != $this->_type_id )
					unset($this->_selected_objects[$k]);
		// print_r($this->_selected_objects);
		// print_r($this->_type_id);
		if ( !empty($this->_selected_objects) )
		{
			$variantID = $this->_getVariantID();
			$extra_price = $this->_saveVariant($productID,$variantID);
		}
		
		return array('variantID'=>$variantID,'extra_price'=>$extra_price);
	}

	
	private function _saveVariant($productID,$variantID)
	{
		$result = false;
		
		$extra_price = $this->_getExtraPrice($productID);
		if ( $variantID )
			$result = mysql_query("INSERT INTO SC_product_options_set (productID,optionID,variantID,price_surplus) VALUES ($productID,{$this->_option_id},$variantID,$extra_price)");
		
		return $extra_price;
	}
	
	
	private function _getVariantID()
	{
		$variantID = 0;
		
		if ( $value = $this->_getVariantValue() )
		{
			$q = "
			SELECT variantID
			FROM
			  SC_products_opt_val_variants
			WHERE
			  optionID = {$this->_option_id}
			  AND option_value_ru LIKE '$value'
			";
			if ( $r = mysql_query($q) )
				if ( $row = mysql_fetch_assoc($r) )
					$variantID = $row['variantID'];
				else
				{
					mysql_query("INSERT INTO SC_products_opt_val_variants (optionID,option_value_ru) VALUES ({$this->_option_id},'$value')");
					if ( $r = mysql_query('SELECT LAST_INSERT_ID() as variantID FROM SC_products_opt_val_variants') )
						if ( $row = mysql_fetch_assoc($r) )
							$variantID = $row['variantID'];
				}
		}
		return $variantID;
	}
	
	
	private function _getExtraPrice($productID)
	{
		$extra_price = 0;
		
		$extra_prices = $this->_getProductExtraPrices($productID);
		// echo '<pre>';
		// print_r($extra_prices);
		// echo '</pre>';
		foreach ( $this->_selected_objects as $v )
		{
			$categoryID = $v['object']['categoryID'];
			$price = $extra_prices[$categoryID];
			if ( $price > $extra_price )
				$extra_price = $price;
		}
		//echo $extra_price;
		return $extra_price;
	}
	
	
	private function _getVariantValue()
	{
		$value = '';
		if ( !empty($this->_selected_objects) )
		{
			$values = array();
			foreach ( $this->_selected_objects as $v )
				$values[] = $v['position']['name'] . ' | ' . $v['object']['name'];
			$value = implode(', ',$values);
		}
		return $value;
	}
	
	
	private function _getProductExtraPrices($productID)
	{
		$extra_prices = array();
		
		$M = new chiplookTypeOptionManager($this->_type_slug);
		
		$q = "
		SELECT DISTINCT s.price_surplus
					  , c.categoryID
		FROM
		  SC_product_options o
		LEFT OUTER JOIN SC_product_options_set s
		ON o.optionID = s.optionID
		RIGHT OUTER JOIN SC_products_opt_val_variants v
		ON s.variantID = v.variantID
		INNER JOIN SC_chiplook_categories c
		ON c.slug = v.option_value_en
		WHERE
		  s.productID = $productID
		  AND s.optionID = {$M->getOptionId()}
		";
		if ( $r = mysql_query($q) )
			while ( $row = mysql_fetch_assoc($r) )
				$extra_prices[$row['categoryID']] = $row['price_surplus'];
		
		return $extra_prices;
	}
}