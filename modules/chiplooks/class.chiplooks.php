<?php

class chiplooks extends ComponentModule {

	public function initInterfaces()
	{
		$this->Interfaces = array(
			'b_categories' => array(
				'name' => 'chiplook categories',
				'method' => 'methodBCategories',
				),
			'b_objects' => array(
				'name' => 'chiplook objects',
				'method' => 'methodBObjects',
				),
			'b_positions' => array(
				'name' => 'chiplook positions',
				'method' => 'methodBPositions',
				),
			'b_types' => array(
				'name' => 'chiplook types',
				'method' => 'methodBTypes',
				),
		);
		
		$chiplookTypeLister = new chiplookTypeLister;
		$list = $chiplookTypeLister->getList();
		
		$select_list = array();
		foreach ( $list as $v )
			$select_list[$v['slug']] = $v['name'];
		
		$this->__registerComponent('chiplooks','Элементы вида',array(TPLID_PRODUCT_INFO),'methodCptChiplooks',array(
			'type' => array(
				'type' => 'select',
				'params' => array(
					'name' => 'type',
					'options' => $select_list
				),
			),
		));
	}
	
	
	private function _productHasOption($productID,$type)
	{
		$q = "
		SELECT DISTINCT s.optionID AS expr1
		FROM
		  SC_product_options o
		INNER JOIN SC_product_options_set s
		ON o.optionID = s.optionID
		WHERE
		  o.name_en LIKE 'chiptype_$type'
		  AND s.productID = $productID
		  AND o.name_en NOT LIKE 'chiptype_selected%'
		";
		if ( $r = mysql_query($q) )
		{
			if ( $row = mysql_fetch_assoc($r) )
				return true;
			else
				return false;
		}
	}
	
	
	private function _getTypeOptionHTML($type,$productID)
	{
		foreach ( chiplookSessionStorage::getByProductID($productID) as $k=>$v )
			$objects[$k] = $v['objectID'];
		
		$M = new chiplookSelectedTypeOptionManager($type);
		extract($M->setVariant($objects,$productID));
		
		return ($variantID) ? "<option rel='0'></option><option value='$variantID' rel='$extra_price' selected='selected'></option>" : "<option rel='0'></option>";
	}
	
	
	public function methodCptChiplooks($call_settings = null)
	{
		$Register = &Register::getInstance();
		$smarty = &$Register->get(VAR_SMARTY);
		
		$local_settings = ( isset($call_settings['local_settings']) ) ? $call_settings['local_settings'] : array();
		if ( isset( $local_settings['type'] ) )
		{
			$type =  $local_settings['type'];
			$L = new chiplookPositionLister;
			$positions = $L->getByType($type);
			// echo '<pre>';
			// print_r($positions);
			// echo '</pre>';
			if ( !empty($positions) )
			{
				$product_info = $smarty->get_template_vars('product_info');
				$productID = $product_info['productID'];
				
				if ( $this->_productHasOption($productID,$type) )
				{
					foreach ( $positions as $k=>$p )
						$positions[$k]['object'] = chiplookSessionStorage::get($productID,$p['positionID']);
					
					$smarty->assign('chiplook_typeID',$positions[0]['typeID']);
					$smarty->assign('chiplook_type_name',$positions[0]['type_name']);
					$smarty->assign('positions',$positions);
					$smarty->assign('option',$this->_getTypeOptionHTML($type,$productID));
					echo $smarty->fetch('chiplooks_chooser.html');
				}
			}
		}
		
	}
	
	
	public function methodBCategories()
	{
		$L = new chiplookCategoryLister(1);
		$L->control();
	}
	
	
	public function methodBObjects()
	{
		$L = new chiplookObjectLister(1);
		$L->control();
	}
	
	
	public function methodBPositions()
	{
		$L = new chiplookPositionLister(1);
		$L->control();
	}
	
	
	public function methodBTypes()
	{
		$L = new chiplookTypeLister;
		$L->control();
		
		$Register = &Register::getInstance();
		$smarty = &$Register->get(VAR_SMARTY);
		
		if ( isset($_GET['edit']) )
		{
			$LC = new chiplookCategoryLister;
			$smarty->assign('add_categories',$L->getAddCategories());
			$smarty->assign('categories',$LC->getAddCategories($_GET['edit']));
		}
	}

}