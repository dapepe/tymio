<?php

class Localizer extends KickstartLocalizer {
	/** @var Localizer The singelton instance */
	private static $instance = NULL;
	
	/**
	 * Return the current instance
	 *
	 * @param array $arrAccepted All accepted languages
	 * @param string $strDefault Default language code
	 * @return Localizer
	 */
	public static function getInstance($arrAccepted=array(), $strDefault='de') {
		if (self::$instance === NULL)
			self::$instance = new self($arrAccepted, $strDefault);
		return self::$instance;
	}
	
	/**
	 * Write the cache file
	 * 
	 * @param string $strCachePath The caching path
	 * @param string $strLang  
	 * @return bool
	 */
	public function writeCacheFile($strCachePath, $strLang) {
		if (is_dir($strCachePath) && is_array($this->arrData)) {
			file_put_contents($strCachePath.$strLang.'.php', '<?php '."\n\n"
			.'/* Language file "'.$strLang.'; Generated '.date('Y-m-d H:i').' */'."\n\n".'$LANG = '.var_export($this->arrData, true).';'."\n".'?>');
			
			file_put_contents($strCachePath.$strLang.'.json', json_encode($this->arrData));
			
			return true;
		}
		
		return false;
	}
	
	public function getJSON($strCachePath=false) {
		if ($strCachePath) {
			$strCachePath = xilyBase::fileFormatDir($strCachePath);
			if (file_exists($strCachePath.$this->strCurrent.'.json'))
				return file_get_contents($strCachePath.$this->strCurrent.'.json');
		}
		
		return json_encode($this->arrData);
	}
}