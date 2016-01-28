<?php

/**
 * Utility functions to parse and generate Excel files using PHPexcel
 *
 * @author Peter-Christoph Haider (Project Leader) et al.
 * @version 1.0 (2012-02-12)
 * @package Kickstart
 * @copyright Copyright (c) 2012, Groupion GmbH & Co. KG
 */

/**
 * Parses an excel file into an array
 * 
 * @param string $strFile Excel file name
 * @param int $intOffset
 * @param array $arrStruct Column structure
 * @param string $strCheck Only add the item if the value for this column is set
 */
function parseExcel($strFile, $arrStruct, $intOffset=1, $strCheck=false) {
	include_once 'lib/phpexcel/Classes/PHPExcel.php';
	
	$exlObject = PHPExcel_IOFactory::load($strFile);
	$exlSheet = $exlObject -> getSheet(0);
	$intRows = $exlSheet -> getHighestRow();
	$arrData = array();
	
	for ($r = $intOffset ; $r <= $intRows ; $r++) {
		$pass = false;
		$arrItem = array();
		foreach ($arrStruct as $col => $label) {
			PHPExcel_Cell::stringFromColumnIndex($col).$r;
			if ($label)
				$arrItem[$label] = trim($exlSheet -> getCellByColumnAndRow($col, $r) -> getValue());
		}
		
		if (($strCheck && isset($arrItem[$strCheck]) && $arrItem[$strCheck] != '') || !$strCheck)
			$arrData[] = $arrItem;
	}
	
	return $arrData;
}

/**
 * Inserts an array of data into an Excel Worksheet
 * 
 * @return PHPExcel
 */
function insertIntoExcel($arrRows, $arrCols, $arrMeta=array(), $arrCollapse=array()) {
	include_once 'lib/phpexcel/Classes/PHPExcel.php';
	
	// Insert the data
	function addRows($arrData, $intRow, $exlSheet) {
		foreach ($arrData as $arrRow) {
			if ($arrRow === false)
				continue;
			if (!is_array($arrRow))
				$arrRow = array($arrRow);
				
			foreach ($arrRow as $intCol => $mxtValue) {
				$exlCell = $exlSheet -> getCellByColumnAndRow($intCol, $intRow);
				if ($mxtValue) {
					$exlCell -> setDataType(PHPExcel_Cell_DataType::TYPE_STRING);
					if (is_array($mxtValue)) {
						if (isset($mxtValue['type']) && $mxtValue['type'] == 'number') {
							$exlCell -> setDataType(PHPExcel_Cell_DataType::TYPE_NUMERIC);
							$exlSheet -> getStyleByColumnAndRow($intCol, $intRow) -> getNumberFormat() -> setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
						}
												
						if (isset($mxtValue['styles'])) {
							$style = $exlSheet -> getStyleByColumnAndRow($intCol, $intRow);
							if (in_array('bold', $mxtValue['styles']))
								$style -> getFont() -> setBold(true);
							if (in_array('italic', $mxtValue['styles']))
								$style -> getFont() -> setItalic();
							if (in_array('underine', $mxtValue['styles']))
								$style -> getFont() -> setUnderline();
							if (in_array('strikethrough', $mxtValue['styles']))
								$style -> getFont() -> setStrikethrough();
								
							if (isset($mxtValue['styles']['size']))
								$style -> getFont() -> setSize($mxtValue['styles']['size']);
							if (isset($mxtValue['styles']['color']))
								$style -> getFont() -> setColor($mxtValue['styles']['color']);	

							if (isset($mxtValue['styles']['align'])) {
								switch ($mxtValue['styles']['align']) {
									case 'left':
										$style -> getAlignment() -> setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
										break;
									case 'right':
										$style -> getAlignment() -> setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
										break;
									case 'center':
										$style -> getAlignment() -> setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
										break;
								}
							}
						}
					}
						
					$exlCell -> setValue(is_array($mxtValue) ? (isset($mxtValue['value']) ? $mxtValue['value'] : '') : $mxtValue);
				}
			}
			$intRow++;
		}
		
		return $intRow;
	}
	
	// Create the workbook and set the meta data
	$exlWorkbook = new PHPExcel();
	if ($arrMeta) {
		$exlProp = $exlWorkbook -> getProperties();
		foreach ($arrMeta as $key => $value) {
			switch ($key) {
				case 'creator':
					$exlProp -> setCreator($value);
					$exlProp -> setLastModifiedBy($value);
					break;
				case 'title':
					$exlProp -> setTitle($value);
					$exlProp -> setSubject($value);
					break;
				case 'description':
					$exlProp -> setDescription($value);
					break;
				case 'keywords':
					$exlProp -> setKeywords($value);
					break;
				case 'category':
					$exlProp -> setCategory($value);
					break;
			}
		}
	}

	// Initialize the main worksheet
	$intRow = 1;
	$exlSheet = $exlWorkbook -> getActiveSheet();
	$exlSheet -> getDefaultStyle() -> getFont() -> setName('Calibri') -> setSize(11);
	if (isset($arrMeta['title'])) {
		$exlSheet -> setTitle(substr($arrMeta['title'], 0, 12));
		$exlSheet -> setCellValueByColumnAndRow(0, $intRow, $arrMeta['title']);
		$style = $exlSheet -> getStyleByColumnAndRow(0, $intRow) -> getFont() -> setBold(true) -> setSize(16);
		$intRow++;
	}
	
	if (isset($arrMeta['intro']) && is_array($arrMeta['intro'])) {
		$intRow++;
		$intRow = addRows($arrMeta['intro'], $intRow, $exlSheet);
	}
	
	// Initialize the columns
	$intRow++;
	foreach ($arrCols as $intCol => $mxtValue) {
		$exlSheet -> setCellValueByColumnAndRow($intCol, $intRow, isset($mxtValue['title']) ? $mxtValue['title'] : $mxtValue);
		$style = $exlSheet -> getStyleByColumnAndRow($intCol, $intRow);
		$style -> getFont() -> setBold(true);
		$style -> getBorders() -> getBottom() -> setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
		
		if (isset($mxtValue['width']))
			$exlSheet -> getColumnDimensionByColumn($intCol) -> setWidth((float) $mxtValue['width']);
	}
	$intOffset = $intRow;
	$intRow++;
	
	// Add the data rows
	$intRow = addRows($arrRows, $intRow, $exlSheet);
	
	// Add the filter
	$exlSheet -> setAutoFilterByColumnAndRow(0, $intOffset, sizeof($arrCols) - 1, $intOffset + sizeof($arrRows) - 1);
	
	foreach ($arrCollapse as $from => $to) {
		for ($i = $from ; $i < $to ; $i++) {
			$exlSheet -> getColumnDimensionByColumn($i) -> setOutlineLevel(1);
			$exlSheet -> getColumnDimensionByColumn($i) -> setVisible(false);
		}
		$exlSheet -> getColumnDimensionByColumn($to) -> setCollapsed(true);
	}
	$exlWorkbook -> setActiveSheetIndex(0);
	$exlSheet -> getCellByColumnAndRow(0, $intOffset);
	$exlSheet -> freezePaneByColumnAndRow(0, $intOffset+1);
	
	// Add the outro
	if (isset($arrMeta['intro']) && is_array($arrMeta['intro'])) {
		foreach ($arrMeta['intro'] as $introRow) {
			
			$intRow++;
		}
	}
	
	return $exlWorkbook;
}

?>