<?php
/**
 */
class txtimport1 extends exambase
{
    public function tempConf(){
        return array(
            "ctype"=>'text/txt',
            "file"=>'txtimport1.txt',
            "filename"=>'txtimport1.txt',
            "name"=>'试题模板1-txt',
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
		$q_type = array(
			"判断"=>1,
			"判断题"=>1,
			"判断題"=>1,
			"单选"=>2,
			"单选题"=>2,
			"単迭題"=>2,
			"多选"=>3,
			"多选题"=>3,
			"填空"=>4,
			"填空题"=>4,
		);
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
			if(strpos($lstr,'[')!==false ||strpos($lstr,']')!==false ){
				preg_match('/\[?(.*?)\]/', $lstr, $matches, PREG_OFFSET_CAPTURE);
				if($c_type==0 && $matches && !empty($matches) && isset($matches[0])){
					$types = $matches[1][0];
					//判断
					$c_type = $q_type[$types];
					$q = array();
					$item = array();
					$lstr = str_replace($types,'',$lstr);
					$lstr = str_replace('[','',$lstr);
					$lstr = str_replace(']','',$lstr);
					$lstr = trim($lstr);
					$lstr = preg_replace('/\(\\d+.*\)/','',$lstr);
					$q[] = $lstr;
					$q[] = $q_type_str[$c_type];
					$q[] = 1;
					$q[] = '';
					$q[] = '';
				}
			}elseif($c_type!=0 && strpos($lstr,'正确答案')!==false){
				$lstr = str_replace('正确答案:','',$lstr);
				$lstr = str_replace('正确答案：','',$lstr);
				$lstr = trim($lstr);
				if($c_type==1){
					$lstr = $lstr=='A' ?'正确':'0';
				}elseif($c_type==2||$c_type==3){
					$lstr = preg_replace('/[^ABCDEF]/','',$lstr);
				}elseif($c_type==4){
					$lstr = str_replace(' ','',$lstr);
				}else{
					$lstr='';
				}

				$q[] = $lstr;
				$q = array_merge($q,$item);
				$allQ[] = $q;
				$q = array();
				$item = array();
				$c_type = 0;
			}else{
				if($c_type==2||$c_type==3){
					$lstr = str_replace('A','',$lstr);
					$lstr = str_replace('B','',$lstr);
					$lstr = str_replace('C','',$lstr);
					$lstr = str_replace('D','',$lstr);
					$lstr = str_replace('E','',$lstr);
					$lstr = str_replace('F','',$lstr);
					$lstr = trim($lstr);
					$lstr = trim($lstr,':');
					$lstr = trim($lstr,'：');
					$lstr = trim($lstr,'.');
					$lstr = trim($lstr,',');
					$item[] = $lstr;
				}
			}
			}
		}
		return $allQ;
    }
}