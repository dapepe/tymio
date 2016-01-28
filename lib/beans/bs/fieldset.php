<?php

namespace Xily;

class BsFieldset extends Bean {

	/**
	 * Creates the HTML code for a form field
	 *
	 * @param \Xily\Xml $xmlField
	 */
	private function buildField($xmlField) {
		$id    = (string)$xmlField->id();
		$html  = '<div class="control-group">';
		$html .= '<label class="control-label"'.( $id === '' ? '' : ' for="'.$id.'"' ).'>'.$xmlField->attribute('label').'</label>'
				.'<div class="controls">';
		$xmlField->removeAttribute('label');
		return $html.$xmlField->run().'</div></div>';
	}

	public function result($xmlData, $intLevel=0) {
		$id   = (string)$this->id();
		$html = '<form class="form-horizontal'.($this->hasAttribute('class') ? ' '.$this->attribute('class') : '').'"'.( $id === '' ? '' : ' id="'.$id.'"' ).'><fieldset>';

		foreach ($this->children() as /* @var $xmlField \Xily\Xml */ $xmlField) {

			switch ($xmlField->tag()) {
				case 'text':
				case 'password':
				case 'file':
				case 'checkbox':
					$xmlField->setAttribute('type', $xmlField->tag());
					$xmlField->setTag('input');
					$html .= $this->buildField($xmlField);
					break;
				case 'actions':
					break;
				case 'title':
					$html .= '<h3>'.$this->value().'</h3>';
					break;
				default:
					$html .= $this->buildField($xmlField);
					break;
			}
		}

		$html .= '</fieldset>';
		if ($xmlActions = $this->child('actions'))
			$html .= '<div class="form-actions">'.$xmlActions->dump().'</div>';

		$html .= '</form>';
		return $html;
	}
}

?>