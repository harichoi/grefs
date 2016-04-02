<?
/*
OOOO.gfs   //superblock
OOOO.gfs0  //blockgroup n
OOOO.gfs1
OOOO.gfs2

nblock{
 block info-1block
 inode bitmap-1block
 inode table-128block(4096 inode)  inode pt-1block
 block bitmap-1block
 blocks-32768blocks
}


fileheader{
 uid -  0~1(2b) 
 gid -  2~3(2b) 
 access - 4~5 (2b) 
 size - 6~11 (6b) 
 nblocks - 12~15 (4b) 
 type - 16 (1b) 
 password - 17~31 (15b) 
 flag - 32 (1b) 
 upnode - 33~36 (4b)
 extents - 37~96(12*5b)

}

extents{
extentheader 12b
extent_4 48b
}

extentindex{
extentheader 12b
extent_340 4084b
}

extentheader{
nextents 2b
maxnextents 2b
depth 1b
}

extent{
logicalblock 4b
nblocks 2b
block 4b
}

superblock{
 maxblock - 0-3 (4b)
 maxinode - 4-7 (4b)
 nblockgroup - 8-11 (4b)
 freeblock - 12-15 (4b)
 fblast - 16-19 (4b)
 filast - 20-23 (4b)
}

makefile()시 addfile+inodereset
*/

function passsolve($block,$shakey){
	$len=strlen($block);
	if(!$shakey) return $block;
	$before=$block;
	$block[0]=chr(ord($block[0])-ord($shakey[0]));
	for($i=1 ; $i<@strlen($block) ; $i++){
	$block[$i]=chr(ord($block[$i])-ord($before[$i-1])-ord($shakey[$i%strlen($shakey)]));
	}
	$block=pack("a$len",$block);
	return $block;
}
////////////////////////////////////////////////////////////////////////////
function makepass($block,$shakey){
	if(!$shakey) return $block;
	$len=strlen($block);
	$block[0]=chr(ord($block[0])+ord($shakey[0]));
	for($i=1 ; $i<strlen($block) ; $i++){
	$block[$i]=chr(ord($block[$i])+ord($block[$i-1])+ord($shakey[$i%strlen($shakey)]));
	}
	$block=pack("a$len",$block);
	return $block;
}

////////////////////////////////////////////////////////////////////////////
function passblock($block,$shakey){
	$len=strlen($block);
	$ret="";
	for($i=0 ; $i<4096 ; $i+=512){
	$ret.=makepass(substr($block,$i,512),$shakey);
	}
	return substr($ret,0,$len);
	}
	function solveblock($block,$shakey){
	$len=strlen($block);
	$ret="";
	for($i=0 ; $i<4096 ; $i+=512){
	$ret.=passsolve(substr($block,$i,512),$shakey);
	}
	return substr($ret,0,$len);
}
////////////////////////////////////////////////////////////////////////////
function toint($num){
	return floor($num);
}
function naerim($num){
	return floor($num);
}
function inttochar($num,$len){
	$ary=array();
	unset($result);
	for($i=0 ; $i<$len ; $i++){
		$ary[$i]=$num%256;
		$num=toint($num/256);
	}
	for($i=$len-1 ; $i>=0 ; $i--){
		$result.=chr($ary[$i]);
	}
	return $result;
}
function chartoint($num,$len){
	unset($result);
	$result=0;
	for($i=0 ; $i<$len ; $i++){
		$result*=256;
		$result+=ord($num[$i]);
	}
	return $result;
}

class GREFS{
	var $maxnblockgroup=0;
	var $maxnblock=0;
	var $maxninode=0;
	var $freebit=array();
	var $fbfront=0;
	var $fbrear=0;
	var $fblast=0;
	var $filast=0;
	var $uid=1000;
	var $gid=1000;
	var $fp;
	var $fps=array();
	var $md5key="";
	var $sha1key="";
	var $passmode=0;
	var $blobit;//block bit
	var $swblo=0;//switch of block group
	var $srcfname;
	var $forceperm=0;
	var $whatnum=-2;
	function GREFS($fname,$owner,$group,$md5key,$sha1key){
		$this->srcfname=$fname;
		$this->md5key=substr($md5key,0,15);
		$this->sha1key=$sha1key;
		$this->uid=$owner;
		$this->gid=$group;
		if(!file_exists($fname)){
			$this->fp=fopen($fname,"w");
			fclose($this->fp);
		}
		$this->fps[0]=fopen($fname,"r+");
		$this->fp=$this->fps[0];
		$header=fread($this->fp,24);
		$this->maxnblock=chartoint(substr($header,0,4),4);
		$this->maxninode=chartoint(substr($header,4,4),4);
		$this->maxnblockgroup=chartoint(substr($header,8,4),4);
		$this->fblast=chartoint(substr($header,16,4),4);
		$this->filast=chartoint(substr($header,20,4),4);
	}
	function close(){
		$this->fsik(-1,16);
		fwrite($this->fp,inttochar($this->fblast,4),4);
		$this->fsik(-1,20);
		fwrite($this->fp,inttochar($this->filast,4),4);
	
		fclose($this->fps[0]);
		for($i=0 ; $i<$this->maxnblockgroup ; $i++){
			if($this->fps[$i+1])
				fclose($this->fps[$i+1]);
		}
	}
	function fsik($nbg,$offset){//nblockgroup, -1 : superblock
		fflush($this->fp);
		if($this->whatnum==$nbg)
			fseek($this->fp,$offset,SEEK_SET);
		else{
			$this->whatnum=$nbg;
			if(!$this->fps[$nbg+1]){
				if(!file_exists($this->srcfname.$nbg)){
					touch($this->srcfname.$nbg);
				}
				$this->fps[$nbg+1]=fopen($this->srcfname.$nbg,"r+");
			}
			$this->fp=$this->fps[$nbg+1];
			fseek($this->fp,$offset,SEEK_SET);
		}
	}

	function getinodeinfo($num){
		$arre=array();
		$headers=$this->getinode($num);

		$arre['uid']=chartoint(substr($headers,0,2),2);
		$arre['gid']=chartoint(substr($headers,2,2),2);
		$arre['access']=chartoint(substr($headers,4,2),2);
		$arre['size']=chartoint(substr($headers,6,6),6);
		$arre['nblocks']=chartoint(substr($headers,12,4),4);
		$arre['type']=chartoint(substr($headers,16,1),1);
		$arre['password']=substr($headers,17,15);
		$arre['flag']=chartoint(substr($headers,32,1),1);
		$arre['upnode']=chartoint(substr($headers,33,4),4);
		$arre['extents']=substr($headers,37,60);

		return $arre;
	}

