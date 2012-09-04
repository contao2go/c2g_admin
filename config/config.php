<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

// Check existance of directories
$strRestoreFolder = new Folder('vhosts');
$strBackupFolder = new Folder('backups');


$GLOBALS['TL_CTE']['c2gAdmin'] = array
	(
		'c2g_listBackups'     => 'c2g_listBackups',
		'c2g_listVHosts'      => 'c2g_listVHosts',
	);
	
if (TL_MODE=='FE')
	$GLOBALS['TL_JAVASCRIPT'][] = 'contao/contao.js';
	