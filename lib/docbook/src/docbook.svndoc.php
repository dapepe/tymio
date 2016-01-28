<?php
namespace Docbook;

/**
 * Creates an HTML documentation based on SVN changes
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
 * @uses \Xily\Dictionary
 * @author Peter-Christoph Haider <peter.haider@zeyon.net>
 * @package docbook
 * @version 1.00 (2011-03-13)
 * @copyright Zeyon GmbH & Co. KG
 * @license GNU Public License
 */
class SvnDoc extends HtmlParser {
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
	/** @var string The local SVN command */
	public $strSVNCMD = 'svn';

	public function __construct($mxtDoc, $bolLoadFile=false, $strTemplate) {
		parent::__construct($mxtDoc, $bolLoadFile);
		$this->strTemplate = $strTemplate;
	}

	public function build() {
		$this->arrConverters['head'] = 'head';
		$this->arrConverters['body'] = 'svn';
	}

	public function setSVNCMD($strCMD) {
		$this->strSVNCMD = $strCMD;
	}

	public function parse() {
		$head = $this->parseTag($this->xmlDoc->child('head'));
		$body = $this->parseTag($this->xmlDoc->child('body'));

		$xlyTemplate = new \Xily\Dictionary(array(
			'title' => $this->xmlDoc->trace('head.title'),
			'js' => '',
			'css' => '',
			'includes' => '',
			'copyright' => $this->xmlDoc->trace('head.copyright'),
			'body' => $head.$body
		));

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

	public function parse_svn($xmlNode) {
		$xmlSettings = $this->xmlDoc->child('repository');

		if (!$strURL = $xmlSettings->trace('url'))
			throw new Exception('repository.url not specified!');

		$strCMD = $this->strSVNCMD.' log --xml --use-merge-history --with-all-revprops --use-merge-history --verbose --incremental';

		$strRevision = '';
		if ($xmlSettings->child('range')->hasChildren()) {
			$strStart = $xmlSettings->trace('range.start');
			$strEnd = $xmlSettings->trace('range.end');
			if ($strStart)
				$strRevision = strpos($strStart, '-') ? '{'.$strStart.'}' : $strStart;
			if ($strEnd)
				$strRevision .= ($strStart ? ':' : '').(strpos($strEnd, '-') ? '{'.$strEnd.'}' : $strEnd);
		} else
			$strRevision = $xmlSettings->trace('range');

		if ($strRevision && $strRevision != '')
			$strCMD .= ' --revision '.$strRevision;

		if ($strUsername = $xmlSettings->trace('username'))
			$strCMD .= ' --username '.$strUsername;
		if ($strPassword = $xmlSettings->trace('password'))
			$strCMD .= ' --password '.$strPassword;

		$strCMD .= ' '.$strURL;

		$strXML = shell_exec($strCMD);

		try {
			$xmlSVN = \Xily\Xily::create('<?xml version="1.0" encoding="UTF-8"?><svnlog>'.$strXML.'</svnlog>');

			return '<pre>'.$xmlSVN->toTree().'</pre>';
		} catch (xmlException $e) {
			return '<div class="error">'.$e->getMessage().'</div><pre>'.$strCMD."\n\n".$strXML.'</pre>';
		}

	}
}
