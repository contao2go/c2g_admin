<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

class c2g_listBackups extends ContentElement
{

	protected $strTemplate = 'ce_c2g_list';
	
	
	public function compile()
	{
		global $objPage;
		$this->import('c2g_functions');
		
				
		$arrBackups = scan(TL_ROOT.'/backups');

		$path = $this->replaceInsertTags('{{env::path}}');
		$this->pathMySelf = $this->replaceInsertTags(sprintf('{{link_url::%s}}',$objPage->id));

		if ($this->Input->get("act"))
		{
			if ($this->Input->get("act")=="statechange")
			{
				$stateCookie = ($this->Input->cookie($this->Input->get('key'))) ? false : true;
			
				$this->setCookie($this->Input->get('key'),$stateCookie,time()+3600);
				$this->redirect($this->pathMySelf);
			}
			
			if (!$GLOBALS['TL_CONFIG']['c2g_disablesnapshoting'])
			{
				if ($this->Input->get("act")=="delete")
				{
					unlink(TL_ROOT.'/backups/'.$this->Input->get("file"));
					$this->redirect($this->pathMySelf);
				}
			}
			
			if ($this->Input->get("act")=="restore_full")
			{
				$strReturn = $this->restoreBackup($this->Input->get("file"),true,true);
				
				$this->Template->infotext = $strReturn;
				
			}
			
			
			if ($this->Input->get("act")=="restore_db")
			{
				$strReturn = $this->restoreBackup($this->Input->get("file"),true,false);
				
				$this->Template->infotext = $strReturn;
				
			}
	
	
			if ($this->Input->get("act")=="restore_files")
			{
				$strReturn = $this->restoreBackup($this->Input->get("file"),false,true);
				
				$this->Template->infotext = $strReturn;
				
			}
	
			if (!$GLOBALS['TL_CONFIG']['c2g_disablesnapshoting'])
			{
				if ($this->Input->get("act")=="backup")
				{
					$strReturn = $this->createBackup($this->Input->get('dir'));
					
					$this->Template->infotext = $strReturn;
					
				}
			}
			
		}
		else
		{
			if (count($arrBackups)==0)
			{
				$this->Template->infotext = $GLOBALS['TL_LANG']['tl_content']['c2g_nobackupsavailable'];
			}
			else
			{
			
				$arrItems = array();
			
				foreach ($arrBackups as $hosts)
				{
					if (is_file(TL_ROOT.'/backups/'.$hosts))
					{
					
						$c2gFile = new File('/backups/'.$hosts);
						
						if (($c2gFile->extension=='c2g') && ($c2gFile->filesize>0))
						{
							$sourceLink = sprintf("%svhosts/%s",$path,$hosts);
							
							
							$c2gZipFile = new ZipReader("/backups/".$hosts);
							$descFile = $c2gZipFile->getFile("description.php");
							
							if ($descFile)
							{	
								unset($GLOBALS["package"]);
								eval($c2gZipFile->unzip());								
								
								$arrOutput = array();
								
								$desc='';
								if ($GLOBALS["package"]["Description"])
								{
									$desc = sprintf('%s : <div class="block">%s</div><br />',$GLOBALS['TL_LANG']['tl_content']['c2g_backupdescription'],
														$GLOBALS["package"]["Description"]);
								}
								
								
								$arrOutput['header'] = sprintf('%s',htmlspecialchars_decode($desc));
								$arrOutput['description'] = sprintf('%s : <i>%s</i><br />%s : <i>%s</i>',
															$GLOBALS['TL_LANG']['tl_content']['c2g_backupcreate'],
															$this->parseDate($GLOBALS['TL_CONFIG']['datimFormat'], $GLOBALS["package"]["Time"]),
															$GLOBALS['TL_LANG']['tl_content']['c2g_backupsize'],
															
															$this->getReadableSize($c2gFile->filesize));
								
								
								$requestLink = $this->Environment->request.'?file='.$hosts;
								
								if (!$GLOBALS['TL_CONFIG']['c2g_disablesnapshoting'])
								{
									$arrOutput['buttons'][] = sprintf('
										<a href="%s&amp;act=delete" title="%s" onclick="if (!confirm(\'%s\')) return false; else AjaxRequest.displayBox(\'%s\');">
											<img src="system/themes/default/images/delete.gif" alt="&nbsp;" />
										</a>',$requestLink,
												$GLOBALS['TL_LANG']['tl_content']['c2g_deletepackage'],											
												sprintf($GLOBALS['TL_LANG']['tl_content']['c2g_deletepackage_confirm'],$GLOBALS["package"]["Name"]),
												$GLOBALS['TL_LANG']['tl_content']['c2g_pleasewait']);
								}
											
								$arrOutput['buttons'][] = sprintf('
									<a href="%s?file=%s&amp;act=restore_full" title="%s" onclick="if (!confirm(\'%s\')) return false; else AjaxRequest.displayBox(\'%s\');">
										<img src="system/themes/default/images/reload.gif" alt="&nbsp;" />
									</a>',$this->Environment->request,$hosts,
											$GLOBALS['TL_LANG']['tl_content']['c2g_restorepackage'],
											sprintf($GLOBALS['TL_LANG']['tl_content']['c2g_restorepackage_confirm'],$GLOBALS["package"]["Name"]),
											$GLOBALS['TL_LANG']['tl_content']['c2g_pleasewait']);
								
								
								
								$strRestoreOutput = sprintf('<li>
									<a href="%s?file=%s&amp;act=restore_db" title="%s" onclick="if (!confirm(\'%s\')) return false; else AjaxRequest.displayBox(\'%s\');">
										%s
									</a></li>',$this->Environment->request,$hosts,
											$GLOBALS['TL_LANG']['tl_content']['c2g_restorepackage_db'],
											sprintf($GLOBALS['TL_LANG']['tl_content']['c2g_restorepackage_confirm'],$GLOBALS["package"]["Name"]),
											$GLOBALS['TL_LANG']['tl_content']['c2g_pleasewait'],
											$GLOBALS['TL_LANG']['tl_content']['c2g_restorepackage_db']);
								
								$strRestoreOutput .= sprintf('<li>
									<a href="%s?file=%s&amp;act=restore_files" title="%s" onclick="if (!confirm(\'%s\')) return false;n else AjaxRequest.displayBox(\'%s\');">
										%s
									</a></li>',$this->Environment->request,$hosts,
											$GLOBALS['TL_LANG']['tl_content']['c2g_restorepackage_files'],
											sprintf($GLOBALS['TL_LANG']['tl_content']['c2g_restorepackage_confirm'],$GLOBALS["package"]["Name"]),
											$GLOBALS['TL_LANG']['tl_content']['c2g_pleasewait'],
											$GLOBALS['TL_LANG']['tl_content']['c2g_restorepackage_files']);
											
								
								
								if (!$GLOBALS['TL_CONFIG']['c2g_disablesnapshoting'])
								{
									$arrOutput['buttons'][] = sprintf('
										<a href="backups/%s" title="%s">
											<img src="system/themes/default/images/down.gif" alt="&nbsp;" />
										</a>',$hosts,
												$GLOBALS['TL_LANG']['tl_content']['c2g_downloadpackage']);
								}
								
								
								$arrOutput['buttons'][] = '<div class="contextmenu"><ul>'.$strRestoreOutput.'</ul></div>';
								
								
								
								$arrItems[$GLOBALS["package"]["Name"]][] = $arrOutput;
							}
						}
						
						$strCookie = 'statebackup_'.standardize($GLOBALS["package"]["Name"]);
						
						if ($this->Input->cookie($strCookie))
						{
							$arrGroups[$GLOBALS["package"]["Name"]]['cssStyle'] =  'hide';
							$arrGroups[$GLOBALS["package"]["Name"]]['stateText'] = sprintf($GLOBALS['TL_LANG']['tl_content']['c2g_vhost_showdirectory'],$GLOBALS["package"]["Name"]);	
						}
						else
						{
							
							$arrGroups[$GLOBALS["package"]["Name"]]['cssStyle'] =  'visible';
							$arrGroups[$GLOBALS["package"]["Name"]]['stateText'] = sprintf($GLOBALS['TL_LANG']['tl_content']['c2g_vhost_hidedirectory'],$GLOBALS["package"]["Name"]);
						}
						
							
						$arrGroups[$GLOBALS["package"]["Name"]]['stateLink'] = $this->pathMySelf.sprintf('?act=statechange&amp;key=%s',$strCookie);
						
						
					}
					
						
				
				}
			}
		}
		
		
		
		$this->Template->groupInfo = $arrGroups;
		$this->Template->items = $arrItems;
	}

