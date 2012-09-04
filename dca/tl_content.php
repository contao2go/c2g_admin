<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Table tl_content
 */
$GLOBALS['TL_DCA']['tl_content']['palettes']['c2g_listBackups'] =
	'{type_legend},type,headline;c2g_listvhosts;cssID,space';
$GLOBALS['TL_DCA']['tl_content']['palettes']['c2g_listVHosts'] =
	'{type_legend},type,headline;c2g_listbackups;cssID,space';
$GLOBALS['TL_DCA']['tl_content']['palettes']['c2g_importPackage'] =
	'{type_legend},type,headline;cssID,space';

	
	
$GLOBALS['TL_DCA']['tl_content']['fields']['c2g_listbackups'] = array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_content']['c2g_listbackups_link'],
			'exclude'                 => true,
			'inputType'               => 'pageTree',
			'eval'                    => array('fieldType'=>'radio', 'tl_class'=>'clr','mandatory'=>true)
		);
		
			
$GLOBALS['TL_DCA']['tl_content']['fields']['c2g_listvhosts'] = array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_content']['c2g_listvhosts_link'],
			'exclude'                 => true,
			'inputType'               => 'pageTree',
			'eval'                    => array('fieldType'=>'radio', 'tl_class'=>'clr','mandatory'=>true)
		);
		
		
?>