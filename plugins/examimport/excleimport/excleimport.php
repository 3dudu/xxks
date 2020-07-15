<?php
/**
 */
require VENDOR_PATH . '/vendor/autoload.php';

class excleimport extends exambase
{
    public function tempConf(){
        return array(
            "ctype"=>'application/vnd.ms-excel',
            "file"=>'excleimport.xlsx',
            "filename"=>'excleimport.xlsx',
			"name"=>'试题模板2-excle',
			"ext"=>'xlsx',
        );
    }

    public function import($excefile){
		require_once IWeb::$app->getBasePath() . '/lib/core/util/phpexcel/PHPExcel.php';
		require_once IWeb::$app->getBasePath() . '/lib/core/util/phpexcel/PHPExcel/IOFactory.php';
		require_once IWeb::$app->getBasePath() . '/lib/core/util/phpexcel/PHPExcel/Reader/Excel5.php';
		$ext = $excefile['ext'];
		$uploadfile = $excefile['qFile'];
		$reader = PHPExcel_IOFactory::createReader(($ext == 'xls' ? 'Excel5' : 'Excel2007'));
		$excel = $reader->load($uploadfile);
		$sheet = $excel->getActiveSheet();
		$highestRow = $sheet->getHighestRow();
		$highestColumn = $sheet->getHighestColumn();
		$highestColumnCount = PHPExcel_Cell::columnIndexFromString($highestColumn);
		$values = array();
		$row = 1;
		while ($row <= $highestRow) 
		{
			$rowValue = array();
			$col = 0;
			while ($col < $highestColumnCount) 
			{
				$rowValue[] = (string)$sheet->getCellByColumnAndRow($col, $row)->getValue();
				++$col;
			}
			$values[] = $rowValue;
			++$row;
		}
		return $values;
    }
}