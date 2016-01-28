<?php
namespace Zeyon;

loadCommon('tcpdf/tcpdf'); // TCPDF before FPDI
loadCommon('fpdi/fpdi');

// -------------------- Implementation --------------------

class iXmlPdf extends \FPDI {
  public $pdf_ixml;
  public $pdf_templates = [];
  public $pdf_header;
  public $pdf_footer;
  public $pdf_styles;

  public function pdf_run($elem, $styles = []) {
    $preserve = $this -> pdf_styles;
    $this -> pdf_setStyles($styles);

    $e = null;

	  try {
      $this -> pdf_ixml -> runPdfContent($elem, $this);
	  } catch (\Exception $e) {}

    $this -> pdf_setStyles($preserve);

	  if ($e)
	    throw $e;
  }

  public function pdf_setStyles($styles = []) {
    $styles OR $styles = [
      'ALIGN' => 'L',
      'BGCOLOR' => '',
      'BORDER' => '',
      'BORDERCOLOR' => '#000',
      'BORDERWIDTH' => $this -> GetLineWidth(),
      'DIR' => 'LTR',
      'FONT' => 'arial',
      'FONTSIZE' => 12,
      'FONTSPACE' => 0,
      'FONTSTRETCH' => 100,
      'FONTSTYLE' => '',
      'LINEHEIGHT' => 0,
      'PADDING' => 0,
      'TEXTCOLOR' => '#000',
      'VALIGN' => ''
    ];

    $this -> pdf_styles = $styles;

    switch ( $font = strtolower($styles['FONT']) ) {
      case 'arial':
        $font = 'freesans'; // 'arialunicid0'
        break;

      case 'serif':
      case 'sans':
      case 'mono':
        $font = "free$font";
        break;
    }

    $this -> setTempRTL($styles['DIR']);
    $this -> SetFont($font, $styles['FONTSTYLE'], $styles['FONTSIZE']);
    $this -> setFontSpacing($styles['FONTSPACE']);
    $this -> setFontStretching($styles['FONTSTRETCH']);
    $this -> SetCellPadding($styles['PADDING']);
    $this -> SetLineWidth($styles['BORDERWIDTH']);
    $this -> SetFillColorArray($this -> pdf_convertColor($styles['BGCOLOR']));
    $this -> SetDrawColorArray($this -> pdf_convertColor($styles['BORDERCOLOR']));
    $this -> SetTextColorArray($this -> pdf_convertColor($styles['TEXTCOLOR']));
  }

  public function pdf_cutText($text, $width) {
    $width > 0 OR $width = $this -> getPageWidth() - $this -> GetX() - $this -> getMargins()['right'];

    while ($this -> GetStringWidth($text) >= $width)
      $text = mb_substr($text, 0, -1);

    return $text;
  }

  public function pdf_convertColor($color) {
    return \TCPDF_COLORS::convertHTMLColorToDec($color, $this -> spot_colors);
  }

  public function Header() {
    if ($this -> pagegroups) {
      if (isset($this -> pdf_templates[ $page = $this -> getGroupPageNo() ]))
        $this -> useTemplate($this -> pdf_templates[$page]);
      else if (isset($this -> pdf_templates['']))
        $this -> useTemplate($this -> pdf_templates['']);
    }

    $this -> pdf_header && $this -> pdf_run($this -> pdf_header);
  }

  public function Footer() {
    if ($this -> pdf_footer) {
      $this -> SetY(0, false);
      $this -> pdf_run($this -> pdf_footer);
    }
  }
}