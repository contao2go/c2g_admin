<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');


class c2g_functions extends System
{

	function loadVHostConfig($path,$bIncludeConstants=true)
	{
		$arrReturn = array();
		$arrImportFiles = array();
		$arrImportFiles['/system/config/localconfig.php']='/\$GLOBALS\[\'TL_CONFIG\'\]\[\'(.*?)\'\]\s*=\s*\'(.*)\';/i';
			
			
		$GLOBALS['IS_NO_CONTAO'] = false;
		if ($bIncludeConstants)
		{
			if (file_exists($path.'/system/constants.php'))
			{
				$arrImportFiles['/system/constants.php'] = '/DEFINE\(\'(.*?)\',\s*\'(.*)\'\);/i';
				define('IS_CONTAO3',false);
			}
			else if (file_exists($path.'/system/config/constants.php'))
			{
				$arrImportFiles['/system/config/constants.php'] = '/DEFINE\(\'(.*?)\',\s*\'(.*)\'\);/i';
				define('IS_CONTAO3',true);
			}
			else
			{
				$arrImportFiles = array();
				$GLOBALS['IS_NO_CONTAO'] = true;
			}
		}
		
		foreach ($arrImportFiles as $importFile=>$importRegex)
		{
			$file = basename($importFile,'.php');
			
			$arrReturn[$file] = array();
			
			$strLocalConfig = file_get_contents ($path.$importFile);
			
			// extract data
			preg_match_all($importRegex, $strLocalConfig, $arrLocalConfig);
			
			foreach ($arrLocalConfig[1] as $key=>$value)
			{
				$arrReturn[$file][$value] = $arrLocalConfig[2][$key];
			}
			
		}
		
		return $arrReturn;
	}
	
	
	public function rewriteLocalconfig($strLocalConfig,$dbHost,$dbPort,$dbDatabase,$dbUser,$dbPass,$websitePath)	
	{
		$pattern = '/\$GLOBALS\[\'TL_CONFIG\'\]\[\'(.*?)\'\]\s*=\s*(.*);/i';
			
		preg_match_all($pattern, $strLocalConfig, $arrLocalConfig);
		$bPortFound = false;
		foreach ($arrLocalConfig[1] as $key=>$value)
		{
			switch (trim($value))
			{
				case 'dbHost' : 
						$strRestoreHost = $this->getGlobalDef('dbHost',$dbHost);

						$strLocalConfig = str_replace($arrLocalConfig[0][$key],$strRestoreHost,$strLocalConfig);
						break;
				case 'dbDatabase' : $strLocalConfig = str_replace($arrLocalConfig[0][$key],$this->getGlobalDef('dbDatabase',$dbDatabase),$strLocalConfig);
						break;
				case 'dbUser' : $strLocalConfig = str_replace($arrLocalConfig[0][$key],$this->getGlobalDef('dbUser',$dbUser),$strLocalConfig);
						break;
				case 'dbPass' : $strLocalConfig = str_replace($arrLocalConfig[0][$key],$this->getGlobalDef('dbPass',$dbPass),$strLocalConfig);
						break;
				case 'websitePath' : $strLocalConfig = str_replace($arrLocalConfig[0][$key],$this->getGlobalDef('websitePath',$websitePath),$strLocalConfig);
					break;
				case 'dbPort' : $strLocalConfig = str_replace($arrLocalConfig[0][$key],$this->getGlobalDef('dbPort',$dbPort),$strLocalConfig);
						
						$bPortFound = true;
						break;
					
			
				default: break;
			}
		}
				
		if (!$bPortFound)
		{
			// dbPort was unset, so add value
					
			$strLocalConfig = str_replace($strRestoreHost,$this->getGlobalDef('dbHost',$dbHost).'
'.
$this->getGlobalDef('dbPort',$dbPort),$strLocalConfig);
				
		}
	
		return $strLocalConfig;
	}
	
	
	public function rewriteHTAccess($strhtaccess,$newDir)
	{
		$pattern = '/RewriteBase \/(.*)/i';
		
		preg_match_all($pattern,$strhtaccess,$arrData);
		
		foreach ($arrData[0] as $data)
		{
			$strhtaccess = str_replace($data,sprintf("RewriteBase %s",$newDir,$strhtaccess),$strhtaccess);
		}
			
	
		return $strhtaccess;
	}
	
	
	function getGlobalDef($strKey,$strValue)
	{
		return sprintf("\$GLOBALS['TL_CONFIG']['%s']   =  %s;",$strKey,is_numeric($strValue) ? $strValue : '\''.$strValue.'\'');
	}
	

	
	public function rrmdir($dir) 
	{
		if (is_dir($dir)) 
		{
			$objects = scandir($dir);
		 
			foreach ($objects as $object) 
			{
				if ($object != "." && $object != "..") 
				{
					if (filetype($dir."/".$object) == "dir") 
						$this->rrmdir($dir."/".$object); 
					else 
						@unlink($dir."/".$object);
				}
			}
		 
			@reset($objects);
			@rmdir($dir);
		}
	 } 
	 
	 
	 
