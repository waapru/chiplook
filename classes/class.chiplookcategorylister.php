<?php

class chiplookCategoryLister extends filteredLister {

	protected $_table = 'SC_chiplook_categories';
	protected $_fields = array('categoryID','name','slug','description','typeID','enabled');
	protected $_template = 'chiplook_category_lister.html';
	protected $_filter_fields = array('typeID'=>1);
	
	protected function _outDataFilter($data)
	{
		$data['filter_get_query'] = $this->_filter_get_query;
		$data['filter_form_items'] = $this->_filter_form_items;
		
		$CTL = new chiplookTypeLister();
		if ( is_array($data['list']) )
			foreach ( $data['list'] as $k=>$v )
			{
				$row = $CTL->getByID($v['typeID']);
				$data['list'][$k]['type'] = $row['name'];
			}

		$data['type_list'] = $CTL->getList();
		$data['filter_typeID'] = $this->_filter_fields['typeID'];

		return $data;
	}
	
	public function getByID($id)
	{
		return $this->_getByID($id);
	}
	
	public function getList()
	{
		return $this->_getList();
	}
	
	// backend
	public function getAddCategories($except_type_id)
	{
		$categories = array();
		
		$q = "
		SELECT t.name AS type_name
			 , c.*
			 , c.categoryID
			 , c.name
		FROM
		  SC_chiplook_categories c
		RIGHT OUTER JOIN SC_chiplook_types t
		ON c.typeID = t.typeID
		WHERE
		  t.typeID <> $except_type_id
		";
		if ( $r = mysql_query($q) )
			while ( $row = mysql_fetch_assoc($r) )
				$categories[] = $row;
		
		return $categories;
	}
	
	// backend
	protected function _save()
	{
		parent::_save();
		$data = $this->_post;
		
		$T = new chiplookTypeLister;
		$type = $T-> getByID($data['typeID']);
		
		$M = new chiplookTypeOptionManager($type['slug']);
		$M->setVariant($data['slug'],$data['name']);
	}
	
	
	static protected function _getCategoryIDsByTypeID($typeID)
	{
		$ids = array();
		
		$q = "SELECT categoryID FROM SC_chiplook_categories WHERE typeID=$typeID";
		if ( $r = mysql_query($q) )
			while ( $row = mysql_fetch_assoc($r) )
				$ids[] = $row['categoryID'];
		// echo '<pre>'; print_r($ids); echo '</pre>';
		return $ids;
	}
	
	
	static protected function _getAddCategoryIDsByTypeID($typeID)
	{
		$ids = array();
		
		$q = "SELECT categoryID FROM SC_added_categories WHERE typeID=$typeID";
		if ( $r = mysql_query($q) )
			while ( $row = mysql_fetch_assoc($r) )
				$ids[] = $row['categoryID'];
		// echo '<pre>'; print_r($ids); echo '</pre>';
		return $ids;
	}
	
	// frontend
	static public function getAllCetegoryIDsByTypeID($typeID)
	{
		$ids = array();
		
		$category_ids = self::_getCategoryIDsByTypeID($typeID);
		$add_category_ids = self::_getAddCategoryIDsByTypeID($typeID);
		
		if ( !empty( $category_ids ) )
		{
			if ( !empty( $add_category_ids ) )
				$ids = array_keys( array_flip( array_merge($category_ids,$add_category_ids) ) );
			else
				$ids = $category_ids;
		}
		// echo '<pre>'; print_r($ids); echo '</pre>';
		return $ids;
	}

}