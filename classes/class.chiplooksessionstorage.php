<?php

class chiplookSessionStorage {

	static public function set($productID,$positionID,$objectID)
	{
		$chiplookObjectLister = new chiplookObjectLister;
		$object = $chiplookObjectLister->getByID($objectID);
		
		if ( !empty($object) )
		{
			$object['small_image'] = chiplooks_getSmallImageUrl($object['image']);
			$object['image'] = chiplooks_getImageUrl($object['image']);
			$_SESSION['chiplooks'][$productID][$positionID] = $object;
		}
	}
	
	static public function get($productID,$positionID)
	{
		return $_SESSION['chiplooks'][$productID][$positionID];
	}
	
	static public function delete($productID,$positionID)
	{
		unset($_SESSION['chiplooks'][$productID][$positionID]);
	}
	
	static public function getByProductID($productID)
	{
		return $_SESSION['chiplooks'][$productID];
	}

}