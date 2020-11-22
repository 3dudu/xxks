<?php
/**
 * @copyright (c) 2014 aircheng
 * @file report.php
 * @brief 导出excel类库
 * @author dabao
 * @date 2014/11/28 22:09:43
 * @version 1.0.0

 * @update 4.6
 * @date 2016/9/15 23:30:28
 * @author nswe
 * @content 重构了写入方式和方法
 */
require VENDOR_PATH . '/vendor/autoload.php';

class report
{
	//文件名
	private $fileName = 'user';

	//数据内容
	private $_data    = "";

	private $title = array();
	private $dataGird = array();

	//构造函数
	public function __construct($fileName = '')
	{
		$this->setFileName($fileName);
	}

	//设置要导出的文件名
	public function setFileName($fileName)
	{
		$this->fileName = $fileName;
	}

	/**
	 * @brief 写入内容操作，每次存入一行
	 * @param $data array 一维数组
	 */
	public function setTitle($data = array())
	{
		$this->title = $data;
		array_walk($data,function(&$val,$key)
		{
			$val = "<th style='text-align:center;background-color:green;color:#fff;font-size:12px;vnd.ms-excel.numberformat:@'>".$val."</th>";
		});
		$this->_data .= "<tr>".join($data)."</tr>";
	}

	/**
	 * @brief 写入标题操作
	 * @param $data array  数据
	 */
	public function setData($data = array())
	{
		$basepath = IUrl::getHost().'/';
		if($data[3] && strpos($data[3],'http')===false){
			$data[3] = $basepath . $data[3];
		}
		$this->dataGird[] = $data;
		array_walk($data,function(&$val,$key)
		{
			$dataType = is_numeric($val) && strlen($val) >= 10 ? "vnd.ms-excel.numberformat:@" : "";
			$val = "<td style='text-align:center;font-size:12px;".$dataType."'>".$val."</td>";
		});
		$this->_data .= "<tr>".join($data)."</tr>";
	}

	//开始下载
	public function toDownload($data = '')
	{
		// Redirect output to a client’s web browser (Excel5)
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename='.$this->fileName.'_'.date('Ymd').'.xlsx');
		header('Cache-Control: max-age=0');
		// If you're serving to IE 9, then the following may be needed
		header('Cache-Control: max-age=1');

		// If you're serving to IE over SSL, then the following may be needed
		header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
		header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header ('Pragma: public'); // HTTP/1.0

		$result = $data ? $data : "<table border='1'>".$this->_data."</table>";
echo <<< OEF
<html>
	<meta http-equiv="Content-Type" content="application/vnd.ms-excel; charset=utf-8" />
	<body>
	{$result}
	</body>
</html>
OEF;
	}

	protected function column_str($key) 
	{
		$array = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ', 'BA', 'BB', 'BC', 'BD', 'BE', 'BF', 'BG', 'BH', 'BI', 'BJ', 'BK', 'BL', 'BM', 'BN', 'BO', 'BP', 'BQ', 'BR', 'BS', 'BT', 'BU', 'BV', 'BW', 'BX', 'BY', 'BZ', 'CA', 'CB', 'CC', 'CD', 'CE', 'CF', 'CG', 'CH', 'CI', 'CJ', 'CK', 'CL', 'CM', 'CN', 'CO', 'CP', 'CQ', 'CR', 'CS', 'CT', 'CU', 'CV', 'CW', 'CX', 'CY', 'CZ', 'DA', 'DB', 'DC', 'DD', 'DE', 'DF', 'DG', 'DH', 'DI', 'DJ', 'DK', 'DL', 'DM', 'DN', 'DO', 'DP', 'DQ', 'DR', 'DS', 'DT', 'DU', 'DV', 'DW', 'DX', 'DY', 'DZ', 'EA', 'EB', 'EC', 'ED', 'EE', 'EF', 'EG', 'EH', 'EI', 'EJ', 'EK', 'EL', 'EM', 'EN', 'EO', 'EP', 'EQ', 'ER', 'ES', 'ET', 'EU', 'EV', 'EW', 'EX', 'EY', 'EZ');
		return $array[$key];
	}
	protected function column($key, $columnnum = 1) 
	{
		return $this->column_str($key) . $columnnum;
	}

