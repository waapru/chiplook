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
	
	
	static function getProductPositions($productID)
	{
		$q = db_phquery("
		SELECT DISTINCT
			Q.positionID,
			Q.name,
			Q.type,
			CASE WHEN P.positionID IS NULL THEN 1 ELSE 0 END AS enabled
		FROM (
		SELECT p.positionID, p.name, t.name as 'type'
		FROM `SC_chiplook_positions` p 
		LEFT JOIN `SC_chiplook_types` t ON t.typeID = p.typeID 
		WHERE p.enabled = 1 AND t.enabled = 1
		ORDER BY t.name
		) Q
		LEFT JOIN (
		SELECT * FROM SC_chiplook_product_position pp WHERE pp.productID = ?
		) P ON Q.positionID = P.positionID 
		ORDER BY Q.type
		",$productID);
		$positions = array();
		while ( $row = db_fetch_row($q) )
			$positions[] = $row;
		return $positions;
	}
	
	
	static function saveProductPosition($data,$productID)
	{
		if ( $productID = (int)$productID )
		{
			$positions = self::getProductPositions($productID);
			mysql_query("DELETE FROM SC_chiplook_product_position WHERE productID = $productID");
			if ( $data && $productID )
				foreach ( $positions as $p )
					if ( !isset($data[$p['positionID']]) )
					{
						$positionID = (int)$p['positionID'];
						mysql_query("INSERT INTO SC_chiplook_product_position (positionID,productID) VALUES ($positionID,$productID)");
					}
		}
	}

}