	public function createBackup($vhostDir)
	{
		$backupHosts = htmlspecialchars_decode($vhostDir);
	
		$arrOutput = array();

		if ($this->Input->post("FORM_SUBMIT")=='c2g_createbackup')
		{
			
			if (!$backupHosts)
			{
				$arrOutput[]= $GLOBALS['TL_LANG']['tl_content']['c2g_unknownvhost'];
			}

			if (!file_exists(TL_ROOT.'/vhosts/'.$backupHosts))
			{
				$arrOutput[]= $GLOBALS['TL_LANG']['tl_content']['c2g_vhostnotexist'];
			}

			if (count($arrOutput)==0)
			{
				$backupTime = time();
				$fileName = standardize($backupHosts.'____'.$backupTime);
				
				
				$arrConfigReturn =$this->c2g_functions->loadVHostConfig(TL_ROOT.'/vhosts/'.$backupHosts);
				
			
				$descFile = array();
				$descFile[] = sprintf('$GLOBALS["package"]["Name"] = "%s";',$backupHosts);
				$descFile[] = sprintf('$GLOBALS["package"]["Description"] = "%s";',htmlspecialchars(nl2br($this->Input->post("description"))));
				$descFile[] = sprintf('$GLOBALS["package"]["Time"] = "%s";',$backupTime);
				$descFile[] = '$GLOBALS["package"]["RootDir"] = "vhosts";';
						
				$arrOutput[] = $GLOBALS['TL_LANG']['tl_content']['c2g_createdescription'];
						
				$arrFiles = $this->c2g_functions->getDirectoryTree('vhosts/'.$backupHosts);
						
				$objZip = new ZipWriter("/backups/".$fileName.'.c2g');
						
				$arrDir=array();
				
				
				// add ".empty" file to all directories.
				// so, all directory including empty ones are added to the backup
				foreach ($arrFiles as $file)
				{
					if (is_dir($file))
					{
						$objEmptyFile = new File($file.'/.empty');
					
					}
				}
				
				foreach ($arrFiles as $file)
				{
					if (is_file($file))
					{
						$objSourceFile = new File($file);
					
						$strData = $objSourceFile->getContent();
					
						if (basename($file)=='localconfig.php')
						{
							$strData = $this->c2g_functions->rewriteLocalconfig($strData,
														'',
														'',
														$arrConfigReturn['localconfig']['dbDatabase'],
														'',
														'',
														$arrConfigReturn['localconfig']['websitePath']);
						}
						$objSourceFile->close();
					
						$objZip->addString($strData,str_replace("vhosts/","",$file));
					}
					else
					{
						$arrDir[$file] = $file;
					}
				}
				
				$objZip->addString($this->c2g_functions->createMYSQLDump($arrConfigReturn['localconfig']['dbDatabase']),$fileName.'.sql');
				$objZip->addString(implode("\r\n",$descFile),"description.php");
				//$objZip->addString(implode("\r\n",$arrDir),"directories.dat");
				$objZip->close();
				
				$arrOutput[] = sprintf('<a href="%s" title="%s">%s</a>',
									$this->pathMySelf,
									$GLOBALS['TL_LANG']['tl_content']['c2g_backupreturntooverview'],
									$GLOBALS['TL_LANG']['tl_content']['c2g_backupreturntooverview']);
				
			}
			
		}
		else
		{
		
		
			$backupTime = time();
			$fileName = standardize($backupHosts.'__'.$backupTime);
				
				
			$arrOutput[] = '
<form action="" id="c2g_createbackup" method="post" enctype="multipart/form-data">

<div class="formbody">
<input type="hidden" name="FORM_SUBMIT" value="c2g_createbackup" />
<input type="hidden" name="MAX_FILE_SIZE" value="200000000" />
<input type="hidden" name="REQUEST_TOKEN" value="{{request_token}}" />
  <div class="label"><label>'.$GLOBALS['TL_LANG']['tl_content']['c2g_form_title'].'</label> </div>
  '.$fileName.'<br />
  <div class="clear"></div>
  <div class="label"><label>'.$GLOBALS['TL_LANG']['tl_content']['c2g_form_description'].'</label> </div>
  <textarea name="description" id="ctrl_c2g_form_description" class="textarea" rows="5" cols="40"></textarea><br />
	<div class="submit_container"><input type="submit" id="ctrl_c2g_submit" class="submit" value="'.$GLOBALS['TL_LANG']['tl_content']['c2g_form_submit'].'" onclick="AjaxRequest.displayBox(\''.
											$GLOBALS['TL_LANG']['tl_content']['c2g_pleasewait'].'\');"/></div>
</div>
</form>
';
		}

		return implode("<br />\r\n",$arrOutput);

	}

	
	public function restoreBackup($restoreFile,$boolDB = false, $boolFiles = false)
	{
		$arrOutput = array();
			
		if ($restoreFile)
		{
			$c2gFile = new File("backups/".$restoreFile);
					
			if ($c2gFile->extension=='c2g')
			{			
				$c2gZipFile = new ZipReader("backups/".$restoreFile);
				$descFile = $c2gZipFile->getFile("description.php");
						
				if ($descFile)
				{
					unset($GLOBALS["package"]);
					eval($c2gZipFile->unzip());
					
					$arrOutput[] = sprintf($GLOBALS['TL_LANG']['tl_content']['c2g_restorebackup'],$GLOBALS["package"]["Name"]);
					$dirName = TL_ROOT.'/'.$GLOBALS["package"]["RootDir"].'/'.$GLOBALS["package"]["Name"];
					
					
					if ($boolFiles)
					{
						$strRestoreFolder = new Folder($GLOBALS["package"]["RootDir"].@'/'.$GLOBALS["package"]["Name"].@'/');
						
						if (($GLOBALS["package"]["RootDir"]!='.') && (is_dir($dirName)))
						{
							$this->c2g_functions->rrmdir($dirName);
							
							$arrOutput[] = sprintf($GLOBALS['TL_LANG']['tl_content']['c2g_restorebackup_cleandir'],$GLOBALS["package"]["Name"]);
						}
						
						
						$directoryFile = $c2gZipFile->getFile("directories.dat");
						if ($directoryFile)
						{
							$strDirectories = $c2gZipFile->unzip();
							
							$arrDirectories = (explode("\r\n",$strDirectories));
							
							if (is_array($arrDirectories))
							{
								foreach ($arrDirectories as $dir)
								{
									$objDir = new Folder($dir);
								}
							}
						}
						
						
						$c2gZipFile->first();
						
						$arrFileList = $c2gZipFile->getFileList();
						
						foreach ($arrFileList as $file)
						{
							$c2gZipFile->getFile($file);
							$objFile = new File('vhosts/'.$file);
							
							$strData = $c2gZipFile->unzip();
							
							if (basename($file)=='localconfig.php')
							{
								$objFile->write($strData);
								$objFile->close();
								
								$arrConfigReturn =  @$this->c2g_functions->loadVHostConfig(TL_ROOT.'/vhosts/'.$GLOBALS["package"]["Name"],false);
					
								$objFile = new File('vhosts/'.$file);
								
								$strData = $this->c2g_functions->rewriteLocalconfig($strData,
															$GLOBALS['TL_CONFIG']['dbHost'],
															$GLOBALS['TL_CONFIG']['dbPort'],
															$arrConfigReturn['localconfig']['dbDatabase'],
															$GLOBALS['TL_CONFIG']['dbUser'],
															$GLOBALS['TL_CONFIG']['dbPass'],
															$arrConfigReturn['localconfig']['websitePath']);
							}
							
                        
							$objFile->write($strData);

                            $objFile->close();                        
                        
                            // let the directorystructure create, but remove .empty file
                            if (basename($file)=='.empty')
                            {
                                $objFile->delete();
                            }

						}
						
						$arrOutput[] = $GLOBALS['TL_LANG']['tl_content']['c2g_restorebackup_filesystemrestored'];
						
					}
						
				
					$arrConfigReturn =$this->c2g_functions->loadVHostConfig(TL_ROOT.'/vhosts/'.$GLOBALS["package"]["Name"]);
					
					
					if ($boolDB)
					{
					
						$file = $c2gFile->filename.'.sql';
					
						$c2gZipFile->getFile($file);
						$objFile = new File('vhosts/'.$file);
						
						
						$objFile->write($c2gZipFile->unzip());
						
                       
						$objFile->close();
							
							
						$sqlDump = new File($GLOBALS["package"]["RootDir"].'/'.$file);
			
						$this->c2g_functions->restoreDump($sqlDump->getContent());						
							
						$sqlDump->close();
						
						$arrOutput[] = $GLOBALS['TL_LANG']['tl_content']['c2g_restorebackup_sqlrestored'];
						
					}
						
					$objDescFile = new File($GLOBALS["package"]["RootDir"].'/description.php');
					$objDescFile->delete();
                    
                    $objDirFile = new File($GLOBALS["package"]["RootDir"].'/directories.dat');
                    $objDirFile->delete();
						
					$objSQLFile = new File($GLOBALS["package"]["RootDir"].'/'.$c2gFile->filename.'.sql');
					$objSQLFile->delete();
						
                        
				}
			}
							
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
		return implode("<br />",$arrOutput);
	}
	
	
 
 
	
	
	
	
	
}

?>