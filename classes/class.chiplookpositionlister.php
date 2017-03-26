<?php

class chiplookPositionLister extends chiplookCategoryLister {

	protected $_table = 'SC_chiplook_positions';
	protected $_fields = array('positionID','name','typeID','enabled');
	protected $_template = 'chiplook_position_lister.html';
	protected $_filter_fields = array('typeID'=>1);
	
	public function getByID($id)
	{
		$object = array();
		
		$q = "SELECT * FROM {$this->_table} WHERE positionID = $id";
		if ( $r = mysql_query($q) )
			$object = mysql_fetch_assoc($r);
		
		return $object;
	}
	
	
	public function getByType($type)
	{
		$positions = array();
		
		$q = "
		SELECT p.*, t.name as 'type_name'
		FROM
		  SC_chiplook_types t
		RIGHT OUTER JOIN SC_chiplook_positions p
		ON t.typeID = p.typeID
		WHERE
		  t.slug LIKE '$type'
		";
		if ( $r = mysql_query($q) )
			while ( $row = mysql_fetch_assoc($r) )
				$positions[] = $row;
		
		return $positions;
	}

}