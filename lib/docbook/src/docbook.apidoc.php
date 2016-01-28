<?php
namespace Docbook;

/**
 * Creates an HTML documentation based on APIdoc syntax
 *
 * Valid documentation parameters are
 * 	cmd COMMAND NAME
 * 	param {TYPE} NAME* DESCRIPTION (The asterisk indicates, if the parameter is optional)
 * 	return {TYPE} DESCRIPTION
 * 	method METHOD
 * 	example EXAMPLE
 * 	description DESCRIPTION
 * 	see NODE_ID
 *
 * @uses \Xily\Xml
 * @author Peter-Christoph Haider <peter.haider@zeyon.net>
 * @package docbook
 * @version 1.00 (2011-03-13)
 * @copyright Zeyon GmbH & Co. KG
 * @license GNU Public License
 */
class APIdoc extends HtmlParser {

	const FORM_ENCODING_MULTIPART  = 'multipart/form-data';
	const FORM_ENCODING_URLENCODED = 'application/x-www-form-urlencoded';

	/**
	 * Defaults to {@link FORM_ENCODING_URLENCODED}.
	 *
	 * @var string
	 */
	private $encType = self::FORM_ENCODING_URLENCODED;

	/** @var string HTML template */
	public $strTemplate = '';
	/** @var int Currently open section level */
	public $intLastLevel = 0;
	/** @var array Section numberin */
	public $arrSectionNums = array();
	/** @var array Table of contents */
	public $arrSectionList = array();
	/** @var string The base path for the source files */
	public $strSRCPath = './';
	/** @var string The base path for image files */
	public $strImgURL = './';
	/** @var string The URL for demo requests */
	public $strDemoURL = false;
	/** @var array JavaScript parts */
	public $arrJS = array();
	/** @var array CSS parts */
	public $arrCSS = array();
	/** @var array Header includes */
	public $arrHeadIncludes = array();

	public function __construct($mxtDoc, $bolLoadFile=false, $strTemplate='') {
		parent::__construct($mxtDoc, $bolLoadFile);
		$this->strTemplate = $strTemplate;

		$this->srcParser = new \Docbook\SrcParser();
		$this->srcParser->setWrapper('/*!', '*/');
		$this->srcParser->setNodeTemplate(array(
			'cmd' => false,
			'params' => array()
		));
	}

	/**
	 * Sets the form encoding type (i.e. the "enctype" attribute).
	 *
	 * If this method is never called, the default encoding type will be
	 * {@link FORM_ENCODING_URLENCODED}.
	 *
	 * @param string $encType The encoding type. Should be one of these:
	 *     - {@link FORM_ENCODING_MULTIPART}
	 *     - {@link FORM_ENCODING_URLENCODED}
	 * @return self Returns this instance for method chaining.
	 * @uses $encType
	 */
	public function setFormEncoding($encType) {
		$this->encType = $encType;
		return $this;
	}

	public function build() {
		$this->arrConverters['api'] = 'api';
		$this->arrConverters['head'] = 'head';
		$this->arrConverters['body'] = 'content';
	}

	public function setWrapper($strCommentStart, $strCommentEnd) {
		$this->srcParser->setWrapper($strCommentStart, $strCommentEnd);
	}

	public function addCSS($strCSS) {
		$this->arrCSS[] = $strCSS;
	}

	public function addJS($strJS) {
		$this->arrJS[] = $strJS;
	}

	public function addInclude($strInclude) {
		$this->arrHeadIncludes[] = $strInclude;
	}

	public function setDemoURL($strURL) {
		$this->strDemoURL = $strURL;
	}

	/**
	 * DEPRECATED due to typo "SCR". Use {@link setSourcePath()} instead.
	 *
	 * @deprecated since 2011-10-04
	 */
	public function setSCRPath($strPath) {
		$this->setSourcePath($strPath);
	}

	public function setSourcePath($strPath) {
		$this->strSRCPath = \Xily\Base::fileFormatDir($strPath, '/');
	}