	public function createMYSQLDump($table,$diffName='')
	{
		$sqlHost = $GLOBALS['TL_CONFIG']['dbHost'];
		
		if ($GLOBALS['TL_CONFIG']['dbPort'])
			$sqlHost .=":".$GLOBALS['TL_CONFIG']['dbPort'];
						
		$conn = mysqli_connect($sqlHost,$GLOBALS['TL_CONFIG']['dbUser'],$GLOBALS['TL_CONFIG']['dbPass'],$arrConfigReturn['TL_CONFIG']['dbDatabase'],$arrConfigReturn['TL_CONFIG']['dbPort']);
		
		 
		if (!$conn) {                                                        
			die(mysqli_error());
		}
		
		$sqlDump =array();
		
		if (!$diffName)
			$diffName = $table;
		
		$sqlDump[] = sprintf("CREATE DATABASE IF NOT EXISTS `%s`;", $diffName);
		$sqlDump[] = sprintf("USE `%s`;", $diffName);



		
		mysqli_select_db($conn,$table);
		
		$tables = mysqli_query($conn,sprintf("SHOW TABLES FROM `%s`",$table));
		
		while ($cells = mysqli_fetch_array($tables)) 
		{
		
			$table = $cells[0];
			$sqlDump[] ="DROP TABLE IF EXISTS `$table`;"; 
			  
			$res = mysqli_query($conn,sprintf("SHOW CREATE TABLE `%s`;",$table));
			if ($res) 
			{
				$create = mysqli_fetch_array($res);
				
				$create[1] .= ";";
				$sqlDump[]=$create[1];
				
				$data = mysqli_query($conn,sprintf("SELECT * FROM `%s`",$table));
				$sqlFieldConfig = mysqli_query($conn,sprintf("SHOW FIELDS FROM `%s`",$table));
				$arrFieldConfig = array();
				
				$num = mysqli_num_fields($data);
				
				$arrFields = array();
				for ($i = 0;$i<$num;$i++)
				{
					$objProperty = mysqli_fetch_field_direct($data,$i);
					
					$arrFields[] = "`".$objProperty->name."`";
				}
				
				while ($row = mysqli_fetch_assoc($sqlFieldConfig)) 
				{
					$arrFieldConfig[$row['Field']] = $row;
				}
				
				
				$arrInserts = array();
				while ($row = mysqli_fetch_assoc($data))
				{
				
					
					foreach ($row as $rowKey=>$rowValue)
					{
						$strValue = "'".mysqli_real_escape_string($conn,$rowValue)."'";
					
						if (!trim($rowValue))
						{
							if ($arrFieldConfig[$rowKey]['Default'])
							{
								$strValue = $arrFieldConfig[$rowKey]['Default'];
							}	
							elseif (strtoupper($arrFieldConfig[$rowKey]['Null'])=='YES')
								$strValue = 'NULL';
								
						
						}
							
						$arrInserts[$rowKey] = $strValue;
						
					}
					
				
					
					$line = sprintf("INSERT INTO `%s` (%s) VALUES (%s);",$table,implode(",",array_keys($arrInserts)),implode(",",array_values($arrInserts)));
						
					
					$sqlDump[] = $line;
					
				}
			}
		}
		
		mysqli_close($conn);
		

		return implode("\r\n",$sqlDump);
		
	}
	
	
	public function restoreDump($text)
	{
		$sqlHost = $GLOBALS['TL_CONFIG']['dbHost'];
		
		if ($GLOBALS['TL_CONFIG']['dbPort'])
			$sqlHost .=":".$GLOBALS['TL_CONFIG']['dbPort'];
						
		$connection = mysqli_connect($sqlHost,$GLOBALS['TL_CONFIG']['dbUser'],$GLOBALS['TL_CONFIG']['dbPass'],$arrConfigReturn['localconfig']['dbDatabase'],$arrConfigReturn['localconfig']['dbPort']);
		
		if ($connection)
		{		
			mysqli_query($connection,sprintf("DROP DATABASE IF EXISTS `%s`",$arrConfigReturn['localconfig']['dbDatabase']));
						
			$arrSQL = explode("\r",$text);
							
			foreach ($arrSQL as $query)
			{
									
				if (trim($query))
				{
					$result = mysqli_query($connection,$query);
					
					if (!$result)
					{
						echo mysqli_error($connection).'<br />';
					}
				}
			}
								
			mysqli_close($connection);
		}
		else
		{
			echo "FAIL";
		}
	}
	
	
	public function getDirectoryTree( $outerDir)
	{
	
		$dir_array[] =$outerDir;
		$dirs = array_diff( scandir(TL_ROOT.'/'.$outerDir ), array( ".", ".." ) );
		
		if (!is_array($dir_array))
			$dir_array=array();
		
		foreach( $dirs as $d )
		{
		
		
			if (is_dir(TL_ROOT.'/'.$outerDir."/".$d))
			{
				$dir_array = array_merge($dir_array,$this->getDirectoryTree( $outerDir."/".$d));
			}
			else
			{
				if (substr($d,0,4)!='c2g_')
				{
					$dir_array[] =$outerDir.'/'.$d;
					
				}
			}
			
		}
		return $dir_array;
	} 
}


?>