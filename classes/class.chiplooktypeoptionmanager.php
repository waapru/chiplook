<?php

class chiplookTypeOptionManager {

	protected $_option_id = 0;
	protected $_type_id = 0;
	protected $_type_slug = 0;
	protected $_option_name_prefix = 'chiptype_';
	protected $_opt_table = 'SC_product_options';
	protected $_name_en = '';
	protected $_name_ru = '';
	
	
	public function __construct($type_slug,$type_id = false)
	{
		$this->_type_slug = $type_slug;
		$this->_type_id = $type_id;
		if ( !empty($this->_type_slug) )
			$this->_setOption();
	}
	
	
	public function getOptionId()
	{
		return $this->_option_id;
	}
	
	
	/**
	 * Добавление в качестве варианта хар-ки категории элементов
	 */
	public function setVariant($category_slug,$category_name)
	{
		$result = true;
		
		$q = "SELECT COUNT(*) as c FROM SC_products_opt_val_variants WHERE option_value_en LIKE '$category_slug' AND optionID = {$this->_option_id}";
		if ( $r = mysql_query($q) )
			if ( $row = mysql_fetch_assoc($r) )
				if ( $row['c'] == 0 )
				{
					$q = "INSERT INTO SC_products_opt_val_variants (option_value_en,option_value_ru,optionID) VALUES ('$category_slug','$category_name',{$this->_option_id})";
					$result = mysql_query($q);
				}
		
		return $result;
	}
	
	
	static public function getAllTypeOptionsIDs()
	{
		static $ids;
		
		if ( empty($ids) )
		{
			$ids = array();
			
			$q = "
			SELECT o.optionID
			FROM
			  SC_product_options o
			WHERE
			  o.name_en LIKE 'chiptype%'
			";
			if ( $r = mysql_query($q) )
				while ( $row = mysql_fetch_assoc($r) )
					$ids[] = $row['optionID'];
		}
		
		return $ids;
	}
	
	
	protected function _setOption()
	{
		$this->_setOptionFromDb();
		
		if ( $this->_option_id == 0 )
			$this->_createOption();
		
		return $this;
	}
	
	
	protected function _setOptionFromDb()
	{
		$this->_option_id = 0;
		
		$q = "SELECT * FROM {$this->_opt_table} WHERE `name_en` LIKE '{$this->_getOptionEnName()}'";
		if ( $r = mysql_query($q) )
			if ( $row = mysql_fetch_assoc($r) )
			{
				$this->_option_id = $row['optionID'];
				$this->_name_en = $row['name_en'];
				$this->_name_ru = $row['name_ru'];
			}
		
		return $this;
	}
	
	
	protected function _createOption()
	{
		$name_ru = $this->_getOptionRuName();
		$name_en = $this->_getOptionEnName();
		if ( mysql_query("INSERT INTO {$this->_opt_table} (`name_en`,`name_ru`) VALUES ('$name_en','$name_ru')") )
			if ( $r = mysql_query("SELECT LAST_INSERT_ID() as optionID FROM {$this->_opt_table}") )
				if ( $row = mysql_fetch_assoc($r) )
				{
					$this->_option_id = $row['optionID'];
					$this->_name_en = $name_en;
					$this->_name_ru = $name_ru;
				}
		
		return $this;
	}
	
	
	protected function _getOptionEnName()
	{
		return  $this->_option_name_prefix . $this->_type_slug;
	}
	
	
	protected function _getOptionRuName()
	{
		return  'Элемент внешнего вида тип "' . $this->_type_slug . '"';
	}

}