	public function setImgURL($url) {
		$this->strImgURL = \Xily\Base::fileFormatDir($url, '/');
	}

	public function parse() {
		$head = $this->parseTag($this->xmlDoc->child('head'));
		$body = $this->parseTag($this->xmlDoc->child('body'));

		$toc = '<div id="toc">';
		$maxpadding = 0;
		foreach ($this->arrSectionList as $key => $value)
			$toc .= '<a href="#sect'.$key.'" class="lev'.substr_count($key, '.').'" style="padding-left: '.(substr_count($key, '.') * 10).'px">'.$value.'</a>';
		$toc .= '</div><div class="headerBar"></div>';

		return array(
			'title' => $this->xmlDoc->trace('head.title'),
			'js' => implode("\n", $this->arrJS),
			'css' => implode("\n", $this->arrCSS),
			'includes' => implode("\n", $this->arrHeadIncludes),
			'copyright' => $this->xmlDoc->trace('head.copyright'),
			'head' => $head,
			'toc' => $toc,
			'body' => $body,
			'sections' => $this->arrSectionList
		);
	}

	public function display() {
		$xlyTemplate = new \Xily\Dictionary($this->parse());
		return $xlyTemplate->insertInto($this->strTemplate);
	}

	public function parse_head($xmlNode) {
		$arrLines = array(
			'version' => 'Version',
			'date' => 'Date',
			'copyright' => 'Copyright',
			'author' => 'Author'
		);

		$html = '<h1>'.$xmlNode->trace('title').'</h1>'
				.'<div class="headerBar"></div>'
				.'<table>';
		foreach ($arrLines as $key => $label) {
			if ($value = $xmlNode->trace($key))
				$html .= '<tr><td>'.$label.'</td><td>'.$value.'</td></tr>';
		}
		$html .= '</table>'
				.'<div class="headerBar"></div>';
		if ($value = $xmlNode->trace('abstract'))
			$html .= '<p class="abstract">'.$value.'</p>'
					.'<div class="headerBar"></div>';

		return $html;
	}

