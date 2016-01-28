<?php

namespace Xily;

/**
 * Page rendering bean <xpage>
 *
 * @author Peter-Christoph Haider (Project Leader) et al.
 * @package Zfx
 * @version 1.0 (2010-09-07)
 * @copyright Copyright (c) 2011, Groupion GmbH & Co. KG
 */
class BeanXpage extends Bean {
	public $colCSS;
	public $colJSVars;
	public $colJSInit;

	private $xmlMenu;

	// ============== Object's core functions ==============

	public function build() {
		$this->initDatasets();

		// Set the view:frame object as global CSS and JavaScript collector
		$this->colCSS = new Dictionary();
		$this->root()->setLink('css', $this->colCSS);
		$this->colJSVars = new Dictionary();
		$this->root()->setLink('javavars', $this->colJSVars);
		$this->colJSInit = new Dictionary();
		$this->root()->setLink('javainit', $this->colJSInit);

		// Collector for view:include (includes CSS and JS libs in the HTML header)
		$this->colInclude = new Dictionary();
		$this->root()->setLink('include', $this->colInclude);

		// Collector for an additional header section (full width)
		$this->colHeader = new Dictionary();
		$this->root()->setLink('header', $this->colHeader);
	}

	public function result($xmlData, $intLevel=0) {
		$xlyTemplate = new Dictionary();
		$xlyTemplate->set('headermenu', $this->buildHeaderMenu());
		$xlyTemplate->set('footermenu', $this->buildFooterMenu());

		// Build the body first, otherwise includes and collectors won't be executed
		$strBody = $this->dump($xmlData);

		$xlyTemplate->set('body', $strBody);
		$xlyTemplate->set('body', $strBody);
		$xlyTemplate->set('windowtitle', $this->hasAttribute('title') ? ' - '.$this->attribute('title') : '');
		$xlyTemplate->set('header', $this->colHeader->implode());
		$xlyTemplate->set('include',
			(
				$this->hasAttribute('lang')
				? '<script type="text/javascript" src="./index.php?_lang='.$this->attribute('lang').'"></script>'."\n"
				: ''
			).
			file_get_contents(ASSET_DIR.'html/include.html').$this->colInclude->implode()
		);
		$xlyTemplate->set('css', ($this->colCSS->count() > 0) ? '<style type="text/css">'."\n".$this->colCSS->implode().'</style>' : '');
		$xlyTemplate->set('javavars', ($this->colJSVars->count() > 0) ? '<script language="JavaScript" type="text/javascript">'."\n".$this->colJSVars->implode().'</script>' : '');

		$user = \APIFactory::getAuthenticator()->getUser();

		$xlyTemplate->set('javainit',
			'<script language="JavaScript" type="text/javascript">'."\n".
			'<!--'."\n".
				'var AUTHENTICATED_USER = '.json_encode( $user === null ? null : $user->toArray() ).';'.
				(
					$this->colJSInit->count() > 0
					?
							'window.addEvent(\'domready\', function(){'."\n".
								$this->colJSInit->implode().
							"});\n"
					: ''
				)."\n".
			'// -->'."\n".
			'</script>'
		);

		// Insert the blocks into the template and return the result
		return $xlyTemplate->insertInto(file_get_contents(ASSET_DIR.'html/main.html'));
	}

	private function compileMenu($xmlMenu, $isSubmenu=false) {
		$html = '';
		$highlight = false;
		/* @var $xmlEntry Xml */
		foreach ($xmlMenu->children() as $xmlEntry) {
			$isActive = '';
			switch ($xmlEntry->tag()) {
				case 'entry':
					if (!$highlight && $this->id()) {
						if (
							($xmlEntry->hasAttribute('href') && $xmlEntry->attribute('href') == $this->id())
							|| ($xmlEntry->hasChildren() && $xmlEntry->children(null, null, null, null, array('href' => $this->id())))
						) {
							$isActive = true;
							$highlight = true;
						}
					}

					if ($xmlEntry->hasChildren()) {
						$html .= '<li class="dropdown'.($isActive ? ' active' : '').'">
							<a class="dropdown-toggle" data-toggle="dropdown" href="#">'.$xmlEntry->attribute('caption').'<b class="caret"></b></a>
							<ul class="dropdown-menu">'.$this->compileMenu($xmlEntry, true).'</ul></li>';
					} else
						$html .= '<li'.($isActive ? ' class="active"' : '').'><a href="'.buildLink($xmlEntry->attribute('href')).'">'.($xmlEntry->hasAttribute('icon') ? '<i class="icon'.($isActive ? '-white' : '').' icon-'.$xmlEntry->attribute('icon').'"></i> ' : '').$xmlEntry->attribute('caption').'</a></li>';
					break;
				case 'divider':
					$html .= $isSubmenu ? '<li class="divider"></li>' : '<li class="divider-vertical"></li>';
					break;
				case 'header':
					$html .= '<li class="nav-header">'.$xmlEntry->attribute('caption').'</li>';
					break;
			}
		}
		return $html;
	}

	public function buildHeaderMenu() {
		global $locale;

		$html = '
		<div class="navbar navbar-fixed-top">
			<div class="navbar-inner">
				<div class="container" style="width: auto;">
					<a class="brand" href="'.buildLink('clockings').'" id="loader"><img src="assets/img/logo.png" alt="tymio" /></a>';

		// Workaround with catching the exception but
		// more pretty would be a function like API->isAuthenticated()
		// instead of call complete authentication again (done in index.php)
		try {
			$authenticator = \APIFactory::getAuthenticator();
			$user = $authenticator->authUser();
			if ( $user !== null ) {
				$email = strtolower($user->getEmail());
				$html .= '<div class="nav-collapse">'
						.'<ul class="nav">'.$locale->replace($this->compileMenu(Xml::create(APP_DIR.( $user->isAdmin() ? 'menu-admin.xml' : 'menu.xml' ), 1))).'</ul>'
						.'</div>'
						.'<div id="login" style="background-image: url(\'https://secure.gravatar.com/avatar/'.md5($email).'?s=28&d=mm\')">'
						.'Logged in as '.$user->getFQN().'. <a class="btn btn-mini" href="'.buildLink('logout').'"><i class="icon icon-off"></i> Logout</a>';
			} else
				throw new Exception();

		} catch ( Exception $e ) {
			$html .= '<div id="login" class="loggedout">Not logged in <a class="btn btn-success btn-mini" href="'.buildLink('login').'"><i class="icon icon-white icon-lock"></i> Login</a>';
		}

		$html .= '
					</div>
					<ul class="nav pull-right">
						<li class="divider-vertical"></li>
					</ul>
				</div>
			</div>
		</div>';

		return $html;
	}

	public function buildFooterMenu() {
		return '';
	}
}

?>
