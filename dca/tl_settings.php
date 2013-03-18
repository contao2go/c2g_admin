<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Table tl_content
 */
$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'].=';{area_c2g},c2g_disablesnapshoting';

	
	
$GLOBALS['TL_DCA']['tl_settings']['fields']['c2g_disablesnapshoting'] = array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['c2g_disablesnapshoting'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
		);
		
			
		
?>