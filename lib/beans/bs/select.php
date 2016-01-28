<?php

namespace Xily;

class BsSelect extends Bean {
	public function result($xmlData, $intLevel=0) {
		$html = '<div class="input-prepend'.($this->hasAttribute('class') ? ' '.$this->attribute('class') : '').'">'
				.'<span class="add-on"><i class="icon-'.($this->hasAttribute('icon') ? $this->attribute('icon') : 'th-list').'"></i>'
				.($this->hasAttribute('label') ? ' '.$this->attribute('label') : '')
				.'</span><select'.( (string)$this->id() !== '' ? ' id="'.$this->id().'"' : '').' style="width:auto;">';

		foreach ($this->children('option') as $xmlOption) {
			$value = $xmlOption->dump();
			$html .= '<option value="'.($xmlOption->hasAttribute('value') ? $xmlOption->attribute('value') : $value).'">'.$value.'</option>';
		}

		$html .= '</select></div>';

		return $html;
	}
}

?>