	public function parse_api($xmlNode) {
		if (!$xmlNode->hasAttribute('source'))
			throw new Exception('API node has no source');

		$arrComments = $this->srcParser->parseFile(\Xily\Base::fileFormatDir($this->strSRCPath, '/').$xmlNode->attribute('source'));

		$html = '';
		$intLevel = $xmlNode->hasAttribute('level') ? $xmlNode->attribute('level') : 2;

		foreach ($arrComments as $arrComment) {
			$strCommentBody = '';
			if (isset($arrComment['description']))
				$strCommentBody .= '<p>'.$arrComment['description'].'</p>';
			if (isset($arrComment['method']))
				$strCommentBody .= '<p><span class="subhead">Method</span>: '.$arrComment['method'].'</p>';
			if (isset($arrComment['param'])) {
				$strCommentBody .= '
					<p><b>Parameter List</b></p>
					<table class="list">
						<tr>
							<th>Parameter</th>
							<th>Type</th>
							<th>Default</th>
							<th>Required</th>
							<th>Description</th>
						</tr>
				';
				foreach ($arrComment['param'] as $arrParam) {
					$strCommentBody .= '
						<tr>
							<td>'.$arrParam['name'].'</td>
							<td>'.$arrParam['type'].'</td>
							<td>'.(isset($arrParam['default']) ? $arrParam['default'] : '-').'</td>
							<td>'.($arrParam['optional'] ? 'No' : 'Yes').'</td>
							<td>'.$arrParam['description'].'</td>
						</tr>
					';
				}
				$strCommentBody .= '</table>';
			}

			if (isset($arrComment['return'])) {
				$strCommentBody .= '<p><span class="subhead">Return value</span>: <i>'.$arrComment['return']['type'].'</i>'
						.($arrComment['return']['description'] == '' ? '' : ' &rarr; '.$arrComment['return']['description']).'</p>';
			}

			if (isset($arrComment['see'])) {
				if ($xmlSee = $xmlNode->getNodeByPath('see(@ref == "'.$arrComment['see'].'")'))
					$strCommentBody .= $this->parse_content($xmlSee);
			}

			if ($this->strDemoURL && isset($arrComment['demo'])) {
				if ($arrComment['demo'] == '')
					$arrComment['demo'] = $arrComment['cmd'];
				if ($xmlDemo = $xmlNode->getNodeByPath('demo(@ref == "'.$arrComment['demo'].'")')) {
					$strCommentBody .= '<div class="demo_box"><table class="view"><tr>';
					$strCommentBody .= '<td class="demo_title"><div>DEMO</div></td><td>';
					$strCommentBody .= '<form action="'.$this->strDemoURL.'" method="POST" target="_blank" enctype="'.htmlentities($this->encType, ENT_QUOTES, 'UTF-8').'">'; // "enctype" MUST be "multipart/form-data" to support file uploads
					$strCommentBody .= '<table class="view demo_table">';
					foreach ($xmlDemo->children() as $xmlParam) {
						$strParamName = $xmlParam->attribute('name');
						$strParamNameEscaped = htmlentities($strParamName, ENT_QUOTES, 'utf-8');

						$xmlParamDefault = $xmlParam->attribute('default');
						$xmlParamDefaultEscaped = htmlentities($xmlParamDefault, ENT_QUOTES, 'utf-8');

						$strParamNameEscapedValue = $xmlParam->tag() == 'array' ? $strParamNameEscaped.'[]' : $strParamNameEscaped;

						$strCommentBody .= '<tr>';
						$strCommentBody .= '<td><input type="checkbox" value="'.$strParamNameEscapedValue.'" '.($xmlParam->isFalse('active') ? '' : 'checked ').($xmlParam->isTrue('optional') ? '' : ' disabled').'/>&nbsp;</td>';
						$strCommentBody .= '<td class="demo_label">'.$strParamNameEscaped.':</td><td style="width:100%">';

						switch($xmlParam->tag()) {
							case 'array':
								$strCommentBody .= '<select class="demo_input" multiple="multiple" size="5" name="'.$strParamNameEscapedValue.'">';
								foreach ($xmlParam->children('option') as $xmlOption) {
									$strCommentBody .=
										'<option'.
											' value="'.htmlentities($xmlOption->value(), ENT_QUOTES, 'utf-8').'"'.
											( $xmlOption->isTrue('default') ? ' selected' : '' ).
										'>'.htmlentities(
											(
												$xmlOption->hasAttribute('label')
												? $xmlOption->attribute('label').' ('.$xmlOption->value().')'
												: $xmlOption->value()
											),
											ENT_QUOTES,
											'utf-8'
										).'</option>';
								}
								$strCommentBody .= '</select>';
								break;
							case 'bool':
								$strCommentBody .= '<input class="demo_input" type="radio" name="'.$strParamNameEscaped.'" value="1"'
													.($xmlParam->isTrue('default') ? ' checked' : '').' /> Yes '
													.'<input class="demo_input" type="radio" name="'.$strParamNameEscaped.'" value="0" '.($xmlParam->isFalse('default') ? ' checked' : '')
													.'/> No';
								break;
							case 'file':
								$strCommentBody .= '<input class="demo_input" type="file" name="'.$strParamNameEscaped.'" />';
								break;
							case 'text':
								$strCommentBody .= '<textarea class="demo_input" name="'.$strParamNameEscaped.'" style="height: '.($xmlParam->hasAttribute('height') ? $xmlParam->attribute('height') : '80px').'">'.$xmlParamDefaultEscaped.'</textarea>';
								break;
							case 'date':
								$strCommentBody .= '<input class="demo_input date" type="text" name="'.$strParamNameEscaped.'" value="'.$xmlParamDefaultEscaped.'"'.($xmlParam->isTrue('fixed') ? ' readonly="true"' : '').' />';
								break;
							default:
								$xlsOptions = $xmlParam->children('option');
								if (!$xlsOptions) {
									$strCommentBody .= '<input class="demo_input text" type="'.($xmlParam->isTrue('password') ? 'password' : 'text').'" name="'.$strParamNameEscaped.'" value="'.$xmlParamDefaultEscaped.'"'.($xmlParam->isTrue('fixed') ? ' readonly="true"' : '').' />';
									break;
								}

								$strCommentBody .= '<select class="demo_input" name="'.$strParamNameEscaped.'">';
								foreach ($xlsOptions as $xmlOption) {
									$strCommentBody .=
										'<option'.
											' value="'.htmlentities($xmlOption->value(), ENT_QUOTES, 'utf-8').'"'.
											( $xmlOption->isTrue('default') ? ' selected' : '' ).
										'>'.htmlentities(
											(
												$xmlOption->hasAttribute('label')
												? $xmlOption->attribute('label').' ('.$xmlOption->value().')'
												: $xmlOption->value()
											),
											ENT_QUOTES,
											'utf-8'
										).'</option>';
								}
								$strCommentBody .= '</select>';

								break;
						}

						$strCommentBody .= '</td></tr>';
					}
					$strCommentBody .= '</table>';
					$strCommentBody .= '<div class="demo_bar"><input class="right" style="margin-left:10px" type="submit" /><select class="right"><option value="post">POST</option><option value="get">GET</option></select><div class="clear"></div></div>';
					$strCommentBody .= '</form></td></tr></table></div>';
				}
			}

			$html .= $this->insert_section($intLevel, $arrComment['cmd'], $strCommentBody);
		}

		// $html .= '<pre>'.var_export($arrComments, 1).'</pre>';

		return $html;
	}

