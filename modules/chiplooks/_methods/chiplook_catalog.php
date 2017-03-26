<?php

class chiplookCatalogController extends ActionsController {

	/* ACTION */
	public function choose()
	{
		chiplookSessionStorage::set($_GET['prdID'],$_GET['positionID'],$_GET['objectID']);
		
		$L = new chiplookTypeLister;
		$type = $L->getByID($_GET['typeID']);
		
		$objects = array(
			'p_'.$_GET['positionID'] => $_GET['objectID']
		);

		foreach ( chiplookSessionStorage::getByProductID($_GET['prdID']) as $k=>$v )
			$ses_objects['p_'.$k] = $v['objectID'];

		$objects = array_merge( $ses_objects , $objects);
		
		foreach ( $objects as $k=>$v )
			$combine_objects[str_replace('p_','',$k)] = $v;
		
		$M = new chiplookSelectedTypeOptionManager($type['slug']);
		extract($M->setVariant($combine_objects,$_GET['prdID']));
		
		echo ($variantID) ? "<option rel=''></option><option value='$variantID' rel='$extra_price' selected='selected'></option>" : '';
		
		//unset($_GET['action']);
		//unset($_GET['objectID']);
		//RedirectSQ();
		
		Message::raiseAjaxMessage(MSG_SUCCESS, 'code', 'choose_chiplook');
		die;
	}
	
	public function clear()
	{
		chiplookSessionStorage::delete($_GET['prdID'],$_GET['positionID']);
		
		$L = new chiplookTypeLister;
		$type = $L->getByID($_GET['typeID']);
		
		foreach ( chiplookSessionStorage::getByProductID($_GET['prdID']) as $k=>$v )
			$objects[$k] = $v['objectID'];
		
		$M = new chiplookSelectedTypeOptionManager($type['slug']);
		extract($M->setVariant($objects,$_GET['prdID']));
		
		echo ($variantID) ? "<option rel=''></option><option value='$variantID' rel='$extra_price' selected='selected'></option>" : '';
		
		Message::raiseAjaxMessage(MSG_SUCCESS, 'code', 'clear_chiplook');
		die;
	}
	
	public function get_active_object()
	{
		Message::raiseAjaxMessage(MSG_SUCCESS, '', 'get_active_fabric');
		die;
	}
	
	public function main()
	{
		// echo '<pre>';
		// print_r($_SESSION['chiplooks']);
		// echo '</pre>';
		
		$Register = &Register::getInstance();
		$GetVars = &$Register->get(VAR_GET);
		$smarty = &$Register->get(VAR_SMARTY);
		
		$categories = chiplookObjectLister::getObjectsByTypeID($GetVars['typeID']);
		// echo '<pre>';
		// print_r($categories);
		// print_r($GetVars);
		// echo '</pre>';
		$L = new chiplookPositionLister;
		
		$choosed = chiplookSessionStorage::get($GetVars['prdID'],$GetVars['positionID']);
		
		$smarty->assign('position',$L->getByID($GetVars['positionID']));
		$smarty->assign('categories',$categories);
		$smarty->assign('typeID',$GetVars['typeID']);
		$smarty->assign('positionID',$GetVars['positionID']);
		$smarty->assign('prdID',$GetVars['prdID']);
		$smarty->assign('choosed',$choosed);
	}

}

ActionsController::exec('chiplookCatalogController');