	public function export($titleWidth=array()) 
	{
		if (PHP_SAPI == 'cli') 
		{
			exit('This example should only be run from a Web Browser');
		}
		require_once IWeb::$app->getBasePath() . '/lib/core/util/phpexcel/PHPExcel.php';
		$excel = new PHPExcel();
		$excel->getProperties()->setCreator('学和练')->setLastModifiedBy('学和练')->setTitle('Office 2007 XLSX Test Document')->setSubject('Office 2007 XLSX Test Document')->setDescription('Test document for Office 2007 XLSX, generated using PHP classes.')->setKeywords('office 2007 openxml php')->setCategory('report file');
		$sheet = $excel->setActiveSheetIndex(0);
		$rownum = 1;
		foreach ($this->title as $key => $column ) 
		{
			$sheet->setCellValue($this->column($key, $rownum), $column);
			if(isset($titleWidth[$key])){
				$sheet->getColumnDimension($this->column_str($key))->setWidth($titleWidth[$key]);
				$sheet->getColumnDimension($this->column_str($key))->setCollapsed(true);
			}else{
				$sheet->getColumnDimension($this->column_str($key))->setAutoSize(true);
			}
			$sheet->getStyle($this->column($key, $rownum))->getFont()->setBold(true);
			$sheet->getStyle($this->column($key, $rownum))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$sheet->getStyle($this->column($key, $rownum))->getFill()->getStartColor()->setARGB('9BC2E6');
		}
		++$rownum;
		foreach ($this->dataGird as $row ) 
		{
			$i = 0;
			foreach($row as $col){
				$col = $this->htmlTransform($col);
				$sheet->setCellValue($this->column($i, $rownum), $col);
				++$i;
			}
			++$rownum;
		}
		$excel->getActiveSheet()->setTitle($this->fileName);
		$filename = urlencode($this->fileName . '-' . date('YmdHi', time()));
		ob_end_clean();
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
		header('Cache-Control: max-age=0');
		$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
		$this->SaveViaTempFile($writer);
		exit();
	}

