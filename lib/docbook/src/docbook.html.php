<?php
namespace Docbook;

/**
 * HTML parser for DocBook to create light-weight, single-file HTML documents
 *
 * @uses \Xily\Xml
 * @author Peter-Christoph Haider <peter.haider@zeyon.net>
 * @package docbook
 * @version 2.00 (2010-01-08)
 * @copyright Zeyon GmbH & Co. KG
 * @license GNU Public License
 */
class HtmlParser extends Parser {
	/** @var array Extend or modify $arrConverters of the original docbook */
	public $arrExtend = array(
		'chapter'           => 'section',
		'itemizedlist'      => 'list',
		'orderedlist'       => array('list', 'Enumerate'),
		'table'             => 'table',
		// Image function parameters: URL, width, inline, caption
		'inlinemediaobject' => array('image', 'imageobject.imagedata.@fileref', 'imageobject.imagedata.@width', 1, 'imageobject.imagedata.@align'),
		'screenshot'        => array('image', 'mediaobject.imageobject.imagedata.@fileref', 520, 0, 'imageobject.imagedata.@align', 'caption.para'),
		'mediaobject'       => array('image', 'imageobject.imagedata.@fileref', 'imageobject.imagedata.@width', 0, 'imageobject.imagedata.@align', 'caption.para')
	);

	private $arrConvertersExtensions = array(
		'keycap'            => 'keycap',
		'subscript'         => 'subscript',
		'superscript'       => 'superscript',
	);

	public function __construct($mxtDoc, $bolLoadFile=false) {
		$this->arrConverters = $this->arrConvertersExtensions + $this->arrConverters;
		parent::__construct($mxtDoc, $bolLoadFile);
	}

	/* ============================== Parsing functions ============================== */

	/**
	 * Parses the mixed content of a XML node (CDATA and XML)
	 *
	 * @param \Xily\Xml $xmlNode The DocBook XML node
	 * @return string Translated HTML code
	 */
	public function parse_content($xmlNode) {
		$strResult = '';
		$arrContent = $xmlNode->content();
		foreach ($arrContent as $child) {
			if ($child instanceof \Xily\Xml)
				$strResult .= $this->parseTag($child);
			else
				$strResult .= htmlspecialchars($child);
		}
		return $strResult;
	}

	public function parse_doc($xmlNode) {
		$strTitle = '<h1>'.$xmlNode->trace('title').'</h1>'."\n";
		return $strTitle.$this->parse_content($xmlNode);
	}

	public function parse_para($xmlNode) {
		return '<div class="para">'.$this->parse_content($xmlNode).'<div class="clear"></div></div>'."\n";
	}

	public function parse_keycap($xmlNode) {
		return '<span class="keycap">'.$this->parse_content($xmlNode).'</span>'."\n";
	}

	public function parse_subscript($xmlNode) {
		return '<sub>'.$this->parse_content($xmlNode).'</sub>'."\n";
	}

	public function parse_superscript($xmlNode) {
		return '<sup>'.$this->parse_content($xmlNode).'</sup>'."\n";
	}

	public function parse_section($xmlNode) {
		$id = $this->addSection($xmlNode);
		$strHeadline = '<div class="'.$xmlNode->tag().'"'.($id ? ' name="'.$id.'" id="'.$id.'"' : '').'>'.$xmlNode->trace('title').'</div>'."\n";
		return $strHeadline.'<div class="content">'.$this->parse_content($xmlNode).'</div>'."\n";
	}

	public function parse_table($xmlNode) {
		$strResult  = '<table>'."\n";
		$strResult .= "\t".'<thead>';
		$strResult .= "\t\t".'<tr>';
		$arrCols = $xmlNode->getNodesByPath('tgroup.thead.row.entry');
		foreach ($arrCols as $col)
			$strResult .= "\t\t\t".'<th>'.$col->value().'</th>'."\n";
		$strResult .= "\t\t".'</tr>'."\n";
		$strResult .= "\t".'</thead>'."\n";
		$strResult .= "\t".'<tbody>';
		$arrRows = $xmlNode->getNodesByPath('tgroup.tbody.row');
		$even = false;
		foreach ($arrRows as $row) {
			$strResult .= "\t\t".'<tr'.($even ? ' class="even"' : '').'>';
			$arrItems = $row->children('entry');
			foreach ($arrItems as $item)
				$strResult .= "\t\t\t".'<td>'.$this->parse_content($item).'</td>'."\n";
			$strResult .= "\t\t".'</tr>'."\n";
			$even = !$even;
		}
		$strResult .= "\t".'</tbody>'."\n";
		$strResult .= '</table>'."\n";
		return $strResult;
	}

	public function parse_list($xmlNode, $strType='Itemize') {
		$strResult  = '<ul class="list'.$strType.'">'."\n";
		$arrItems = $xmlNode->children('listitem');
		foreach ($arrItems as $item)
			$strResult .= "\t".'<li>'.$this->parse_content($item).'</li>'."\n";
		$strResult .= '</ul>'."\n";
		return $strResult;
	}

	function parse_image($xmlNode, $strURL, $mxtWidth, $bolInline, $strAlignment, $strCaption=false) {
		$intWidth = false;
		if ( is_int($mxtWidth) )
			$intWidth = $mxtWidth;
		else
			$intWidth = $xmlNode->trace($mxtWidth);

		if ( !$strFile = $xmlNode->trace($strURL) ) {
			throw new Exception('Could not trace image URL for tag "'.$xmlNode->tag().'". The path is "'.$strURL.'"');
			return;
		}
		if ( $strCaption )
			$strCaption = $xmlNode->trace($strCaption);

		$strResult  = '';
		if ( !$bolInline ) {
			$strResult .= '<div class="imgFrame">'."\n";
		}
		$strResult .= '<img src="'.$this->strImgURL.$strFile.'"';
		if ( $intWidth )
			$strResult .= ' width="'.$intWidth.'"';

		$alignment = $xmlNode->trace($strAlignment);
		if ( (string)$alignment !== '' )
			$strResult .= ' align="'.htmlentities($alignment, ENT_QUOTES, 'UTF-8').'"';

		$strResult .= ' />'."\n";
		if ( !$bolInline ) {
			if ( $strCaption )
				$strResult .= '<div>'.htmlspecialchars($strCaption).'</div>'."\n";
			$strResult .= '</div>'."\n";
		}
		return $strResult;
	}

	public function parse_inline($xmlNode, $strClass=false) {
		return '<span'.($strClass ? ' class="'.$strClass.'"' : '').'>'.$this->parse_content($xmlNode).'</span>';
	}

	public function parse_listing($xmlNode) {
		return '<pre><![CDATA['.$this->parse_content($xmlNode).']]></pre>'."\n";
	}

	public function parse_link($xmlNode) {
		return '<a href="'.$xmlNode->attribute('xlink:href').'">'.$xmlNode->value().'</a>';
	}

	public function parse_box($xmlNode, $strClass='info') {
		return '<div class="box '.$strClass.'">'.$this->parse_content($xmlNode).'</div>'."\n";
	}
}

?>
