<?php

namespace Xily;

class BeanInclude extends Bean {
	public function result($xmlData, $intLevel=0) {
		if (!$this->hasAttribute('src'))
			return;
		return Bean::create(file_get_contents($this->attribute('src')))->run($xmlData);
	}
}