	public function parse_section($xmlNode) {
		$intLevel = (int) substr($xmlNode->tag(), -1);
		return $this->insert_section($intLevel, $xmlNode->trace('title')).'<div class="content">'.$this->parse_content($xmlNode).'</div>';
	}

	public function parse_listing($xmlNode) {
		// If a the language attribute is specified, the syntaxhightligher brush class is used
		if ($xmlNode->hasAttribute('language'))
			return '<pre class="brush: '.$xmlNode->attribute('language').'">'.$xmlNode->value().'</pre>';
		else
			return '<div class="pre">'.str_replace(array('  ', "\t"), '&nbsp;&nbsp;', \Xily\Base::htmlEncode(rtrim(preg_replace('/^.*\n/', '', $xmlNode->value(), 1)))).'</div>';
	}

	public function insert_section($intLevel, $strTitle, $strContent=false) {
		if (!isset($this->arrSectionNums[$intLevel]))
			$this->arrSectionNums[$intLevel] = 0;

		if ($this->intLastLevel > $intLevel) {
			for ($i = $this->intLastLevel ; $i > $intLevel ; $i--) {
				if (isset($this->arrSectionNums[$i]))
					$this->arrSectionNums[$i] = 0;
			}
		}
		$this->arrSectionNums[$intLevel]++;
		$this->intLastLevel = $intLevel;

		$arrPrefix = array();
		for ($i = 1 ; $i <= $intLevel ; $i++) {
			if (!isset($this->arrSectionNums[$i]))
				$this->arrSectionNums[$i] = 1;
			$arrPrefix[] = $this->arrSectionNums[$i];
		}
		$strPrefix = implode('.', $arrPrefix);
		$this->arrSectionList[$strPrefix] = $strPrefix.'&nbsp;&nbsp;&nbsp;'.$strTitle;

		// $id = $this->addSection($xmlNode);
		return '<h'.$intLevel.'><a name="sect'.$strPrefix.'">'.$strPrefix.' '.$strTitle.'</a></h'.$intLevel.'>'.($strContent ? '<div class="content">'.$strContent.'</div>' : '')."\n";
	}
}