	public function exportText(){
		$i = 1;
		$filename = urlencode($this->fileName . '-' . date('YmdHi', time()));
		header('Content-Type: text/txt');
		header('Content-Disposition: attachment;filename="' . $filename . '.txt"');
		header('Cache-Control: max-age=0');
		$ABC = ['A','B','C','D','E','F'];
		echo '导出题目'.PHP_EOL;
		foreach ($this->dataGird as $row ) 
		{
			$q = $this->htmlTransform($row[0]);
			echo '['.$row[1].'] '.$i.'. '.$q.PHP_EOL;
			if(count($row)>6){
			for($index=6;$index<count($row);$index++){
				$item = $this->htmlTransform($row[$index]);
				echo $ABC[$index-6].' '.$item.PHP_EOL;
			}}else{
				echo PHP_EOL;
			}
			echo PHP_EOL;

			$a = $this->htmlTransform($row[5]);
			echo '答案'.PHP_EOL;
			echo $a;
			echo PHP_EOL;
			echo PHP_EOL;

			$e = $this->htmlTransform($row[4]);
			echo '讲解'.PHP_EOL;
			echo $e;
			echo PHP_EOL;
			echo PHP_EOL;
			echo PHP_EOL;
			$i++;
		}
	}
	private function writeItem($section,$stem,$tag=false){
		if(isset($stem['t'])){
			$textrun = $section->addTextRun();
			if($stem['t']=='p'){
				$textrun->addTextBreak(1);
				if($tag){
					$textrun->addText($tag.'. ', 'fontStyleContent');
				}
				if(is_array($stem['c'])){
					foreach ($stem['c'] as $item ) {
						if($item['t']=='span'){
							$textrun->addText($this->htmlTransformWord($item['c']), 'fontStyleContent');
						}
						if($item['t']=='img'){
							if(isset($item['w']) && $item['w']){
								$textrun->addImage($item['src'], array('width'=>$item['w'], 'height'=>$item['h']));
							}elseif(isset($item['width']) && $item['width']){
								$textrun->addImage($item['src'], array('width'=>$item['width'], 'height'=>$item['height']));
							}else{
								$textrun->addImage($item['src']);
							}
						}
					}
				}else{
					$textrun->addText($this->htmlTransformWord($stem['c']), 'fontStyleContent');
				}
			}
			if($stem['t']=='span'){
				$textrun->addText($this->htmlTransformWord($stem['c']), 'fontStyleContent');
			}
			if($stem['t']=='img'){
				if($stem['w']){
					$textrun->addImage($stem['src'], array('width'=>$stem['w'], 'height'=>$stem['h']));
				}else{
					$textrun->addImage($stem['src'], array('width'=>$stem['width'], 'height'=>$stem['height']));
				}
			}
		}
	}
	public function exportBDWord($qlist){
		$phpWord = new \PhpOffice\PhpWord\PhpWord();
		$section = $phpWord->createSection();
		$type = array(
			'pd'=>'判断题',
			'sc'=>'单选题',
			'mc'=>'多选题',
			'qa'=>'问答题',
		);
		$ABC = ['A','B','C','D','E','F'];

		$section->addText('导出题目', 'fontStyleTitle');
		foreach ($qlist as $row ) {
			$row['bdjson'] = $this->htmlTransform($row['bdjson']);
			$bdjson = JSON::decode($row['bdjson']);
			$section->addText(isset($type[$bdjson['que_info']['type']])?$type[$bdjson['que_info']['type']]:'其他', 'fontStyleTitle');

			foreach ($bdjson['que_stem'] as $stem ) {
				$this->writeItem($section,$stem);
			}

			if(isset($bdjson['que_options'])){
				foreach ($bdjson['que_options'] as $key=>$options ) {
					foreach ($options as $option ) {
						if($bdjson['que_info']['type']=='sc' || $bdjson['que_info']['type']=='mc'){
							$tag = $ABC[$key];
						}else{
							$tag = false;
						}
						foreach ($option['ret'] as $key => $opt ) {
							if($key==0){
								$this->writeItem($section,$opt,$tag);
							}else{
								$this->writeItem($section,$opt);
							}
						}
					}
				}
			}
			$section->addText('正确答案', 'fontStyleTitle');
			if(isset($bdjson['que_answer'])){
				if(isset($bdjson['que_answer'][0]['t'])){
					foreach ($bdjson['que_answer'] as $key=>$answer ) {
						$this->writeItem($section,$answer);
					}
				}else{
					foreach ($bdjson['que_answer'] as $key=>$answers ) {
						foreach ($answers as $key=>$answer ) {
							$this->writeItem($section,$answer);
						}
					}
				}
			}
			$section->addText('', 'fontStyleTitle');
			$section->addText('', 'fontStyleTitle');
		}

		$phpWord->addFontStyle('fontStyleContent', array('size' => 20,'bold' => false));
        $phpWord->addFontStyle('fontStyleTitle', array('size' => 20,'bold' => true));

		$objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
		$filename = urlencode($this->fileName . '-' . date('YmdHi', time()));
		ob_end_clean();
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment;filename="' . $filename . '.docx"');
		header('Cache-Control: max-age=0');
		$this->SaveViaTempFile($objWriter);
		exit();
	}
	public function exportWord(){
		$phpWord = new \PhpOffice\PhpWord\PhpWord();
		$section = $phpWord->createSection();
		$i = 1;
		$ABC = ['A','B','C','D','E','F'];
		$section->addText('导出题目', 'fontStyleTitle');

		foreach ($this->dataGird as $row ) 
		{
			$q = $this->htmlTransformWord($row[0]);
			$s = explode(PHP_EOL,$q);
			$linenum = 0;
			foreach($s as $line){
				if($linenum==0){
					$section->addText('['.$row[1].'] '.$i.'. '.$line, 'fontStyleTitle');
				}else{
					$section->addText($line, 'fontStyleTitle');
				}
				$section->addTextBreak(1);
				$linenum++;
			}
			if($row[3]!="" && $row[3]!=null){
				$section->addImage($row[3]);
			}
				$section->addText($row[3], 'fontStyleContent');
			
				if(count($row)>6){
				for($index=6;$index<count($row);$index++){
					$item = $this->htmlTransformWord($row[$index]);
					$section->addText($ABC[$index-6].' '.$item, 'fontStyleContent');
					$section->addTextBreak(1);
				}
			}else{
				$section->addTextBreak(1);
			}
			
			$a = $this->htmlTransformWord($row[5]);
			$section->addText('答案', 'fontStyleTitle');
			$section->addTextBreak(1);
			$section->addText($a, 'fontStyleContent');
			$section->addTextBreak(1);

			$e = $this->htmlTransformWord($row[4]);
			$section->addText('讲解', 'fontStyleTitle');
			$section->addTextBreak(1);
			$section->addText($e, 'fontStyleContent');

			$section->addTextBreak(2);
			$i++;
		}
		
		$phpWord->addFontStyle('fontStyleContent', array('size' => 20,'bold' => false));
        $phpWord->addFontStyle('fontStyleTitle', array('size' => 20,'bold' => true));

		$objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
		$filename = urlencode($this->fileName . '-' . date('YmdHi', time()));
		ob_end_clean();
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment;filename="' . $filename . '.docx"');
		header('Cache-Control: max-age=0');
		$this->SaveViaTempFile($objWriter);
		exit();
	}

