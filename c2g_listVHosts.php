<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

class c2g_listVHosts extends ContentElement
{

	protected $strTemplate = 'ce_c2g_list';
	
	
	public function compile()
	{
	
		global $objPage;
		$this->import('c2g_functions');
		$this->import('Environment');
		
		$arrVHosts = scan(TL_ROOT.'/vhosts');
		
		$path = $this->replaceInsertTags('{{env::path}}');
		$pathBackups = $this->replaceInsertTags(sprintf('{{link_url::%s}}',$this->c2g_listbackups));
		$this->pathMySelf = $this->replaceInsertTags(sprintf('{{link_url::%s}}',$objPage->id));
		
		
		
		if ($this->Input->get("act"))
		{
			if ($this->Input->get("act")=="statechange")
			{
				$stateCookie = ($this->Input->cookie($this->Input->get('key'))) ? false : true;
			
				$this->setCookie($this->Input->get('key'),$stateCookie,time()+3600);
				$this->redirect($this->pathMySelf);
			}

			if ($this->Input->get("act")=="copy")
			{
				$strReturn = $this->copyVHost($this->Input->get('dir'));
					
				$this->Template->infotext = $strReturn;
			}
			
		
			if ($this->Input->get("act")=="delete")
			{
				$dirName = TL_ROOT.'/vhosts/'.$this->Input->get('dir');
			
				$arrConfigReturn =$this->c2g_functions->loadVHostConfig($dirName);
				
				$sqlHost = $GLOBALS['TL_CONFIG']['dbHost'];
				if ($GLOBALS['TL_CONFIG']['dbPort'])
					$sqlHost .=":".$GLOBALS['TL_CONFIG']['dbPort'];
						
		
				$connection = mysqli_connect($sqlHost,$GLOBALS['TL_CONFIG']['dbUser'],$GLOBALS['TL_CONFIG']['dbPass'],$arrConfigReturn['TL_CONFIG']['dbDatabase'],$arrConfigReturn['TL_CONFIG']['dbPort']);
		
							
				if ($connection)
				{		
					mysqli_query($connection,sprintf("DROP DATABASE IF EXISTS `%s`",$arrConfigReturn['localconfig']['dbDatabase']));
					mysqli_close($connection);
				}
				
				$this->c2g_functions->rrmdir($dirName);
						
				$this->redirect($this->pathMySelf);
			}
		}
		else
		{
		
			
			foreach ($arrVHosts as $hosts)
			{
				$arrOutput = array();
				$sourceLink = sprintf("%svhosts/%s",$path,$hosts);
				
				if (is_dir(TL_ROOT.'/vhosts/'.$hosts))
				{
					
					$arrReturn =$this->c2g_functions->loadVHostConfig(TL_ROOT.'/vhosts/'.$hosts);
					
					
					$BELink = 'contao';
					
					if(version_compare($arrReturn['constants']['VERSION'] . '.' . $arrReturn['constants']['BUILD'], '2.9.0', '<')) 
					{ 
					   $BELink = 'typolight';
					} 
				
					$arrOutputLinks = array();

					$arrOutput['header'] = sprintf($GLOBALS['TL_LANG']['tl_content']['c2g_vhost_directory'],$hosts);
					$arrOutput['buttons'][] = sprintf('<a href="%s" title="Frontend" %s>Frontend</a>',$sourceLink,LINK_NEW_WINDOW);
					$arrOutput['buttons'][] = sprintf('<a href="%s/%s" title="Backend" %s>Backend</a>',$sourceLink,$BELink,LINK_NEW_WINDOW);
				
					if (!$GLOBALS['TL_CONFIG']['c2g_disablesnapshoting'])
					{
						$arrOutput['buttons'][] = sprintf('
												<a href="%s?dir=%s&amp;act=backup" title="%s">
													<img src="system/themes/default/images/root.gif" alt="&nbsp;" />
												</a>',$pathBackups,
														htmlspecialchars($hosts),
														$GLOBALS['TL_LANG']['tl_content']['c2g_createbackup']);
					
						$arrOutput['buttons'][] = sprintf('
												<a href="%s?dir=%s&amp;act=copy" title="%s">
													<img src="system/themes/default/images/copy.gif" alt="&nbsp;" />
												</a>',$this->pathMySelf ,
														htmlspecialchars($hosts),
														$GLOBALS['TL_LANG']['tl_content']['c2g_copyvhost']);
					
													
													
						$arrOutput['buttons'][] = sprintf('
											<a href="%s?dir=%s&amp;act=delete" title="%s" onclick="if (!confirm(\'%s\')) return false; else AjaxRequest.displayBox(\'%s\');">
												<img src="system/themes/default/images/delete.gif" alt="&nbsp;" />
											</a>',$this->pathMySelf ,
													htmlspecialchars($hosts),
													$GLOBALS['TL_LANG']['tl_content']['c2g_deletepackage'],
													sprintf($GLOBALS['TL_LANG']['tl_content']['c2g_deletevhosts_confirm'],$hosts),
													$GLOBALS['TL_LANG']['tl_content']['c2g_pleasewait']);
					}
								
					$filePackagePHP = TL_ROOT.'/vhosts/'.$hosts.'/package.php';
					$filePackageXML = TL_ROOT.'/vhosts/'.$hosts.'/package.info';
					
					$arrOutput['description'] = sprintf($GLOBALS['TL_LANG']['tl_content']['c2g_vhost_softwareversion'],$arrReturn['constants']['VERSION'] . '.' . $arrReturn['constants']['BUILD']);
					$arrOutput['description'] .='<br />';
					
					if (file_exists($filePackagePHP))
					{
						include($filePackagePHP);
						
						$arrOutput['description'] .= $GLOBALS['Description'];
					}
					
					if (file_exists($filePackageXML))
					{
						
						$descXML = simplexml_load_file($filePackageXML);
						
						$myLang = 'en';
						$httpLang = $GLOBALS['TL_LANGUAGE'];
						
						if (isset($descXML->$httpLang))
						{
							$myLang = $httpLang;
						}
						
						
						$arrOutput['description'] .= $descXML->$myLang->description;
					}
					
					
					if ($GLOBALS['IS_NO_CONTAO'])
					{
						unset($arrOutput['buttons']);
						unset($arrOutput['description']);
					}
					
					
					$arrItems[$hosts][] = $arrOutput;
					
					
					$strCookie = 'statevhost_'.standardize($hosts);
					
					if ($this->Input->cookie($strCookie))
					{
						
						$arrGroups[$hosts]['cssStyle'] =  'hide';
						$arrGroups[$hosts]['stateText'] = sprintf($GLOBALS['TL_LANG']['tl_content']['c2g_vhost_showdirectory'],$hosts);
					}
					else
					{
						$arrGroups[$hosts]['cssStyle'] =  'visible';
						$arrGroups[$hosts]['stateText'] = sprintf($GLOBALS['TL_LANG']['tl_content']['c2g_vhost_hidedirectory'],$hosts);
						
					}
					
					
					$arrGroups[$hosts]['stateLink'] = $this->pathMySelf.sprintf('?act=statechange&amp;key=%s',$strCookie);
				}
			}
		}

		$this->Template->items = $arrItems;
		$this->Template->groupInfo = $arrGroups;
		
	}
	public function copyvHost($vhostDir)
	{
		$backupHosts = htmlspecialchars_decode($vhostDir);
	
		$arrOutput = array();
		
		$error = '';
		
		$sourceDir = $this->Input->get("dir");
		$destDir = $this->Input->post("c2g_vhost_destdir");
		
		if ($sourceDir==$destDir)
			$arrOutput[] = sprintf('<b>%s</b>',$GLOBALS['TL_LANG']['tl_content']['c2g_vhost_copy_already_exists']);
		
		if (($this->Input->post("FORM_SUBMIT")=='c2g_copyvhost') && ($sourceDir!=$destDir))
		{
			$newDB = standardize($destDir);
			
			$arrSourceFiles = $this->c2g_functions->getDirectoryTree('vhosts/'.$sourceDir);
			$newFolder = new Folder("vhosts/".$destDir);
			foreach ($arrSourceFiles as $file)
			{
				if (is_file(TL_ROOT.'/'.$file))
				{
					$objDestFile = new File(str_replace("vhosts/".$sourceDir,"vhosts/".$destDir,$file));
					$objSourceFile = new File($file);
					
					$strData = $objSourceFile->getContent();
					
					if (basename($file)=='localconfig.php')
					{
						$strData = $this->c2g_functions->rewriteLocalconfig($strData,
													$GLOBALS['TL_CONFIG']['dbHost'],
													$GLOBALS['TL_CONFIG']['dbPort'],
													$newDB,
													$GLOBALS['TL_CONFIG']['dbUser'],
													$GLOBALS['TL_CONFIG']['dbPass'],
													'/vhosts/'.$destDir);
													
						$arrOutput[] = sprintf('%s',$GLOBALS['TL_LANG']['tl_content']['c2g_copyvhosts_localconfig']);
					}
					
					if ($file=="vhosts/".$sourceDir.'/.htaccess')
					{
						$strData = $this->c2g_functions->rewriteHTAccess($strData,'/vhosts/'.$destDir);
						$arrOutput[] = sprintf('%s',$GLOBALS['TL_LANG']['tl_content']['c2g_copyvhosts_htaccess']);
					}
					
					if (basename($file)=='pathconfig.php')
					{
						$strData = str_replace("vhosts/".$sourceDir,"vhosts/".$destDir,$strData);
						
					}
					
					$objDestFile->write($strData);
					
					$objDestFile->close();
					$objSourceFile->close();
				}
				else
				{
					$objDir = new Folder($file);
					
				}
			}
			
			$arrConfigReturn =$this->c2g_functions->loadVHostConfig(TL_ROOT.'/vhosts/'.$sourceDir);
      
			if(IS_CONTAO3)
			{
				$objFile=new File("vhosts/".$destDir.'/system/config/pathconfig.php', true);
				$objFile->write("<?php\n\n// Relative path to the installation\nreturn '/vhosts/".$destDir."';\n");
				$objFile->close();
				$arrOutput[]=$GLOBALS['TL_LANG']['tl_content']['c2g_createpathconfig'];
			}
			
			$strNewDump =$this->c2g_functions->createMYSQLDump($arrConfigReturn['localconfig']['dbDatabase'],$newDB);
			$this->c2g_functions->restoreDump($strNewDump);
			$arrOutput[] = sprintf('%s',$GLOBALS['TL_LANG']['tl_content']['c2g_copyvhosts_databasecopied']);
							
			$arrOutput[] = sprintf('<a href="%s" title="%s">%s</a>',
									$this->pathMySelf,
									$GLOBALS['TL_LANG']['tl_content']['c2g_backupreturntooverview'],
									$GLOBALS['TL_LANG']['tl_content']['c2g_backupreturntooverview']);
			

			$pathBackups = $this->replaceInsertTags(sprintf('{{link_url::%s}}',$this->c2g_listvhosts));			
			$arrOutput[] = sprintf('<a href="%s" title="%s">%s</a>',
									$pathBackups,
									$GLOBALS['TL_LANG']['tl_content']['c2g_backupreturntovhosts'],
									$GLOBALS['TL_LANG']['tl_content']['c2g_backupreturntovhosts']);
			
			
						
		}
		else
		{
		
		
			$backupTime = time();
			$fileName = standardize($backupHosts.'__'.$backupTime);
				
				
			$arrOutput[] = '
<form action="" id="c2g_copyvhost" method="post" enctype="multipart/form-data">

<div class="formbody">
<input type="hidden" name="FORM_SUBMIT" value="c2g_copyvhost" />
<input type="hidden" name="MAX_FILE_SIZE" value="200000000" />
<input type="hidden" name="REQUEST_TOKEN" value="{{request_token}}" />
<div class="tl_error">'.$error.'</div>
  <div class="label"><label>'.$GLOBALS['TL_LANG']['tl_content']['c2g_vhost_sourcedir'].'</label> </div>
  /vhosts/'.$this->Input->get("dir").'<br />
  <div class="clear"></div>
  <div class="label"><label>'.$GLOBALS['TL_LANG']['tl_content']['c2g_vhost_destdir'].'</label> </div>/vhosts/
  <input type="text" name="c2g_vhost_destdir" id="ctrl_c2g_form_description" class="text"><br />
	<div class="submit_container"><input type="submit" id="ctrl_c2g_submit" class="submit" value="'.$GLOBALS['TL_LANG']['tl_content']['c2g_form_submit'].'" /></div>
</div>
</form>
';
		}

		return implode("<br />\r\n",$arrOutput);

	}

	

}

?>