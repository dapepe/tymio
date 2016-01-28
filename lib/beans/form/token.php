<?php

namespace Xily;

/**
 * form:token
 *
 * @param name
 * @param id
 *
 * @author Huy Hoang Nguyen
 */
class FormToken extends Bean {

	public function result($data, $level = 0) {
		return '<input '.\HTML::expandAttributes(array(
			'type'  => 'hidden',
			'id'    => $this->id(),
			'name'  => \Form::getTokenName(),
			'value' => \Form::getToken($this->attribute('name')),
		)).' />';
	}

}

?>
