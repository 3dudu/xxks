<?php
/**
 */
class txtimport2 extends exambase
{
    public function tempConf(){
        return array(
            "ctype"=>'text/txt',
            "file"=>'txtimport2.txt',
            "filename"=>'txtimport2.txt',
            "name"=>'试题模板2-txt',
            "ext"=>'txt',
        );
    }

    public function import($excefile){

		$uploadfile = $excefile['qFile'];

		$fhandle   = fopen($uploadfile,'r');
		$firstLine = fgets($fhandle);
		rewind($fhandle);
	
		//跨过BOM头信息
		$charset[1] = substr($firstLine,0,1);
		$charset[2] = substr($firstLine,1,1);
		$charset[3] = substr($firstLine,2,1);
		if(ord($charset[1]) == 239 && ord($charset[2]) == 187 && ord($charset[3]) == 191)
		{
			fseek($fhandle,3);
		}
		$q_type_str = array(
			1=>"判断",
			2=>"单选",
			3=>"多选",
			4=>"填空",
		);
		//计算安装进度
		$totalSize  = filesize($uploadfile);
		$c_type = 0;
		$allQ = array();
		$q = array();
		$item = array();
		$allQ[] = array('题目','题型','难度','配图','讲解','答案');
		while(!feof($fhandle))
		{
			$lstr = fgets($fhandle);     //获取指针所在的一行数据
			$lstr = trim($lstr);
			if(isset($lstr[0])){
            preg_match('/^\\d+[\.\:](.*?)/', $lstr, $matches, PREG_OFFSET_CAPTURE);
            if($c_type==0 && $matches && !empty($matches) && isset($matches[0])){
                $nums = $matches[0][0];
                //判断
                $q = array();
                $item = array();
                $lstr = str_replace($nums,'',$lstr);
                $lstr = trim($lstr);
                $q[] = $lstr;
                $q[] = '';
                $q[] = 1;
                $q[] = '';
                $q[] = '';
			}elseif($c_type==0 && strpos($lstr,'答案')!==false){
				$lstr = str_replace('答案:','',$lstr);
				$lstr = str_replace('答案：','',$lstr);
				$lstr = str_replace('答案','',$lstr);
				$lstr = str_replace('，','',$lstr);
                $lstr = str_replace(',','',$lstr);
                $lstr = str_replace(' ','',$lstr);
				$lstr = trim($lstr);
                //根据答案判断题型
                preg_match('/[ABCDEF]/', $lstr, $matches, PREG_OFFSET_CAPTURE);
                if($matches && !empty($matches) && isset($matches[0])){
                    if(strlen($lstr)==1){
                        $c_type=2;
                    }else{
                        $c_type=3;
                    }
                }elseif($lstr=='正确'||$lstr=='错误'||$lstr=='对'||$lstr=='错'){
                    $lstr = $lstr=='对' ?'正确':'0';
                    $c_type=1;
                }else{
                    $c_type=4;
                }
                $q[1] = $q_type_str[$c_type];
				$q[] = $lstr;
				$q = array_merge($q,$item);
				$allQ[] = $q;
				$q = array();
				$item = array();
				$c_type = 0;
			}else{
                $lstr = trim($lstr);
                preg_match('/^[ABCDEF]{1}(.*?)/', $lstr, $matches, PREG_OFFSET_CAPTURE);
                if($matches && !empty($matches) && isset($matches[0])){
					$lstr = str_replace($matches[0][0],'',$lstr);
					$lstr = trim($lstr);
					$lstr = trim($lstr,'.');
					$lstr = trim($lstr,':');
					$lstr = trim($lstr,',');
					$lstr = trim($lstr,'：');
					$lstr = trim($lstr,'，');
					$item[] = $lstr;
				}elseif(isset($q[0])){
                    $q[0] .= PHP_EOL . $lstr;
                }
            }
        }
    }
		return $allQ;
    }
}