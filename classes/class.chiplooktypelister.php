<?php

class chiplookTypeLister extends lister {

	protected $_table = 'SC_chiplook_types';
	protected $_fields = array('typeID','name','slug','description','enabled');
	protected $_template = 'chiplook_type_lister.html';

	public function getByID($id)
	{
		return $this->_getByID($id);
	}
	
	public function getList()
	{
		return $this->_getList();
	}
	
	protected function _save()
	{
		parent::_save();
		
		$this->_saveAddCategories();
		
		$data = $this->_post;
		$M = new chiplookTypeOptionManager($data['slug']);
		$this->_setVariantForAddCategories($M);
	}
	
	protected function _saveAddCategories()
	{
		$data = $this->_post;
		$scan_result = scanArrayKeysForID($this->_post, 'add_category');
		
		mysql_query("DELETE FROM SC_chiplook_added_categories WHERE typeID={$this->_post['typeID']}");
		
		$q = "INSERT INTO SC_chiplook_added_categories (`typeID`,categoryID) VALUES ({$this->_post['typeID']},?)";
		foreach ( $scan_result as $id=>$scan_info )
			db_phquery($q, $id);
	}
	
	protected function _setVariantForAddCategories($M)
	{
		$categories = $this->getAddCategories();
		foreach ( $categories as $category )
			$M->setVariant($category['type_slug'].'-'.$category['slug'],$category['name']);
	}
	
	public function getAddCategories()
	{
		$categories = array();
		
		$q = "
		SELECT c.*
			 , t2.name AS type_name
			 , t2.slug AS type_slug
		FROM
		  SC_chiplook_added_categories a
		RIGHT OUTER JOIN SC_chiplook_categories c
		ON a.categoryID = c.categoryID
		LEFT OUTER JOIN SC_chiplook_types t
		ON a.typeID = t.typeID
		RIGHT OUTER JOIN SC_chiplook_types t2
		ON c.typeID = t2.typeID
		WHERE
		  t.typeID = {$this->_get['edit']}
		";
		if ( $r = mysql_query($q) )
			while ( $row = mysql_fetch_assoc($r) )
				$categories[] = $row;
		
		return $categories;
	}

}