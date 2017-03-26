<?php

class chiplookOptionCleaner {

	private $_option_ids_in = '';
	private $_option_name = 'chiptype_selected';
	
	private function _getOptionIdsIn()
	{
		if ( $this->_option_ids_in )
			return $this->_option_ids_in;
		
		$q = "
			SELECT o.optionID
			FROM
			  SC_product_options o
			WHERE
			  o.name_en LIKE '{$this->_option_name}%'
		";
		
		$ids = easyMySQL::col($q);
		if ( count($ids) == 1 )
			$this->_option_ids_in = ' = '.array_shift($ids);
		elseif ( count($ids) > 1 )
			$this->_option_ids_in = ' IN ('.implode(',',$ids).')';
		
		return $this->_option_ids_in;
	}
	
	public function clean()
	{
		if ( $this->_getOptionIdsIn() )
		{
			mysql_query("DELETE FROM SC_products_opt_val_variants WHERE optionID {$this->_getOptionIdsIn()}");
			$this->_cleaned_variant_count = mysql_affected_rows();
			mysql_query("DELETE FROM SC_product_options_set WHERE optionID {$this->_getOptionIdsIn()}");
			$this->_cleaned_product_set_count = mysql_affected_rows();
		}
		
		return array($this->_cleaned_variant_count,$this->_cleaned_product_set_count);
	}

}