	public function SaveViaTempFile($objWriter) 
	{
		$filePath =  IWeb::$app->getBasePath() . 'runtime/_phpexcle';
		if(!is_dir($filePath))
    	{
    		IFile::mkdir($filePath);
		}
		$filePath =  $filePath . '/' . rand(0, getrandmax()) . rand(0, getrandmax()) . '.tmp';

		$objWriter->save($filePath);
		readfile($filePath);
		unlink($filePath);
	}

	public function importDoc($excefile) 
	{
		$uploadfile = $excefile['qFile'];

		$file_md5 = IHash::md5_file($uploadfile);
		$filePath =  IWeb::$app->getBasePath() . 'runtime/_phpword';
		if(!is_dir($filePath))
    	{
    		IFile::mkdir($filePath);
		}
		$filePath =  $filePath . '/' . $file_md5 . '.html';
		if(!file_exists($filePath)){
			$phpHtmlWord = \PhpOffice\PhpWord\IOFactory::load($uploadfile);
			$objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpHtmlWord, 'HTML');
			$objWriter->save($filePath);
		}

		$content = file_get_contents($filePath);
		$content = $this->htmlTransform($content);

		$content_html_pattern = '/<body.*>(.*?)<\/body>/s';
        preg_match_all($content_html_pattern, $content, $html_matchs);
        
		$content_html = $html_matchs[0][0];
		$content_html = str_replace("!important", '',$content_html);

		$content_html = preg_replace_callback('/<body.*>/', function($matches){
			return '';
		  }, $content_html);
		  $content_html = preg_replace_callback('/<\/body>/', function($matches){
			return '';
		  }, $content_html);
        $content_html = preg_replace_callback('/width="\\d+"/', function($matches){
          return '';
        }, $content_html);
        $content_html = preg_replace_callback('/height="\\d+"/', function($matches){
          return '';
        }, $content_html);

		$content_html = str_replace('"',"'",$content_html);
		return $content_html;
	}

	public function htmlTransform($string)
	{
		  $string = str_replace('&quot;','"',$string);
		  $string = str_replace('&amp;','&',$string);
		  $string = str_replace('amp;','',$string);
		  $string = str_replace('&lt;','<',$string);
		  $string = str_replace('&gt;','>',$string);
		  $string = str_replace('&nbsp;',' ',$string);
		 // $string = str_replace("\\", '',$string);
		  return $string;
	}

	public function htmlTransformWord($string)
	{
		  $string = str_replace('&quot;','"',$string);
		  $string = str_replace('&amp;','&',$string);
		//  $string = str_replace('&','&amp;',$string);
		//  $string = str_replace('amp;','',$string);
		//  $string = str_replace('<','&lt;',$string);
		//  $string = str_replace('>','&gt;',$string);
		  $string = str_replace('&lt;','<',$string);
		  $string = str_replace('&gt;','>',$string);
		  $string = str_replace('&nbsp;',' ',$string);
	//	  $string = str_replace("\\", '',$string);
			$string = htmlspecialchars($string,ENT_NOQUOTES);
		  return $string;
	}
}