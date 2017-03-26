<?php

class chiplookObjectLister extends chiplookCategoryLister {

	protected $_table = 'SC_chiplook_objects';
	protected $_fields = array('objectID','name','image','categoryID','enabled');
	protected $_template = 'chiplook_object_lister.html';
	protected $_filter_fields = array('categoryID'=>1,'typeID'=>1);
	
	
	protected function _outDataFilter($data)
	{
		$data = parent::_outDataFilter($data);
		if ( $data['show_item'] && file_exists(DIR_CHIPLOOKS_PICTURES.'/'.$data['item']['image']) && $data['item']['image'] )
			$data['image_exists'] = 1;
		//
		$CCL = new chiplookCategoryLister(1);
		$data['category_list'] = $CCL->getList();
		if ( !isset($this->_get['categoryID']) && !isset($this->_post['categoryID']) )
		{
			$this->_filter_fields['categoryID'] = $data['category_list'][0]['categoryID'];
			$this->_setQueries();
			$data['list'] = $this->_getList();
		}
		$data['filter_categoryID'] = $this->_filter_fields['categoryID'];
		
		if ( is_array($data['list']) )
			foreach ( $data['list'] as $k=>$v )
			{
				$row = $CCL->getByID($v['categoryID']);
				$data['list'][$k]['category'] = $row['name'];
				$data['list'][$k]['small_image'] = chiplooks_getSmallImageUrl($v['image']);
			}
		
		return $data;
	}
	
	
	protected function _file_save($data)
	{
		$Register = &Register::getInstance();
		$FilesVar = &$Register->get(VAR_FILES);
		
		if (isset($FilesVar["image_file"]) && $FilesVar["image_file"]["name"])
			do {
				$res = File::checkUpload($FilesVar["image_file"]);
				if (PEAR::isError($res))
				{
					$error = $res;
					break;
				}
				$a = explode('.',$FilesVar["image_file"]["name"]);
				$ext = array_pop($a);
				$file_name = str2url(implode('.',$a)).'.'.$ext;
				$file_name = chiplooks_getUnicFile($file_name);
				
				//move file to work directory
				if(
				PEAR::isError($res = File::checkUpload($FilesVar['image_file']))||
				PEAR::isError($res = File::move_uploaded($FilesVar['image_file']['tmp_name'],DIR_CHIPLOOKS_PICTURES.'/'.$file_name))
				){
					$error = $res;
					break;
				}
				
				$data['image'] = $file_name;
				$small_file_name = chiplooks_getSmallImageName($file_name);
				
				$img = waImage::factory(DIR_CHIPLOOKS_PICTURES.'/'.$file_name);
				$img->resize(100, 100);
				$img->save(DIR_CHIPLOOKS_PICTURES.'/'.$small_file_name);
				
			} while(false);
			
		return $data;
	}
	
	
	public function getByID($id)
	{
		$object = array();
		
		$q = "SELECT * FROM {$this->_table} WHERE objectID = $id AND enabled=1";
		if ( $r = mysql_query($q) )
			$object = mysql_fetch_assoc($r);
		
		return $object;
	}
	
	
	static public function getObjectsByTypeID($typeID)
	{
		$objects = array();
		
		$categoryIDs = chiplookCategoryLister::getAllCetegoryIDsByTypeID($typeID);
		// echo '<pre>'; print_r($categoryIDs); echo '</pre>';
		if ( !empty($categoryIDs) )
		{
			$in = '('.implode(',',$categoryIDs).')';
			// echo $in;
			$q = "
			SELECT c.name AS category
				 , c.categoryID
				 , c.description
				 , o.objectID
				 , o.name
				 , o.image
			FROM
			  SC_chiplook_objects o
			LEFT OUTER JOIN SC_chiplook_categories c
			ON o.categoryID = c.categoryID
			WHERE
			  c.enabled = 1
			  AND o.enabled = 1
			  AND c.categoryID IN $in
			ORDER BY
			  c.priority
			, o.priority
			";
			// echo $q;
			$objects = array();
			if ( $r = mysql_query($q) )
				while ( $row = mysql_fetch_assoc($r) )
					$objects[] = $row;
			
			$objects = self::_combineObjectsInCategories($objects);
			// echo '<pre>'; print_r($objects); echo '</pre>';
		}
		
		return $objects;
	}
	
	
	static protected function _combineObjectsInCategories($objects)
	{
		$categories = array();
		
		if ( !empty($objects) )
		{
			$old_category_id = 0;
			$k = 0;
			foreach ( $objects as $o )
			{
				if ( $o['categoryID'] != $old_category_id )
				{
					$k++;
					$categories[$k]['name'] = $o['category'];
					$categories[$k]['description'] = $o['description'];
					$old_category_id = $o['categoryID'];
				}
				unset($o['category']);
				unset($o['description']);
				unset($o['categoryID']);
				$o['small_image_url'] = chiplooks_getSmallImageUrl( $o['image']);
				$o['large_image_url'] = chiplooks_getImageUrl( $o['image']);
				$categories[$k]['objects'][] = $o;
			}
		}
		
		return $categories;
	}

}