	function setinodeinfo($num,$arr){


		$headers=pack("a2a2a2a6a4a1a15a1a4a60",inttochar($arr['uid'],2),inttochar($arr['gid'],2),inttochar($arr['access'],2),inttochar($arr['size'],6),inttochar($arr['nblocks'],4),inttochar($arr['type'],1),$arr['password'],inttochar($arr['flag'],1),inttochar($arr['upnode'],4),$arr['extents']);

		$this->setinode($num,$headers);
		return 1;
	}


//////////////////////////////////////////////////////
	function exist_inode($inode,$plusnode)
	{
		$info=$this->getinodeinfo($inode);
		if($this->getperm($inode,"r")==0) return -1;
		$check=0;
		$files=$this->getfiles($inode);
		if($files==-1) return -1;
	
		for($j=0 ; $j<$files['len'] ; $j++){
			if($files[$j][0]==$plusnode){
			return 1;
		}
	}
	return 0;
}


//////////////////////////////////////////////////////
function addfile($inode,$plusnode,$filename)
{
	$info=$this->getinodeinfo($inode);

	if($this->getperm($inode,"w")==0) return -1;

	if(trim($filename)=="") return -1;

	$filename=str_replace("\\","",$filename);
	$filename=str_replace("*","",$filename);
	$filename=str_replace("?","",$filename);
	$filename=str_replace("/","",$filename);
	$check=0;

	$files="";
	$data=$this->getcontent($inode);
	for($i=0 ; $i<strlen($data) ; $i+=128){
		$files.=pack("a128",substr($data,$i,128));
	}

	$files.=pack("a4a124",inttochar($plusnode,4),substr($filename,0,124));

	$this->replacedata($inode,$files);
	return 1;
}
//////////////////////////////////////////////////////
function move($path1,$path2){
	$inodeup1=$this->pathtoinode($this->getrootdir(),$path1,1);
	$inodeup2=$this->pathtoinode($this->getrootdir(),$path2,0);

	if($this->getperm($inodeup1,"r")==0) return -1;
	if($this->getperm($inodeup1,"w")==0) return -1;
	if($this->getperm($inodeup2,"r")==0) return -1;
	if($this->getperm($inodeup2,"w")==0) return -1;

	$uu=explode($path1,$path2);
	if(count($uu)>1) return -1;
	$inode1=$this->getpath($path1);
	$tt=explode("/",$path1);
	$fname1=$tt[count($tt)-1];
	//$tt=explode("/",$path2);
	$fname2=$tt[count($tt)-1];

	if($inodeup1<0 || $inodeup2<0 || $inode1<0 || $inode2<0)
		return -1;
	$this->delonlyfile($inodeup1,$fname1);
	$this->addfile($inodeup2,$inode1,$fname2);

	$info=$this->getinodeinfo($inode1);
	$info['upnode']=$inodeup2;
	$this->setinodeinfo($inode1,$info);
}

/////////////////////////////////////////////////////////
function delonlyfile($inode,$filename)
{
	if($this->getperm($inode,"r")==0) return -1;
	if($this->getperm($inode,"w")==0) return -1;

	$info=$this->getinodeinfo($inode);
	if(trim($info['password'])!="" && trim($info['password'])!=substr($this->md5key,0,15)) return -1;

	$files="";
	$data=$this->getcontent($inode);

	if($info['type']!=5) return -1;

	for($i=0 ; $i<strlen($data) ; $i+=128){
		$ind=substr($data,$i,4);
		$fname=trim(substr($data,$i+4,124));
		if($fname==$filename){
		}
		else{
			$files.=substr($data,$i,128);
		}
	}
	$this->replacedata($inode,$files);
}


//////////////////////////////////////////////////////
function delpath($ide,$path)
{
	$ex=explode("/",$path);
	$inde=$this->pathtoinode($ide,$path,1);

	if($this->getperm($inde,"r")==0) return -1;
	if($this->getperm($inde,"w")==0) return -1;

	if($inde==-1)return -1;
	$delname=$ex[count($ex)-1];
	echo "[[".$ex[0]."/".$ex[1]."/".$ex[2]."/".$ex[3]."/".$ex[4]."]]<br>";
	$this->delfile($inde,$delname);

	return 1;
}
/////////////////////////////////////////////////////////
function delfile($path)
{
	$inode=$this->getpath($path);
	$upnode=$this->pathtoinode($this->getrootdir(),$path,1);
	if($inode<0 || $upnode<0) return -1;
	if($this->getperm($inode,"r")==0) return -1;
	if($this->getperm($inode,"w")==0) return -1;
	if($this->getperm($upnode,"r")==0) return -1;
	if($this->getperm($upnode,"w")==0) return -1;

	echo "DELFILE : ".$path."<br>";
	flush();

	$info=$this->getinodeinfo($inode);

	$files="";

	if($info['type']==5){
		$data=$this->getcontent($inode);
		for($i=0 ; $i<strlen($data) ; $i+=128){
			$ind=chartoint(substr($data,$i,4),4);
			$fname=trim(substr($data,$i+4,124));
			if($fname!=""){
				$ret=$this->delfile($path."/".$fname);
				if($ret==-1) return -1;
			}
		}
	}
	$data=$this->getcontent($upnode);
	for($i=0 ; $i<strlen($data) ; $i+=128){
		$ind=chartoint(substr($data,$i,4),4);
		$fname=trim(substr($data,$i+4,124));
		if($ind==$inode && $fname!=""){
			$ret=$this->delinode($ind);
			if($ret==-1) return -1;
		}
		else{
			$files.=substr($data,$i,128);
		}
	}
	$this->replacedata($upnode,$files);
	return 1;
}
//////////////////////////////////////////////////////
function getpath($path){
	return $this->pathtoinode($this->getrootdir(),$path,0);
}
//////////////////////////////////////////////////////
function pathtoinode($inode,$path,$up)
{
	$pa=explode("/",$path);
	for($i=0 ; $i<count($pa)-$up ; $i++){
		if(trim($pa[$i])=="")continue;
		$files=$this->getfiles($inode);
		$flag=0;
		for($j=0 ; $j<$files['len'] ; $j++){
			if(trim($files[$j][1])==trim($pa[$i])){
				$inode=$files[$j][0];
				$flag=1;
				break;
			}
		}
		if($flag==0){
			 return -1;
		}
	}
	return $inode;
}
//////////////////////////////////////////////////////
function makepathdir($path,$up)
{
	$uppath="";
	$info=$this->getinodeinfo($inode);

	$pa=explode("/",$path);
	for($i=0 ; $i<count($pa)-$up ; $i++){
		if(trim($pa[$i])=="")continue;
		$files=$this->getfiles($inode);
		$uppath.="/".$pa[$i];
		if($this->getpath($uppath)<0){
			//makedir
			echo "inode ".$inode." ".$inde."<br>";
			$inde=$this->makefile($uppath,5,"");
	
			$inode=$inde;
		}
	}
	return $inode;
}
//////////////////////////////////////////////////////
function chname($inode,$filename1,$filename2){

	if($this->getperm($inode,"r")==0) return -1;
	if($this->getperm($inode,"w")==0) return -1;

	$info=$this->getinodeinfo($inode);

	$files="";
	$data=$this->getcontent($inode);

	if($info['type']!=5) return -1;

	for($i=0 ; $i<strlen($data) ; $i+=128){
		$ind=substr($data,$i,4);
		$fname=trim(substr($data,$i+4,124));
		if($fname==$filename1){
			$files.=$ind;
			$files.=pack("a124",$filename2);
		}
		else{
			$files.=substr($data,$i,128);
		}
	}
	$this->replacedata($inode,$files);

}
//////////////////////////////////////////////////////
function getfiles($inode)
{
	$info=$this->getinodeinfo($inode);
	if($info['type']!=5) return -1;
	if($this->getperm($inode,"r")==0) return -1;
	
	$files=array();
	$num=0;
	$data=$this->getcontent($inode);
	for($i=0 ; $i<strlen($data) ; $i+=128){
		if(strlen(trim(substr($data,$i+4,124)))>0){
			$files[$num][0]=chartoint(substr($data,$i,4),4);
			$files[$num][1]=trim(substr($data,$i+4,124));
			$inf=$this->getinodeinfo($files[$num][0]);
			$files[$num][2]=$inf['type'];
			$files[$num][3]=$inf['size'];
			$files[$num][4]=$inf['uid']."/".$inf['gid'];
			$accessmode="";
			if(($inf['access']&1)>0){$accessmode.="r";} else {$accessmode.="-";}
			if(($inf['access']&2)>0){$accessmode.="w";} else {$accessmode.="-";}
			if(($inf['access']&4)>0)$accessmode.="x"; else $accessmode.="-";
			if(($inf['access']&8)>0)$accessmode.="r"; else $accessmode.="-";
			if(($inf['access']&16)>0)$accessmode.="w"; else $accessmode.="-";
			if(($inf['access']&32)>0)$accessmode.="x"; else $accessmode.="-";
			if(($inf['access']&64)>0)$accessmode.="r"; else $accessmode.="-";
			if(($inf['access']&128)>0)$accessmode.="w"; else $accessmode.="-";
			if(($inf['access']&256)>0)$accessmode.="x"; else $accessmode.="-";
			
			$files[$num][5]=$accessmode;
			if(trim($inf['password'])=="")
				$files[$num][6]=0;
			else if(trim($inf['password'])==trim(substr($this->md5key,0,15)))
				$files[$num][6]=2;
			else
				$files[$num][6]=1;
			
			$num++;
		}
	}
	$files['len']=$num;
	return $files;
}
//////////////////////////////////////////////////////
function getfreeinode(){
	$a=array();
	$a[0]=128;
	for($i=1 ; $i<8 ; $i++)
		$a[$i]=$a[$i-1]/2;
	$flag=8;
	$fflag=0;
	for($num=$this->filast ; ; $num+=8){
		if($num>=$this->maxninode-8){
			$num=0;
			$fflag++;
		}
		$bsize=1024*4;
		$this->fsik(floor($num/4096),$bsize+floor(($num%4096)/8));
		$temp=fread($this->fp,1);
		if($flag==1 || $fflag>=2)break;
		if($flag==8){
			if(ord($temp)==255)continue;
			else{
				for($j=0 ; $j<8 ; $j++){
					if((ord($temp)&$a[$j])==0 && $num+$j>=1){
					$this->setinodebit($num+$j);
					$this->filast=$num;
					return $num+$j;
					}
				}
			}
		}
	}
	return -1;
}
//////////////////////////////////////////////////////
function getfreeextent($nfree){
	if($this->fbrear<=$this->fbfront){
		$this->fbrear=0;$this->fbfront=0;
		unset($this->freebit);
		$this->freebit=array();
		/////////////
		$a=array();
		$a[0]=128;
		for($i=1 ; $i<8 ; $i++)
		$a[$i]=$a[$i-1]/2;
		$flag=8;
		$fflag=0;
		for($num=$this->fblast ; ; $num+=8){
			if($num>=$this->maxnblock-8){
				$num=0;
				$fflag++;
			}
			$bsize=1024*4;
			$this->fsik(floor($num/32768),$bsize*130+floor(($num%32768)/8));
			$temp=fread($this->fp,1);
			if($flag==1 || $fflag>=2)break;
			if($flag==8){
				if(ord($temp)==255)continue;
				else{
					for($j=0 ; $j<8 ; $j++){
						if((ord($temp)&$a[$j])==0){
							//return $num+$j;
							$this->freebit[$this->fbrear]=$num+$j;
							$this->fbrear++;
							$this->fblast=$num;
							if(true){//$this->fbrear>=1){
								$flag=1;
								 break;
							}
						}
					}
				}
			}
		}
	}
	while(true){
		if($this->fbrear<=$this->fbfront) return -1;
		$this->fbfront++;
		if($this->getblockbit($this->freebit[$this->fbfront-1])==0){
			$len=$this->getextentbits($this->freebit[$this->fbfront-1],$nfree);
			$this->setblockbit($this->freebit[$this->fbfront-1],$len);
			$arr=array();
			$arr[0]=$this->freebit[$this->fbfront-1];
			$arr[1]=$len;
			return $arr;
		}
	}
	$_SESSION[error].="from getfreeblock, no freeblock"."/";
	return -1;
}
//////////////////////////////////////////////////////
function replacedata($inode,$data){
	
	if($this->getperm($inode,"r")==0) return -1;
	if($this->getperm($inode,"w")==0) return -1;
	$pt=0;
	$this->trun($inode,strlen($data));
	while(true){
		$info=$this->getinodeinfo($inode);
		if($info['size']<strlen($data)){
			$len=$this->addsize($inode,strlen($data)-$info['size']);
			if($len<=0) return -1;
		}
		else break;
	}
	while($pt<strlen($data)){
		$len=$this->fileseek($inode,$pt);
		if($len<=0) return -1;
		$dt=substr($data,$pt,$len);
		if(trim($info['password'])!="")
		$dt=passblock($dt,$this->sha1key);
		fwrite($this->fp,$dt,$len);
		$pt+=$len;
	}
}
//////////////////////////////////////////////////////
function trun($inode,$pt){
	
	if($this->getperm($inode,"r")==0) return -1;
	if($this->getperm($inode,"w")==0) return -1;
	$info=$this->getinodeinfo($inode);
	$extents=$info['extents'];
	$ret=$this->trunrecur($extents,$pt,-1);
	if(strlen($ret)>4){
		$info['extents']=$ret;
	}
	if($info['size']>$pt)
		$info['size']=$pt;
	$this->setinodeinfo($inode,$info);
}
function trunrecur($extents,$pt,$blockaddr){
	$nextents=chartoint(substr($extents,0,2),2);
	$maxextents=chartoint(substr($extents,2,2),2);
	$depth=chartoint(substr($extents,4,1),1);
	$flag=0;
	for($i=$nextents-1 ; $i>=0 ; $i--){
		$logi=chartoint(substr($extents,$i*12+12,4),4);
		$addr=chartoint(substr($extents,$i*12+18,4),4);
		$nb=chartoint(substr($extents,$i*12+16,2),2);
		
		if($depth!=0)
			$this->trunrecur($this->getblock($addr,0,4096),$pt,$addr);
		if($logi*4096>=$pt){
			$flag=1;
			//delete
			if($depth==0){
				$this->delblockbit($addr,$nb);
				$nextents=$i;
			}
			else{
				$nextents=$i;
				$this->delblockbit($addr,1); 
			}
		}
	}
	
	if($blockaddr!=-1){
		$this->setblock($blockaddr,inttochar($nextents,2),0,2);
		return 1;
	} else {
		$extents=substr_replace($extents,inttochar($nextents,2),0,2);
		return $extents;
	}
}
//////////////////////////////////////////////////////
function fileseek($inode,$pt){

	$info=$this->getinodeinfo($inode);
	$extents=$info['extents'];
	
	while(true){
		$nextents=chartoint(substr($extents,0,2),2);
		$maxextents=chartoint(substr($extents,2,2),2);
		$depth=chartoint(substr($extents,4,1),1);
		$sel=$nextents-1;
		for($i=0 ; $i<$nextents ; $i++){
			$logi=chartoint(substr($extents,$i*12+12,4),4);
			if($logi*4096>$pt){ $sel=$i-1; break;}
		}
		$logi=chartoint(substr($extents,$sel*12+12,4),4);
		$addr=chartoint(substr($extents,$sel*12+18,4),4);
		$nb=chartoint(substr($extents,$sel*12+16,2),2);
		
		if($depth==0){
			$this->fsik(floor($addr/32768),4096*($addr%32768+131)+$pt-$logi*4096);
			$aa=$logi*4096+$nb*4096-$pt;
			$bb=$info['size']-$pt;
			if($aa>$bb)
				$aa=$bb;
			return $aa;
		}
		$extents=$this->getblock($addr,0,4096);
	}
}
//////////////////////////////////////////////////////
function extentadd($extents,$extent,$blockaddr){
	//ret -2 : up-add / 1: success
	$nextents=chartoint(substr($extents,0,2),2);
	$maxextents=chartoint(substr($extents,2,2),2);
	$depth=chartoint(substr($extents,4,1),1);
	$logicalchar=substr($extent,0,4);
	
	if($depth==0){
	/////////////depth0/////////
		if($maxextents<=$nextents){
			//adding
			if($blockaddr==-1){
				$arr=$this->getfreeextent(1);
				$addr=$arr[0];
				$extents=substr_replace($extents,inttochar(340,2),2,2);
				$this->setblock($addr,$extents,0,4096);
				$this->extentadd($extents,$extent,$addr);
				$extents=pack("a2a2a1a7a12a36",inttochar(1,2),inttochar(4,2),inttochar($depth+1,1),"",pack("a4a2a4",chr(0),"",inttochar($addr,4)),"");
				return $extents;
			} else { 
				return -2;
			}
		} else {
			$nextents++;
			$extents=substr_replace($extents,inttochar($nextents,2),0,2);
			$extents=substr_replace($extents,$extent,$nextents*12,12);
			if($blockaddr==-1) return $extents;
			$this->setblock($blockaddr,$extents,0,4096);
			return 1;
		}
////////////////////////////
	}
	else{
	/////////////depth-else/////////
		$addr=chartoint(substr($extents,$nextents*12+6,4),4);
		$ret=$this->extentadd($this->getblock($addr,0,4096),$extent,$addr);
		if($ret==-2){
			///////////
			if($maxextents<=$nextents){
				if($blockaddr==-1){
					$arr=$this->getfreeextent(1);
					$addr=$arr[0];
					$extents=substr_replace($extents,inttochar(340,2),2,2);
					$this->setblock($addr,$extents,0,4096);
					$this->extentadd($extents,$extent,$addr);
					$extents=pack("a2a2a1a7a12a36",inttochar(1,2),inttochar(4,2),inttochar($depth+1,1),"",pack("a4a2a4","","",inttochar($addr,4)),"");
					return $extents;
				}
				return -2;
			} else{
				$arr=$this->getfreeextent(1);
				$addr=$arr[0];
				
				$nextents++;
				$extentindex=pack("a4a2a4",$logicalchar,"",inttochar($addr,4));
				$extents=substr_replace($extents,inttochar($nextents,2),0,2);
				$extents=substr_replace($extents,$extentindex,$nextents*12,12);
				$nexextents=pack("a2a2a1a7a48",chr(0),inttochar(340,2),inttochar($depth-1,1),"","");
				$this->setblock($addr,$nexextents,0,4096);
				$this->extentadd($nexextents,$extent,$addr);
				
				if($blockaddr==-1) return $extents;
				else {
					$this->setblock($blockaddr,$extents,0,4096);
					$ret=$this->extentadd($nexextents,$extent,$addr);
				}
			}
		///////////
		}
		else return 1;
		////////////////////////////
	}
}
//////////////////////////////////////////////////////
function addsize($inode,$size){

	if($this->getperm($inode,"r")==0) return -1;
	if($this->getperm($inode,"w")==0) return -1;
	if($size>$this->freespace()) return -1;
	$info=$this->getinodeinfo($inode);
	
	$basicsize=$info['size'];
	
	$plus=ceil(($info['size']+$size)/4096)-ceil($info['size']/4096);
	if($plus>0){
		$ext=$this->getfreeextent($plus);
		
		$extent=pack("a4a2a4",inttochar(ceil($info['size']/4096),4),inttochar($ext[1],2),inttochar($ext[0],4));
		$ret=$this->extentadd($info['extents'],$extent,-1);
		if($info['size']+$size<ceil($info['size']/4096)*4096+$ext[1]*4096)
			$info['size']+=$size;
		else
			$info['size']=ceil($info['size']/4096)*4096+$ext[1]*4096;
		if(strlen($ret)>5)
			$info['extents']=$ret;
	}
	else{
		$info['size']+=$size;
	}
	$this->setinodeinfo($inode,$info);
	$this->fsik(floor($ext[0]/32768),4096*($ext[1]%32768)+131*4096);
	$plussize=$info['size']-$basicsize;
	return $plussize; //len
}

//////////////////////////////////////////////////////
function getperm($inode,$type){
	if($this->forceperm==1) return 1;
	
	$info=$this->getinodeinfo($inode);
	$perm=0;
	
	
	if(trim($info['password'])!="" && trim(substr($info['password'],0,15))!=trim(substr($this->md5key,0,15)))
		return 0;
	if($info['uid']==$this->uid){
		if($type=="r"){
			if(($info['access']&1)>0) $perm=1;
		}
		if($type=="w"){
			if(($info['access']&2)>0) $perm=1;
		}
		if($type=="x"){
			if(($info['access']&4)>0) $perm=1;
		}
	}
	else if($info['gid']==$this->gid){
		if($type=="r"){
			if(($info['access']&8)>0) $perm=1;
		}
		if($type=="w"){
			if(($info['access']&16)>0) $perm=1;
		}
		if($type=="x"){
			if(($info['access']&32)>0) $perm=1;
		}
	}
	else{
		if($type=="r"){
			if(($info['access']&64)>0) $perm=1;
		}
		if($type=="w"){
			if(($info['access']&128)>0) $perm=1;
		}
		if($type=="x"){
			if(($info['access']&256)>0) $perm=1;
		}
	}
	return $perm;
}
//////////////////////////////////////////////////////
function getcontent($inode)
{
	if($this->getperm($inode,"r")==0) return -1;
	$info=$this->getinodeinfo($inode);
	$pt=0;
	$echodata="";
	while($pt<$info['size']){
		$len=$this->fileseek($inode,$pt);
		
		if($len==0){
			break;
		}
		$remain=$len;
		for($i=0 ; $i<ceil($len/4096) ; $i++){
			$uu=4096;
			if($remain>4096) $remain-=4096;
			else{
				$uu=$remain;
				$remain=0;
			}
			$data=fread($this->fp,$uu);
			if(trim($info['password'])!=""){
				$data=solveblock($data,$this->sha1key);
			}
			$echodata.=$data;
		}
		$pt+=$len;
	}
	return $echodata;
}

//////////////////////////////////////////////////////
function makefile($path,$type,$password){
	$fname="none";
	$uppath="/";
	if($path=="/") $upnode=0;
	else{
		$this->makepathdir($path,1);
		$upnode=$this->pathtoinode($this->getrootdir(),$path,1);
		if($upnode<0) return -1;
		if($this->getperm($upnode,"r")==0) return -1;
		if($this->getperm($upnode,"w")==0) return -1;
		$info=$this->getinodeinfo($upnode);
		$temp=explode("/",$path);
		$fname=$temp[count($temp)-1];
		for($i=0 ; $i<count($temp)-1 ; $i++)
			$uppath.=$temp[$i]."/";
	}
	unset($inode);
	unset($nblocks);
	unset($inodedata);
	
	$inode=$this->getfreeinode();
	///
	if($inode==-1) return -1;
	$exheader=pack("a2a2a1",chr(0),inttochar(4,2),chr(0));
	$inodedata=pack("a2a2a2a6a4a1a15a1a4a60",inttochar($this->uid,2),inttochar($this->gid,2),inttochar(367,2),chr(0),chr(0),inttochar($type,1),$password,chr("c"),inttochar($upnode,4),$exheader);
	
	///////
	$this->setinode($inode,$inodedata);
	
	if($upnode!=0){
		for($i=0 ; ; $i++){
			$rr=$this->getpath($uppath.$fname);
			if($rr>=1){
				$fname="copy-".$fname;
			}
			else break;
		}
		$ret=$this->addfile($upnode,$inode,$fname);
	}
	return $inode;
}
///////////////////////////////////////////////////////////////////////////
function setchmod($inode,$chmod){
	if($inode>=$this->maxninode || $inode<0){
		$_SESSION[error].="from setchmod, access inode : ".$inode."/";
		return -1;
	}
	$info=$this->getinodeinfo($inode);
	if($info['uid']!=$this->uid) return -1;
	$info['access']=$chmod;
	$this->setinodeinfo($inode,$info);
	return 1;
}
//////////////////////////////////////////////////////
function makeblock($block){
	return pack("a4096",$block);
}

//////////////////////////////////////////////////////
function getinodebit($num){
	if($num>=$this->maxninode || $num<0){
		$_SESSION[error].="from getinodebit, access inode : ".$num."/";
	
		return -1;
	}
	$bsize=1024*4;
	$this->fsik(floor($num/4096),$bsize+floor(($num%4096)/8));
	$temp=fread($this->fp,1);
	$a=array();
	$a[0]=128;
	for($i=1 ; $i<8 ; $i++)
		$a[$i]=$a[$i-1]/2;
	if((ord($temp)&$a[$num%8])>0)
		return 1;
	return 0;
}
//////////////////////////////////////////////////////
function setinodebit($num){
	if($num>=$this->maxninode || $num<0){
		$_SESSION[error].="from setinodebit, access inode : ".$num."/";
		return -1;
	}
	$bsize=1024*4;
	$this->fsik(floor($num/4096),$bsize+floor(($num%4096)/8));
	$temp=ord(fread($this->fp,1));
	$a=array();
	$a[0]=128;
	for($i=1 ; $i<8 ; $i++)
		$a[$i]=$a[$i-1]/2;
	
	if(($temp&$a[$num%8])==0){
		$temp+=$a[$num%8];
	}
	
	$this->fsik(floor($num/4096),$bsize+floor(($num%4096)/8));
	fwrite($this->fp,chr($temp),1);
	fflush($this->fp);
}
//////////////////////////////////////////////////////
function setinode($num,$data){
	$bsize=1024*4;
	
	$this->fsik(floor($num/4096),$bsize*2+128*($num%4096));
	
	fwrite($this->fp,$data,128);
	fflush($this->fp);
}

//////////////////////////////////////////////////////
function getinode($num){
	if($this->getinodebit($num)==0){
		$_SESSION[error].="noinode, from getinode, access inode : ".$num."/";
		return "";
	}
	$bsize=1024*4;
	$this->fsik(floor($num/4096),$bsize*2+128*($num%4096));
	
	$inodedata=fread($this->fp,128);
	return $inodedata;
}
//////////////////////////////////////////////////////
function getextentbits($num,$wlen){
	
	$bsize=1024*4;
	$this->fsik(floor($num/32768),$bsize*130+floor(($num%32768)/8));
	$temp=fread($this->fp,ceil($wlen/8));
	$a=array();
	$a[0]=128;
	for($i=1 ; $i<8 ; $i++)
		$a[$i]=$a[$i-1]/2;
	$len=0;
	
	for($i=0 ; $i<$wlen ; $i++){
		if($num%32768+$i>=32768) break;
		if((ord($temp[floor($i/8)])&$a[($num+$i)%8])>0)
			break;
		$len++;
	}
	return $len;
}

//////////////////////////////////////////////////////
function getblockbit($num){
	
	$bsize=1024*4;
	$this->fsik(floor($num/32768),$bsize*130+floor(($num%32768)/8));
	$temp=fread($this->fp,1);
	$a=array();
	$a[0]=128;
	for($i=1 ; $i<8 ; $i++)
		$a[$i]=$a[$i-1]/2;
	if((ord($temp)&$a[$num%8])>0)
		return 1;
	return 0;
}
//////////////////////////////////////////////////////
function setblockbit($num,$len){
	if($num>=$this->maxnblock || $num<0) return -1;
	
	$bsize=1024*4;
	$this->fsik(floor($num/32768),$bsize*130+floor(($num%32768)/8));
	$temp=fread($this->fp,ceil($len/8));
	$a=array();
	$a[0]=128;
	for($i=1 ; $i<8 ; $i++)
		$a[$i]=$a[$i-1]/2;
	//////////
	for($j=0 ; $j<$len ; $j++){
		if((ord($temp[floor($j/8)])&$a[($num+$j)%8])==0){
			$temp[floor($j/8)]=chr(ord($temp[floor($j/8)])+$a[($num+$j)%8]);
		}
	}
	$this->setfreespace($this->freespace()-4096*$len);
	$this->fsik(floor($num/32768),$bsize*130+floor(($num%32768)/8));
	
	fwrite($this->fp,$temp,ceil($len/8));
	//////////
	fflush($this->fp);
}
//////////////////////////////////////////////////////
function delblockbit($num,$len){
	if($num>=$this->maxnblock || $num<0){
		$_SESSION[error].="from delblockbit, access inode : ".$num."/";
		return -1;
	}
	$bsize=1024*4;
	$this->fsik(floor($num/32768),$bsize*130+floor(($num%32768)/8));
	$temp=fread($this->fp,ceil($len/8));
	$a=array();
	$a[0]=128;
	for($i=1 ; $i<8 ; $i++)
		$a[$i]=$a[$i-1]/2;
	
	for($j=0 ; $j<$len ; $j++){
		if((ord($temp[floor($j/8)])&$a[($num+$j)%8])>0){
			$temp[floor($j/8)]=chr(ord($temp[floor($j/8)])-$a[($num+$j)%8]);
		}
	}
	$this->setfreespace($this->freespace()+4096*$len);
	$this->fsik(floor($num/32768),$bsize*130+floor(($num%32768)/8));
	fwrite($this->fp,$temp,ceil($len/8));
	fflush($this->fp);
	unset($temp);
}
////////////////////////////////////////////////////////////////////////////
function topassfile($inode){
	
	if($this->getperm($inode,"r")==0) return -1;
	if($this->getperm($inode,"w")==0) return -1;
	
	$info=$this->getinodeinfo($inode);
	
	if(trim($info['password'])!="") return -1;
	if($info['uid']!=$this->uid) return -1;
	
	$info['password']=substr($this->md5key,0,15);
	
	for($i=0 ; $i<ceil($info['size']/4096) ; $i++){
		$len=$this->fileseek($inode,$i*4096);
		if($len>4096) $len=4096;
		$data=passblock(fread($this->fp,$len),$this->sha1key);
		$len=$this->fileseek($inode,$i*4096);
		if($len>4096) $len=4096;
		fwrite($this->fp,$data,$len);
	}
	$this->setinodeinfo($inode,$info);
}
////////////////////////////////////////////////////////////////////////////
function topassfile_onlyheader($inode){
	$info=$this->getinodeinfo($inode);
	$info['password']=substr($this->md5key,0,15);
	$this->setinodeinfo($inode,$info);
}
//////////////////////////////////////////////////////
function tosolvefile($inode){
	
	if($this->getperm($inode,"r")==0) return -1;
	if($this->getperm($inode,"w")==0) return -1;
	$info=$this->getinodeinfo($inode);
	if(trim($info['password'])=="") return -1;
	if($info['uid']!=$this->uid) return -1;
	$info['password']="";
	for($i=0 ; $i<ceil($info['size']/4096) ; $i++){
		$len=$this->fileseek($inode,$i*4096);
		if($len>4096) $len=4096;
		$data=solveblock(fread($this->fp,$len),$this->sha1key);
		$len=$this->fileseek($inode,$i*4096);
		if($len>4096) $len=4096;
		fwrite($this->fp,$data,$len);
	}
	$this->setinodeinfo($inode,$info);
}

//////////////////////////////////////////////////////
function delinode($inode){
	if($inode<0 || $inode>$this->maxninode) return 1;
	if($this->getperm($inode,"r")==0) return -1;
	if($this->getperm($inode,"w")==0) return -1;
	
	$this->trun($inode,0);
	
	$bsize=1024*4;
	$this->fsik(floor($inode/4096),$bsize+floor(($inode%4096)/8));
	$temp=ord(fread($this->fp,1));
	$a=array();
	$a[0]=128;
	for($i=1 ; $i<8 ; $i++)
	$a[$i]=$a[$i-1]/2;
	
	if(($temp&$a[$inode%8])>=1) $temp-=$a[$inode%8];
	
	$this->fsik(floor($inode/4096),$bsize+floor(($inode%4096)/8));
	fwrite($this->fp,chr($temp),1);
	fflush($this->fp);
	return 1;
}

//////////////////////////////////////////////////////
function getblock($num,$offset,$len){
	if($this->getblockbit($num)==0) return "";
	$bsize=1024*4;
	$this->fsik(floor($num/32768),$bsize*131+($num%32768)*$bsize+$offset);
	$block=fread($this->fp,$len);
	
	return $block;
}
//////////////////////////////////////////////////////
function setblock($num,$data,$offset,$len){
	if($num<0 || $num>$this->maxnblock){
		$_SESSION[error].="from setblock, access block : ".$num."/";
		return -1;
	}
	$bsize=1024*4;
	
	
	$this->fsik(floor($num/32768),$bsize*131+($num%32768)*$bsize+$offset);
	fwrite($this->fp,$data,$len);
	fflush($this->fp);
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function getrootdir(){
return 1;
}

//////////////////////////////////////////////////////
function htmlcontent($inode)
{
	$data=$this->getcontent($inode);
	//$data=str_replace("&","&amp;",$data);
	//$data=str_replace("<","&lt;",$data);
	//$data=str_replace(">","&gt;",$data);
	return ($data);
	return 1;
}
//////////////////////////////////////////////////////
function echocontent($inode)
{
	if($this->getperm($inode,"r")==0) return -1;
	$info=$this->getinodeinfo($inode);
	$pt=0;
	
	while($pt<$info['size']){
		$len=$this->fileseek($inode,$pt);
		
		if($len==0){
			break;
		}
		$remain=$len;
		for($i=0 ; $i<ceil($len/4096) ; $i++){
			$uu=4096;
			if($remain>4096) $remain-=4096;
			else{
				$uu=$remain;
				$remain=0;
			}
			$data=fread($this->fp,$uu);
			if(trim($info['password'])!=""){
				$data=solveblock($data,$this->sha1key);
			}
			echo $data;
			flush();
		}
		$pt+=$len;
	}
	flush();
	return 1;
}
//////////////////////////////////////////////////////
//////////////////////////////////////////////////////
function grefscopy($other,$path,$copypath,$fname){
	$gf=new grefs($other,$this->uid,$this->gid,$this->md5key,$this->sha1key);
	$this->gettinggrefs($gf,$path,$copypath,$fname);
	$gf->close();
}
	//////////////////////////////////////////////////////
function gettinggrefs($othergf,$path1,$path2,$fname){
	$inode1=$othergf->getpath($path1."/".$fname);
	$inode2=$this->getpath($path2);
	if($inode1<0 || $inode2<0) return -1;
	if($othergf->getperm($inode1,"r")==0) return -1;
	if($this->getperm($inode2,"r")==0) return -1;
	if($this->getperm($inode2,"w")==0) return -1;
	$info1=$othergf->getinodeinfo($inode1);
	$info2=$this->getinodeinfo($inode2);
	
	
	if($info1['type']==5){
		$files=$othergf->getfiles($inode1);
		
		$ind=$this->makefile($path2."/".$fname,5,$info1['password']);
		for($i=0 ; $i<$files['len'] ; $i++){
		$this->gettinggrefs($othergf,$path1."/".$fname,$path2."/".$fname,$files[$i][1]);
		}
		}
		else{ //file
			$pt=0;
			$inode=$this->makefile($path2."/".$fname,0,$info1['password']);
			while(true){
				$info=$this->getinodeinfo($inode);
				if($info['size']<$info1['size']){
					$len=$this->addsize($inode,$info1['size']-$info['size']);
					if($len<=0) return -1;
				}
				else break;
			}

		
		while($pt<$info1['size']){
			$len=$othergf->fileseek($inode1,$pt);
				
			if($len==0){
				break;
			}
			$remain=$len;
			for($i=0 ; $i<ceil($len/4096) ; $i++){
				$uu=4096;
				if($remain>4096) $remain-=4096;
				else{
					$uu=$remain;
					$remain=0;
				}
				$data=fread($othergf->fp,$uu);
				$lenn=$this->fileseek($inode,$pt+$i*4096);
				if($lenn<$uu){
					$_SESSION['error']="SIZE FETAL ERROR";
					return -1;
				}
				fwrite($this->fp,$data,$uu);
		
			}
			$pt+=$len;
		}
	
	}
}
////////////////////////////////////////////////////////////////////////////
function tarfile_put($file,$path){
	$file=str_replace("\\","/",$file);
	$pass=1;
	$dirname=explode("/",$file);
	$inde=$this->makefile($path."/".($dirname[count($dirname)-1]),5,"");
	if($inde==-1) return -1;
	$inode=$inde;
	$inde=-1;
	$fp=fopen($file,"r");
	
	$header=fread($fp,512);
	if(substr($header,0,20)!="grecuspassmd5sha1enc"){
		$shkey="";
		$mhkey="";
	}
	else{
		$shkey=$this->sha1key;
		$mhkey=substr($this->md5key,0,15);
		if(substr($this->md5key,0,15)!=substr($header,400,15)){
			echo "<b><p>[Error: Permission Denied]</b>";
			return -1;
		}
	}
	$this->fsik(-1,0);
	echo substr($header,148,7)."   ".tarfile::getchksum($header);
	if(octdec(substr($header,148,7))==octdec(tarfile::getchksum($header))){
		
		while(!feof($fp)){
			$header=tarfile::passsolve(fread($fp,512),($shkey));
			if(feof($fp))break;
			$fname=tarfile::dirandfile(trim(substr($header,0,100)));
			$fsize=octdec(substr($header,124,12));
			$fchksum=substr($header,148,7);
			$ftype=substr($header,156,1);
			
			if(octdec($fchksum)==octdec(tarfile::getchksum($header))){
				if($fsize>=$this->freespace()){
					$_SESSION[error].="NO SPACE"."/";
					echo "..nospace..";
					return -1;
				}
		
				$inode=$this->makefile($path."/".($dirname[count($dirname)-1])."/".$fname[0]."/".$fname[1],0,"");
				/////////////////////////
				
				$pt=0;
				$this->trun($inode,$fsize);
				while(true){
					$info=$this->getinodeinfo($inode);
					if($info['size']<$fsize){
						$len=$this->addsize($inode,$fsize-$info['size']);
						if($len<=0){
							$_SESSION['error'].="NO FREESPACE";
							 return -1;
						}
					}
					else break;
				}
				while($pt<$fsize){
					$len=$this->fileseek($inode,$pt);
					if($len<=0){
						return -1;
					}
					for($i=0 ; $i<ceil($len/512) ; $i++){
						$uu=512;
						if($i==ceil($len/512)-1) $uu=$len-$i*512;
						fwrite($this->fp,fread($fp,512),$uu);
						fflush($this->fp);
					}
					$pt+=$len;
				}


			
			/////////////////////////
				if($inode<0){
					$_SESSION[error].="MAKEFILE ERROR"."/";
					echo "MAKEFILE ERROR"; return -1;
				}
				echo "[inode] ".$mmh." ".$inode." ".$fname[0]." ".$fname[1];
				echo $this->freespace()."<br>";
				flush();
				
				if($mhkey!="")
					$this->topassfile_onlyheader($inode);
			}
		}
	}
	fclose($fp);
	return $data;
}
//////////////////////////////////////////////////////
function solveout($inode,$filename,$plusdir){
	if($this->getperm($inode,"r")==0) return -1;
	echo "FILENAME : ".$filename."<br>";
	
	$info=$this->getinodeinfo($inode);
	if(trim($info['password'])!="" && trim($info['password'])!=trim(substr($this->md5key,0,15)))
		return -1;
	
	if($info['type']==5){
		$files=$this->getfiles($inode);
		mkdir(iconv("EUC-KR","UTF-8",$plusdir.$filename));
		for($i=0 ; $i<$files['len'] ; $i++){
			$this->solveout($files[$i][0],$files[$i][1],$plusdir."/".$filename."/");
		}
	}
	else{
		$fp2=fopen(iconv("EUC-KR","UTF-8",$plusdir.$filename),"w");
		if(!$fp2) return -1;
		$remain=chartoint($info['size'],5);
		
		$pt=0;
		$echodata="";
		while($pt<$info['size']){
			$len=$this->fileseek($inode,$pt);
			
			if($len==0){
				break;
			}
			$remain=$len;
				for($i=0 ; $i<ceil($len/4096) ; $i++){
					$uu=4096;
					if($remain>4096) $remain-=4096;
					else{
						$uu=$remain;
						$remain=0;
					}
				$data=fread($this->fp,$uu);
				if(trim($info['password'])!=""){
					$data=solveblock($data,$this->sha1key);
				}
				fwrite($fp2,$data,strlen($data));
			}
			$pt+=$len;
		}
		
		fclose($fp2);
	}
}

//////////////////////////////////////////////////////
function uploaddir($path,$dir){
	echo "PATH/DIR : ".$path.",".$dir."<br>";
	flush();
	$upnode=$this->pathtoinode($this->getrootdir(),$path,1);
	if($this->getperm($upnode,"r")==0) return -1;
	if($this->getperm($upnode,"w")==0) return -1;
	if (!$dh = @opendir($dir)) {
		$this->putfile($dir,$path,"");
	} else {
		while (($file = readdir($dh)) !== false) {
			if ($file == "." || $file == "..") continue;
			$nd=$upnode;
			if(is_dir($dir."/".$file)){
				$nd=$this->makepathdir($path."/".$file,0);
				if($nd==-1)return -1;
			}
			$this->uploaddir($path."/".$file,$dir."/".$file);
		}
	}
}

//////////////////////////////////////////////////////
function putfile($src,$path,$password){
	$inode=$this->makefile($path,0,$password);
	if($inode<0) return -1;
	$file=fopen($src,"rb");
	if(!$file){
		$_SESSION['error'].="SRC ERROR: ".$src."<br>";
		return -1;
	}
	$fsize=fsize($src);
	$pt=0;
	$this->trun($inode,$fsize);
	while(true){
		$info=$this->getinodeinfo($inode);
		if($info['size']<$fsize){
			$len=$this->addsize($inode,$fsize-$info['size']);
			if($len<=0){
				$_SESSION['error'].="NO FREESPACE";
				 return -1;
			}
		}
		else break;
	}
	while($pt<$fsize){
		$len=$this->fileseek($inode,$pt);
		if($len<=0){
			return -1;
		}
		for($i=0 ; $i<ceil($len/4096) ; $i++){
			$uu=4096;
			if($i==ceil($len/4096)-1) $uu=$len-$i*4096;
			fwrite($this->fp,fread($file,$uu),$uu);
			fflush($this->fp);
		}
		$pt+=$len;
	}
	
	
	fclose($file);
	
	return $inode;
}
//////////////////////////////////////////////////////
function downtar($inode,$filename,$plusdir){
	
	if($this->getperm($inode,"r")==0) return -1;
	$info=$this->getinodeinfo($inode);
	
	if($info['type']==5){
		$chksum=0;
		$isdir=1;
		$time=time();//"11625406072"
		$header=pack("a100a8a8a8a12a12a8a1a355",$plusdir.$filename,"0100444","0000002","0000002",0,$time,"\x00",$isdir*5,"\x00");
		$chksum=tarfile::getchksum($header);
		$header=substr_replace($header,$chksum,148,7);
		echo $header;
		//dir
		$files=$this->getfiles($inode);
		for($i=0 ; $i<$files['len'] ; $i++){
			if(trim($files[$i][1])=="")break;
			$this->downtar($files[$i][0],$files[$i][1],$plusdir.$filename."/");
		}
	}
	else{
		//file
		$chksum=0;
		$isdir=0;
		$time=time();//"11625406072"
		$header=pack("a100a8a8a8a12a12a8a1a355",$plusdir.$filename,"0100444","0000002","0000002",decoct($info['size']),$time,"\x00",$isdir*5,"\x00");
		$chksum=tarfile::getchksum($header);
		$header=substr_replace($header,$chksum,148,7);
		echo $header;
		$this->echocontent($inode);
		$remainsize=512-($info['size']%512);
		if($remainsize!=512)
			echo pack("a$remainsize","");
	}
}
//////////////////////////////////////////////////////
function setfreespace($num){//실제사이즈
	if($num<0)$num=0;
		$this->fsik(-1,12);
	fwrite($this->fp,inttochar(toint($num/4096),4),4);
}
function freespace(){//블록사이즈
	$this->fsik(-1,12);
	$dt=chartoint(fread($this->fp,4),4);
	return $dt*4096;
}
//////////////////////////////////////////////////////
function chkdsk(){
	$this->forceperm=1;
	$ifff=$this->getinodeinfo($this->getrootdir());
	if(trim($ifff['uid'])!=trim($this->uid)) return -1;
	$this->chkinode();
	$this->chkfreespace();
	
	$this->forceperm=0;
}
function chkinode(){
	for($i=$this->getrootdir()+1 ; $i<$this->maxninode ; $i++){
		if($this->getinodebit($i)==1){
			$info=$this->getinodeinfo($i);
			$upnode=$info['upnode'];
			echo "checking node ".$i."<br> upnode : ".$upnode;
			if($i%100==0)
				flush();
			if($this->getinodebit($upnode)==0){
				$_SESSION[error].="from chkinode, ".$i."/";
				$this->delinode($i);continue;
			}
			$upinfo=$this->getinodeinfo($upnode);
			if(trim($upinfo['password'])=="" || $upinfo['password']==substr($this->md5key,0,15)){
				if($this->exist_inode($upnode,$i)==0){
					$this->delinode($i);
				}
			}
		}
	}
}
function chkfreespace(){
	$nb=0;
	for($i=0 ; $i<$this->maxnblock ; $i++){
		if($this->getblockbit($i)==0){
			$nb++;
		}
	}
	$this->setfreespace($nb*4096-4096*8);
}
//////////////////////////////////////////////////////
function debugdetail(){
	
	$ifff=$this->getinodeinfo($this->getrootdir());
	if($ifff['uid']!=$this->uid) return -1;
	$this->fsik(-1,0);
	echo "<p> superblockinfo <p>";
	$hhh=fread($this->fp,24);
	echo "<code>maxnblock</code> ".chartoint(substr($hhh,0,4),4)."<br>";
	echo "<code>maxninode</code> ".chartoint(substr($hhh,4,4),4)."<br>";
	echo "<code>maxnblockgroup</code> ".chartoint(substr($hhh,8,4),4)."<br>";
	echo "<code>freespace</code> ".chartoint(substr($hhh,12,4),4)."<br>";
	echo "<code>last free block</code> ".chartoint(substr($hhh,16,4),4)."<br>";
	echo "<code>last free inode</code> ".chartoint(substr($hhh,20,4),4)."<br>";
	echo "<p> inodeinfo <p>";
	for($i=0 ; $i<$this->maxninode ; $i++){
		if($this->getinodebit($i)==1){
			$info=$this->getinodeinfo($i);
			echo "<p><b> inode </b> ".$i."<br>";
			echo "<code>uid</code> ".$info['uid']."<br>";
			echo "<code>gid</code> ".$info['gid']."<br>";
			echo "<code>access</code> ".$info['access']."<br>";
			echo "<code>size</code> ".$info['size']."<br>";
			echo "<code>nblocks</code> ".$info['nblocks']."<br>";
			echo "<code>type</code> ".$info['type']."<br>";
			echo "<code>password</code> ".$info['password']."<br>";
			echo "<code>flag</code> ".$info['flag']."<br>";
			echo "<code>upnode</code> ".$info['upnode']."<br>";
			
			$this->printextent(0,$info['extents']);
			/*
			echo "<p>extent header info<br>";
			echo " nextents : ".chartoint(substr($info['extents'],0,2),2)."<br>";
			echo " maxextents : ".chartoint(substr($info['extents'],2,2),2)."<br>";
			echo " depth : ".chartoint(substr($info['extents'],4,1),1)."<br>";
			for($j=0 ; $j<chartoint(substr($info['extents'],0,2),2) ; $j++){
			echo "<p>extent ".$j." info<br>";
			echo " logicalblock : ".chartoint(substr($info['extents'],$j*12+12,4),4)."<br>";
			echo " nblocks : ".chartoint(substr($info['extents'],$j*12+16,2),2)."<br>";
			echo " block : ".chartoint(substr($info['extents'],$j*12+18,4),4)."<br>";
			
			}
			*/
			echo "<p>";
			if($info['type']==5){
				$files=$this->getfiles($i);
				for($j=0 ; $j<count($files) ; $j++){
					if($files[$j][0])
						echo "<code>inode</code> ".$files[$j][0]." <code>filename</code> ".$files[$j][1]."<br>";
				}
			}
		}
	}
	echo "<p> <b>blockinfo</b><p>";
	for($i=0 ; $i<$this->maxnblock ; $i+=32768){
		$this->fsik(floor($i/32768),130*4096);
		$blk=fread($this->fp,4096);
		for($j=0 ; $j<4096 ; $j++){
			if($i+$j>=$this->maxnblock)break;
			echo ord($blk[$j])." ";
		}
	}
}
function printextent($level,$extents){
	$nextents=chartoint(substr($extents,0,2),2);
	$maxextents=chartoint(substr($extents,2,2),2);
	$depth=chartoint(substr($extents,4,1),1);
	echo "<b>".$level." Lev Extents info</b><br>";
	echo " nextents : ".$nextents."<br>";
	echo " maxextents : ".$maxextents."<br>";
	echo " depth : ".$depth."<br>";
	for($i=0 ; $i<$nextents ; $i++){
		echo "<b>info</b> ".$i."<br>";
		$logical=chartoint(substr($extents,12*$i+12,4),4);
		$nblocks=chartoint(substr($extents,12*$i+16,2),2);
		$blockaddr=chartoint(substr($extents,12*$i+18,4),4);
		echo " logical : ".$logical."<br>";
		echo " nblocks : ".$nblocks."<br>";
		echo " blockaddr : ".$blockaddr."<br>";
		if($depth!=0) $this->printextent($level+1,$this->getblock($blockaddr,0,4096));
	}
	echo $level." end <br>";
}
//////////////////////////////////////////////////////
function debug($fname){
	$i=0;
	$k=0;
	fseek($this->fp,0,SEEK_SET);
	while(!feof($this->fp)){
		$i++;
		$ch=fread($this->fp,1);
		if(($k<130 || $k>132 ) && $k!=1167 && $k!=1168 && $k!=2193 && $k>2)
			echo $ch;
		else echo ord($ch)."'$ch' ";
		if($i>=1024*4){
			$i=0;
			echo "[$k]";
			$k++;
			 echo " <br>------------------<br> ";
		}
	}
}
//////////////////////////////////////////////////////
function resize($size){
	$ifff=$this->getinodeinfo($this->getrootdir());
	
	if(trim($ifff['uid'])!=trim($this->uid)) return -1;
	$bsize=4096;
	$blankblock=$this->makeblock("");
	$resizelen=$this->maxnblock+$size;
	if($size>0){
		$this->fsik(floor($this->maxnblock/32768),$bsize*131+($this->maxnblock%32768)*$bsize);
		for($i=$this->maxnblock ; $i<$resizelen ; $i++){
			if($i%32768==0){
				$this->fsik(floor($i/32768),0);
				fwrite($this->fp,$blankblock,$bsize); //block info
				fwrite($this->fp,$blankblock,$bsize); //inode bitmap
				for($j=0 ; $j<128 ; $j++)
					fwrite($this->fp,$blankblock,$bsize); //inode table
				fwrite($this->fp,$blankblock,$bsize); //block bitmap 
				$this->maxnblockgroup++;
				$this->maxninode+=4096;
			}
			fwrite($this->fp,$blankblock,$bsize); //blocks
			$this->maxnblock++;
		}
		$frees=$this->freespace();
		$this->fsik(-1,0);
		fwrite($this->fp,pack("a4a4a4a4",inttochar($this->maxnblock,4),inttochar($this->maxninode,4),inttochar($this->maxnblockgroup,4),inttochar($frees+$size*4096,4)),16);
	}
	else if($size<0){
		$decsize=0;
		for($i=$this->maxnblock-1 ; $i>$this->maxnblock+$size-8 ; $i--){
			if($this->getblockbit($i)==1) break;
			$decsize++;
		}
		$size=-$decsize;
		$resizelen=$this->maxnblock+$size;
		//$this->fsik(floor($resizelen/32768),$bsize*131+($resizelen%32768)*$bsize);
		$reduce=toint($this->maxnblock/32768)-toint($resizelen/32768);
		
		for($i=($this->maxnblockgroup-$reduce)*4096 ; $i<$this->maxninode ; $i++)
		if($this->getinodebit($i)==1) return -1;
		
		$this->maxninode-=$reduce*4096;
		$this->maxnblockgroup-=$reduce;
		$this->maxnblock+=$size;
		$this->fsik($this->maxnblockgroup-1,0);
		ftruncate($this->fp,$bsize*131+($resizelen%32768)*$bsize);
		for($i=$this->maxnblockgroup ; $i<$reduce+$this->maxnblockgroup ; $i++)
			unlink($this->srcfname.$i);
		$frees=$this->freespace();
		$this->fsik(-1,0);
		fwrite($this->fp,pack("a4a4a4a4",inttochar($this->maxnblock,4),inttochar($this->maxninode,4),inttochar($this->maxnblockgroup,4),inttochar($frees+$size*4096,4)),16);
	}
	$this->chkfreespace();

}
//////////////////////////////////////////////////////
function createfs($nblock){
	if($nblock<100) $nblock=100;
	fseek($this->fp,0,SEEK_SET);
	$bsize=1024*4;
	$blankblock=$this->makeblock("");
	$this->maxnblock=$nblock;
	$this->maxninode=ceil($nblock/32768)*4096;
	$this->maxnblockgroup=ceil($nblock/32768);
	$this->fblast=0;
	$this->filast=0;
	//echo "[maxnblock]|$this->maxnblock|[maxinode]|$this->maxninode|[maxblockgroup]|$this->maxnblockgroup|[]";
	$superblock=pack("a4a4a4a4",inttochar($this->maxnblock,4),inttochar($this->maxninode,4),inttochar($this->maxnblockgroup,4),inttochar($this->maxnblock-8,4));
	fwrite($this->fp,$this->makeblock($superblock),$bsize); //superblock
	//////////
	$remainblock=$nblock;
	for($i=0 ; $i<$this->maxnblockgroup ; $i++){
		$this->fsik($i,0);
		fwrite($this->fp,$blankblock,$bsize); //block info
		fwrite($this->fp,$blankblock,$bsize); //inode bitmap
		for($j=0 ; $j<128 ; $j++)
			fwrite($this->fp,$blankblock,$bsize); //inode table
		fwrite($this->fp,$blankblock,$bsize); //block bitmap 
		if($remainblock>32768){
			$uu=32768;
		$remainblock-=32768;
		}
		else{
			$uu=$remainblock;
			$remainblock=0;
		}
		for($j=0 ; $j<$uu ; $j++)
			fwrite($this->fp,$blankblock,$bsize); //blocks
		}
		ftruncate($this->fp,ftell($this->fp));
		$this->setinodebit(0);
		$this->setblockbit(0,1);
		$this->makefile("/",5,""); //root directory
		$this->setchmod($this->getrootdir(),511);
		//////////
	}
};
?>
