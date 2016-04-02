<?
/*
512 Byte
1 block
header[128] : 0-127
flag[iladnuk] : 128-134
header[128] : 135-262
headerblock[1] : 263
1 block
name[256] : 0-255
user[20] : 256-275
group[20] :276-295
access[2] : 296-297
size[8] : 298-305
firstcontent[8] : 306-313
passtype[10] : 314-323
password[20] : 324-343
*/
include "tarfile.php";
function touchfile($path){
htmlsave($path,"");
}
function htmlsave($path,$data){
$pp=explode("*/",$path);
$uu=explode("/",$path);
$filename=$uu[count($uu)-1];
if(count($pp)==1)
$pp=explode("*",$path);

if(count($pp)>1){
$ar=getfspath($pp[0],$pp[1]);
//$ueue=explode("/",$ar);
//$ar=substr($ar,0,strlen($ar)-strlen($ueue[count($ueue)-1]));
if(getperm($ar,"w")==0) return 0;
$fp=fopen($ar,"w");
if(!$fp)return "";
fwrite($fp,$data,strlen($data));
fclose($fp);
fsconv($ar,$filename);
}
else{
$fp=fopen($path,"w");
if($fp){
fwrite($fp,$data,strlen($data));
fclose($fp);
}
}
}
function delete_all($dir){
chmod($dir,octdec(707));
if(!$dh=@opendir($dir)){
unlink("$dir");
return;
}
while(($file=readdir($dh)) !==false){
if($file=="." || $file=="..") continue;
if(is_dir("$dir/$file")){
delete_all("$dir/$file");
} else {
unlink("$dir/$file");
}
}
closedir($dh);
rmdir($dir);
}

function transhtml($txt){
global $dire;
$result="";
while(strpos($txt,"[[img]")){
$p1=strpos($txt,"[[img]");
$result.=substr($txt,0,$p1);
$txt=substr($txt,$p1+6,strlen($txt));
$p2=strpos($txt,"]");
$src=substr($txt,0,$p2);
$result.="<img src=download.php?file=".urlen("$dire/$src").">";
$txt=substr($txt,$p2+1,strlen($txt));
}
$result.=$txt;
return $result;
}

function htmlload($path){
$pp=explode("*/",$path);
if(count($pp)==1)
$pp=explode("*",$path);
if(count($pp)>1){
return htmlcontent(getfspath($pp[0],$pp[1]));
}
else{
if(file_exists(($path))){
$fp=fopen($path,"r");
if(!$fp)return "";
$data=fread($fp,9999999);
return $data;
fclose($fp);
}
}
}
function inttochar($num,$len){
$ary=array();
unset($result);
for($i=0 ; $i<$len ; $i++){
$ary[$i]=$num%256;
$num=floor($num/256);
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
function htmlcontent($nowpath){
if(getperm($nowpath,"r")==0) return 0;
$data="";
$fp=fopen($nowpath,"rb");
if(!$fp)return "";
fseek($fp,263,SEEK_SET);
$number=chartoint(fread($fp,1),1);

fseek($fp,0,SEEK_SET);
$header=fread($fp,512*$number);

$fpasstype=trim(substr($header,512+314,10));
$fpassword=trim(substr($header,512+324,20));

if($fpasstype=="pass1"){
if($fpassword!=substr(md5($_SESSION[key]),0,20))
return 0;
$shpass=sha1($_SESSION[key]);
}
else $shpass="";

fseek($fp,chartoint(substr($header,306+512,8),8),SEEK_SET);
$remain=512*$number;

if($remain>chartoint(substr($header,298+512,8),8))
$remain=chartoint(substr($header,298+512,8),8);
if($remain!=0){
$data.=solveblock(fread($fp,$remain),$shpass);
$remain=chartoint(substr($header,298+512,8),8)-512*$number;
fseek($fp,512*$number,SEEK_SET);
while($remain>0){
$ooo=512;
if($ooo>$remain)
$ooo=$remain;
$data.=solveblock(fread($fp,$ooo),$shpass);
$remain-=$ooo;
flush();
}
}
fclose($fp);
return $data;//transhtml($data);
}
////////////////////


function unnpassfile($nowpath){
if(getperm($nowpath,"w")==0) return 0;
if(issdir($nowpath)==1) $nowpath.="/DIRSET";
$fp=fopen($nowpath,"rb");
if(!$fp)return "";
$tempfp=fopen($nowpath.".tmp","wb");
if(!$tempfp)return "";
fseek($fp,263,SEEK_SET);
$number=chartoint(fread($fp,1),1);

fseek($fp,0,SEEK_SET);
$header=fread($fp,512*$number);

$fpasstype=trim(substr($header,512+314,10));
$fpassword=trim(substr($header,512+324,20));

if($fpasstype=="pass1"){
if($fpassword!=substr(md5($_SESSION[key]),0,20))
return 0;
$shpass=sha1($_SESSION[key]);
}
else $shpass="";

fseek($fp,chartoint(substr($header,306+512,8),8),SEEK_SET);
$remain=512*$number;

if($remain>chartoint(substr($header,298+512,8),8))
$remain=chartoint(substr($header,298+512,8),8);
if($remain!=0){
fwrite($tempfp,solveblock(fread($fp,$remain),$shpass));
$remain=chartoint(substr($header,298+512,8),8)-512*$number;
fseek($fp,512*$number,SEEK_SET);
while($remain>0){
$ooo=512;
if($ooo>$remain)
$ooo=$remain;
fwrite($tempfp,solveblock(fread($fp,$ooo),$shpass));
$remain-=$ooo;
flush();
}
}
fclose($fp);
fclose($tempfp);
unlink($nowpath);
rename($nowpath.".tmp",$nowpath);
clearstatcache();
return 1;
}

//////////////
function fsechocontent($nowpath){

if(getperm($nowpath,"w")==0) return 0;
$fp=fopen($nowpath,"rb");
if(!$fp) return "";

$au=fread($fp,256+7);

$auth="";
for($i=0 ; $i<128 ; $i++)
$auth.=chr($i);
$auth.="iladnuk";
for($i=128 ; $i<256 ; $i++)
$auth.=chr($i);

if($au!=$auth) return "";
fseek($fp,263,SEEK_SET);
$number=chartoint(fread($fp,1),1);

fseek($fp,0,SEEK_SET);
$header=fread($fp,512*$number);


$fpasstype=trim(substr($header,512+314,10));
$fpassword=trim(substr($header,512+324,20));

if($fpasstype=="pass1"){
if($fpassword!=substr(md5($_SESSION[key]),0,20))
return 0;
$shpass=sha1($_SESSION[key]);
}
else $shpass="";

fseek($fp,chartoint(substr($header,306+512,8),8),SEEK_SET);
$remain=512*$number;

if($remain>chartoint(substr($header,298+512,8),8))
$remain=chartoint(substr($header,298+512,8),8);
if($remain!=0){
echo solveblock(fread($fp,$remain),$shpass);
$remain=chartoint(substr($header,298+512,8),8)-512*$number;
fseek($fp,512*$number,SEEK_SET);

while($remain>0){
$ooo=512;
if($ooo>$remain)
$ooo=$remain;
echo solveblock(fread($fp,$ooo),$shpass);
$remain-=$ooo;
flush();
}
}
fclose($fp);
return 1;
}
/////
function getdownload($files,$pass)
{
$file=explode("*/",$files);

if(count($file)>1){
fsechocontent(getfspath($file[0],$file[1]));

} else {
$fp=fopen($files,"r");
if(!$fp)return "";
fseek($fp,0,SEEK_SET);
while(!feof($fp)){
echo fread($fp,512);
flush();
}
fclose($fp);
}
}
function fsreconvdir($nowpath){
if(getperm($nowpath,"w")==0) return 0;

if(!is_dir($nowpath)){
fsreconv($nowpath,"");
return 1;
}
$auth="";
for($i=0 ; $i<128 ; $i++)
$auth.=chr($i);
$auth.="iladnuk";
for($i=128 ; $i<256 ; $i++)
$auth.=chr($i);


if($dh=@opendir($nowpath)){
while(($file=readdir($dh))!== false){
if($file=="." || $file=="..") continue;
if(is_dir($nowpath."/".$file)){
if(file_exists($nowpath."/".$file."/DIRSET")){
$fp=fopen($nowpath."/".$file."/DIRSET","rb");
if(!$fp)continue;
fseek($fp,512,SEEK_SET);
$fname=iconv("UTF-8","euc-kr",trim(fread($fp,256)));
fclose($fp);
rename($nowpath."/".$file,$nowpath."/".$fname);
clearstatcache();
$file=$fname;
}
fsreconvdir($nowpath."/".$file);
@unlink($nowpath."/".$file."/DIRSET");

} else {

$fp=fopen($nowpath."/".$file,"rb");
if(!$fp)continue;
if(fread($fp,256+7)==$auth){
fseek($fp,512,SEEK_SET);
$fname=iconv("UTF-8","euc-kr",trim(fread($fp,256)));
fclose($fp);

rename($nowpath."/".$file,$nowpath."/".$fname);
clearstatcache();
$file=$fname;
fsreconv($nowpath."/".$file,$file);
}
else fclose($fp);
}
}
}
//unlink($nowpath."/DIRSET");
}

function fsreconv($nowpath,$rfname,$pass=""){

$auth="";

for($i=0 ; $i<128 ; $i++)
$auth.=chr($i);
$auth.="iladnuk";
for($i=128 ; $i<256 ; $i++)
$auth.=chr($i);

$shpass=sha1($pass);
$nowpathname=explode("/",$nowpath);
$nowpathname=$nowpathname[count($nowpathname)-1];

if(issdir($nowpath)==0)
$fp=fopen($nowpath,"rb+");
else
$fp=fopen($nowpath."/DIRSET","rb+");
if(!$fp)return "";

if(fread($fp,256+7)==$auth){
	fseek($fp,263,SEEK_SET);
	$number=chartoint(fread($fp,1),1);
	fseek($fp,0,SEEK_SET);
	$header=fread($fp,512*$number);
	$fname=iconv("UTF-8","euc-kr",substr($header,512,256));

	$fpasstype=trim(substr($header,512+314,10));
	$fpassword=trim(substr($header,512+324,20));

	if($fpasstype=="pass1"){
		if($fpassword!=substr(md5($pass),0,20)){
			fclose($fp);
			return 0;
		} else {
			fclose($fp);
			unnpassfile($nowpath,$pass);
			$fname=solveblock($fname,sha1($pass));
$erer=rename(trim($nowpath),trim(substr($nowpath,0,strlen($nowpath)-strlen($nowpathname)).$fname));

			return 1;
		}
	}
	fseek($fp,chartoint(substr($header,306+512,8),8),SEEK_SET);
	$first=fread($fp,512*$number);


	fseek($fp,0,SEEK_SET);
	fwrite($fp,$first);
	$size=chartoint(substr($header,298+512,8),8);
	fseek($fp,$size,SEEK_SET);
	ftruncate($fp,$size);
	fclose($fp);
clearstatcache();
if($rfname=="") $rfname=$fname;
$erer=rename(trim($nowpath),trim(substr($nowpath,0,strlen($nowpath)-strlen($nowpathname)).$rfname));

}
else{
	fclose($fp);
	return 0;
}
return 1;
}

function fsconvdir($nowpath,$pass=""){


$auth="";
for($i=0 ; $i<128 ; $i++)
$auth.=chr($i);
$auth.="iladnuk";
for($i=128 ; $i<256 ; $i++)
$auth.=chr($i);

if($dh=@opendir($nowpath)){

$thisname=explode("/",$nowpath);
$thisname=$thisname[count($thisname)-1];
while(($file=readdir($dh))!== false){
if($file=="." || $file=="..") continue;
if(is_dir($nowpath."/".$file)){
fsconvdir($nowpath."/".$file,$pass);
} else {
fsconv($nowpath."/".$file,$file,$pass);
}
}
closedir($nowpath);
fsconv($nowpath,$thisname,$pass);
}
}

function fsconv($nowpath,$fname,$pass=""){
$auth="";
for($i=0 ; $i<128 ; $i++)
$auth.=chr($i);
$auth.="iladnuk";
for($i=128 ; $i<256 ; $i++)
$auth.=chr($i);
$isdir=0;

if(issdir($nowpath)==1){
$isdir=1;
$nowpath=$nowpath."/DIRSET";
if(file_exists($nowpath)==0){
touch($nowpath);
}
}

if($isdir==1){
$size=fsize($nowpath);
$fp=fopen($nowpath,"rb+");
}
else{
$size=fsize($nowpath);
$fp=fopen($nowpath,"rb+");
}

if(!$fp)return "";

$first=fread($fp,1024);

if(substr($first,0,256+7)==$auth){
fclose($fp);
return 0;
}

fseek($fp,0,SEEK_END);

$firstpos=ftell($fp);
if($firstpos<1024){
fwrite($fp,pack("a".(1024-$firstpos),""));
$firstpos=1024;
}
fwrite($fp,$first);
fseek($fp,0,SEEK_SET);
fwrite($fp,$auth);
fwrite($fp,chr(2));
fwrite($fp,pack("a248",""));

//$fname=explode("/",$nowpath);
//$fname=$fname[count($fname)-1];
$user=$_SESSION[uid];
$group=$_SESSION[gid];
$access=367;

fwrite($fp,pack("a512",pack("a256a20a20a2a8a8",$fname,$user,$group,inttochar($access,2),inttochar($size,8),inttochar($firstpos,8))));
fclose($fp);
if(strlen($pass)>0){
$shpass=sha1($pass);

$fp=fopen($nowpath,"rb");
if(!$fp)return "";
$tempfp=fopen($nowpath.".tmp","wb");
fwrite($tempfp,fread($fp,512));
fwrite($tempfp,passblock(fread($fp,256),$shpass));
fwrite($tempfp,fread($fp,58));
fseek($fp,30,SEEK_CUR);
fwrite($tempfp,pack("a10","pass1"));
fwrite($tempfp,pack("a20",md5($pass)));
fwrite($tempfp,fread($fp,168));

$remain=$size-1024;
while($remain>0){
$ooo=512;
if($remain<$ooo)
$ooo=$remain;
$remain-=$ooo;
fwrite($tempfp,passblock(fread($fp,$ooo),$shpass));
}
fwrite($tempfp,passblock($first,$shpass));

fclose($fp);
fclose($tempfp);
unlink($nowpath);
rename($nowpath.".tmp",$nowpath);
clearstatcache();
}
if($isdir==0)
rename($nowpath,substr($nowpath,0,strlen($nowpath)-strlen($fname)).time().rand(1,1000));
else {
$nowpath=substr($nowpath,0,strlen($nowpath)-strlen("/DIRSET"));
rename($nowpath,substr($nowpath,0,strlen($nowpath)-strlen($fname)).time().rand(1,1000));
}
}
///


function mkfsdir($fname,$path,$pass=""){
$fp=fopen($path,"w");
if(!$fp)return "";
for($i=0 ; $i<128 ; $i++)
fwrite($fp,chr($i));
fwrite($fp,"iladnuk");
for($i=128 ; $i<256 ; $i++)
fwrite($fp,chr($i));
fwrite($fp,chr(2));
fwrite($fp,pack("a248",""));


$user=$_SESSION[uid];
$group=$_SESSION[gid];
$access=inttochar(367,2);
$size=chr(0);
$firstpos=0;

$shpass=sha1($pass);
if(strlen($pass)>0){
fwrite($fp,pack("a256a20a20a2a8a8a10a20",passblock($fname,$shpass),$user,$group,$access,$size,$firstpos,"pass1",md5($pass)));
} else {
fwrite($fp,pack("a256a20a20a2a8a8",$fname,$user,$group,$access,$size,$firstpos));
}
}

function makefsdir($nowpath){
if(getperm($nowpath,"w")==0) return 0;
mkdir($nowpath);
chmod($nowpath,octdec(707));
$fname=explode("/",$nowpath);
$fname=$fname[count($fname)-1];
fsconv($nowpath,$fname);
}
$gfp=array();

function getfspath($basepath,$path){
global $gfp;
if($gfp[$path]){
return $gfp[$path];
}
//echo "GETFSPATH : $basepath , $path <br>";
$uuo=explode("/",$path);
$nowpath=$basepath;
$rrpath="";
$uu=array();
for($i=0 ; $i<count($uuo) ; $i++){
if(strlen(trim($uuo[$i]))>0){
$pathing=getfsfilelist($basepath,$rrpath);
$ext=-1;
for($j=0 ; $j<$pathing['len'] ; $j++)
if($pathing[$j][1]==$uuo[$i])
$ext=$j;
if($ext==-1){
if($i!=count($uuo)-1){
echo "ERROR : NONE($uuo[$i])<br>";
$gfp[$path]="none";
return "none";
}
$gfp[$path]=$nowpath."/".$uuo[$i];
return $nowpath."/".$uuo[$i];
}
else {
$nnext=$pathing[$ext][realfile];
}
$nowpath.="/".$nnext;
$rrpath.="/".$pathing[$ext][1];
}
/*
$nowpath.="/".md5(trim($uuo[$i]));
*/

}
//echo "GETFSPATHRETURN : $nowpath<br>";
$gfp[$path]=$nowpath;
return $nowpath;
}

///////////////////////////////////
function file_list($dir,$pass){
$dir=str_replace("//","/",$dir);
$data=array();
$num=0;
if (!$dh = @opendir($dir)) {
if(strstr($dir,"*/"))
$rr=explode("*/",$dir);
else
$rr=explode("*",$dir);

if(count($rr)==1) return -1;

$ret=getfsfilelist($rr[0],$rr[1]);
return $ret;
}

while (($file = readdir($dh)) !== false) {
if ($file == "." || $file == "..") continue;
$temp="$dir/$file";
$temp=str_replace("//","/",$temp);

if(trim($file)=="fsdata") $data[$num][2]=9;
else if(is_dir($temp))
$data[$num][2]=5;
else $data[$num][2]=0;

$data[$num][1]=$file;
$data[$num][3]=@filesize("$dir/$file");
$data[$num][4]=@fileowner("$dir/$file")."/".@filegroup("$dir/$file");
$data[$num][5]=decoct(@fileperms("$dir/$file"));
$num++;
}
$data['len']=$num;
return $data;
}
$gff=array();
function getfsfilelist($basepath,$path){
global $gff;
if($gff[$path])return $gff[$path];
//echo "GETFSFILELIST : $basepath , $path <br>";
	if(getperm(getfspath($basepath,$path),"r")==0) return 0;
	$uuo=explode("/",$path);
	$uu=array();
	$nn=0;
	for($i=0 ; $i<count($uuo) ; $i++){
		if(strlen(trim($uuo[$i]))>0){
			$uu[$nn]=trim($uuo[$i]);
			$nn++;
		}
	}
	$nowpath=getfspath($basepath,$path);


	$num=0;

$auth="";
for($i=0 ; $i<128 ; $i++)
$auth.=chr($i);
$auth.="iladnuk";
for($i=128 ; $i<256 ; $i++)
$auth.=chr($i);
	if ($dh = @opendir($nowpath)) {
		while (($file = readdir($dh)) !== false) {
			if($file == "." || $file == ".." || $file=="DIRSET") continue;

			$temp="$nowpath/".$file;
			$temp=str_replace("//","/",$temp);

			if(!is_dir($temp))
				$fp=fopen("$temp","r");
			else
				$fp=fopen("$temp/DIRSET","r");
			if(!$fp){
				fsconvdir("$nowpath/$file");
				continue;
			}
			$header=fread($fp,1024);
			fseek($fp,512,SEEK_SET);
			$fname=trim(fread($fp,256));
			fclose($fp);
			if(substr($header,0,256+7)==$auth){
				$fsize=chartoint(substr($header,512+298,8),8);
				$fowner=trim(substr($header,512+256,8));
				$fgroup=trim(substr($header,512+276,8));
				$fperm=chartoint(substr($header,512+296,2),2);

				$fpasstype=trim(substr($header,512+314,10));
				$fpassword=trim(substr($header,512+324,20));
				if($fpasstype=="pass1"){
					if($fpassword==substr(md5($_SESSION[key]),0,20)){
						$fname=trim(solveblock($fname,sha1($_SESSION[key])));
						$data[$num][6]=2;
					}
					else{
						$fname="?????";
						$data[$num][6]=1;
					}
				}
			} else {
				if(!is_dir($nowpath."/".$file))
				fsconv($nowpath."/".$file,$file);
				$fname=$file;
				$fsize=fsize($temp);
				$fowner="ERROR";
				$fgroup="ERROR";
				$fperm=0;
				$fpasstype="";
				$fpassword="";
			}
			if(!is_dir($temp))
				$data[$num][2]=0;
			else
				$data[$num][2]=5;
			$data[$num][1]=$fname;
			$data[$num][3]=$fsize;
			$data[$num][4]="$fowner/$fgroup";
			$data[$num][realfile]="$file";
			$accessmode="";
			if(($fperm&1)>0){$accessmode.="r";} else {$accessmode.="-";}
			if(($fperm&2)>0){$accessmode.="w";} else {$accessmode.="-";}
			if(($fperm&4)>0){$accessmode.="x";} else {$accessmode.="-";}
			if(($fperm&8)>0){$accessmode.="r";} else {$accessmode.="-";}
			if(($fperm&16)>0){$accessmode.="w";} else {$accessmode.="-";}
			if(($fperm&32)>0){$accessmode.="x";} else {$accessmode.="-";}
			if(($fperm&64)>0){$accessmode.="r";} else {$accessmode.="-";}
			if(($fperm&128)>0){$accessmode.="w";} else {$accessmode.="-";}
			if(($fperm&256)>0){$accessmode.="x";} else {$accessmode.="-";}
			$data[$num][5]=$accessmode;

			$num++;
		}
	}
	
	$data['len']=$num;

$gff[$path]=$data;
	//echo "GETFSFILELISTRETURN : $nowpath<br>";
	return $data;
}

function canwrite($dirr=-1){
global $dire;
if($dirr==-1)$dirr=$dire;
$ses=explode("*",$dirr);
$ret=0;

if(substr($dirr,0,strlen($_SESSION[mustdir]))==$_SESSION[mustdir]){
$ret=1;
}
else
$ret=0;

if(count($ses)>1 && $ret==1){
return getperm(getfspath($ses[0],$ses[1]),"w");
} else {
}
return $ret;
}

function makedir($dir,$filename,$pass=""){
if(!canwrite($dir)) return 0;
$oo=explode("*",$dir);
if(count($oo)>1){
$rrr=explode("*/",$dir);
if($rrr[1]) $oo=$rrr;
$oo[1]=trim($oo[1]);

makefsdir(getfspath($oo[0],$oo[1]."/".$filename));
return 1;
}
mkdir("$dir/$filename");
chmod("$dir/$filename",octdec(707));
return 1;
}

function uploadfile($file,$dir,$filename,$mode,$pass){
$oo=explode("*",$dir);
if(count($oo)>1){
$rrr=explode("*/",$dir);
if($rrr[1]) $oo=$rrr;
$oo[1]=trim($oo[1]);
while(true){
$pa=getfspath($oo[0],$oo[1]."/".$filename);
if(!file_exists($pa)) break;
$filename="_".$filename;
}
if(getperm($pa,"w")==0) return 0;
move_uploaded_file($file,getfspath($oo[0],$oo[1]."/".$filename));
fsconv(getfspath($oo[0],$oo[1]."/".$filename),$filename);
}
else
move_uploaded_file($file,"$dir/$filename");
return $filename;
}
///////////////////////////////

function copydir($path,$path2){

if (!$dh = @opendir($path)) {
copy($path,$path2);
return;
}
@mkdir($path2);
@chmod($path2,octdec(707));

while(($file = readdir($dh)) !== false) {
if ($file == "." || $file == "..") continue;
if(is_dir("$path/$file")){
@mkdir($path2."/".$file);
@chmod($path2."/".$file,octdec(707));
copydir("$path/$file","$path2/$file");
}
else{
copy("$path/$file","$path2/$file");
}
}
closedir($dh);
}
///////////////////////////////
function issdir($path){
$pp=explode("*/",$path);
if(count($pp)==1)
$pp=explode("*",$path);
if(count($pp)>1){
return is_dir(getfspath($pp[0],$pp[1]));
}
else{
return is_dir($path);
}
}
///////////////////////////////
function tocompress($makezip,$files){
if(canwrite()==0) return 0;

if(count($files)==0) return;

$uuu=explode("/",$makezip);
$oo=explode("*/",$makezip);
if(count($oo)==1)
$oo=explode("*",$makezip);

if(count($oo)==1)
$zip=new PclZip($makezip);
else
$zip=new PclZip(getfspath($oo[0],$oo[1]));

$zip->create($files);

if(count($oo)==2){
fsconv(getfspath($makezip),$uuu[count($uuu)-1]);
}

}
function toextract($files)
{
if(canwrite()==0) return 0;
if(strlen($files)==0 || count($files)==0 ||trim($files)==".")
return -1;

$uuu=explode("/",$files);
$oo=explode("*/",$files);
if(count($oo)==1)
$oo=explode("*",$files);
if(count($oo)==1){
$zip=new PclZip($files);
$zip->extract($files."_contents");
} else {
fsreconv(getfspath($oo[0],$oo[1]),$uuu[count($uuu)-1],$_SESSION[key]);
$zip=new PclZip(getfspath($oo[0],$oo[1]));
$zip->extract(getfspath($oo[0],$oo[1]."_contents"));
fsconvdir(getfspath($oo[0],$oo[1]."_contents"),$uuu[count($uuu)-1]);
}
}

function topaste($files)
{
global $dire;
if(canwrite()==0) return 0;
if(strlen($files)==0 || count($files)==0 ||trim($files)==".")
return -1;
$ses=explode("*/",$dire);
$oo=explode("*/",$files);
if(count($ses)==1)
$ses=explode("*",$dire);
if(count($oo)==1)
$oo=explode("*/",$files);
$pat=explode("/",$files);
$oou=explode("/",$oo[1]);
$ofof="";
for($i=0 ; $i<count($oou)-1 ; $i++)

$ofof.="/".$oou[$i];
echo $dire;

//oo->ses
if(count($ses)>1){

	if(count($oo)==1){
		if($_SESSION[copymode]==1){
			copydir($files,getfspath($ses[0],$ses[1])."/".$pat[count($pat)-1]);
		}
		else
			rename($files,getfspath($ses[0],$ses[1])."/".$pat[count($pat)-1]);
		clearstatcache();
		if(is_dir(getfspath($ses[0],$ses[1]."/".$pat[count($pat)-1]))){
			$oot=getfspath($ses[0],$ses[1]."/".$pat[count($pat)-1])."/DIRSET";
			$oot=str_replace("//","/",$oot);

			fsconvdir(getfspath($ses[0],$ses[1]."/".$pat[count($pat)-1]));
			echo $oot;
		}
		else
			fsconv(getfspath($ses[0],$ses[1]."/".$pat[count($pat)-1]),$pat[count($pat)-1]);
	} else {
		if($_SESSION[copymode]==1)
			copydir(getfspath($oo[0],$oo[1]),getfspath($ses[0],$ses[1]."/".$pat[count($pat)-1]));
		else
			rename(getfspath($oo[0],$oo[1]),getfspath($ses[0],$ses[1]."/".$pat[count($pat)-1]));
		clearstatcache();
	}

}
else if(count($oo)>1 && count($ses)==1){
	if($_SESSION[copymode]==1)
		copydir(getfspath($oo[0],$oo[1]),$ses[0]."/".$pat[count($pat)-1]);
	else
		rename(getfspath($oo[0],$oo[1]),$ses[0]."/".$pat[count($pat)-1]);
	clearstatcache();
	fsreconvdir($ses[0]."/".$pat[count($pat)-1]);
}
else{
if($_SESSION[copymode]==1)
{
copydir($files,$ses[0]."/".$pat[count($pat)-1]);

}
else
rename($files,$ses[0]."/".$pat[count($pat)-1]);
clearstatcache();
return 0;
}
return 1;
}
///////////////////////////////

function delete($files,$pass)
{
$file=explode("*/",$files);
if(count($file)>1){
if(getperm(getfspath($file[0],$file[1]),"w")==0) return 0;
if(issdir($files)==1)
delete_all(getfspath($file[0],$file[1]));
else
unlink(getfspath($file[0],$file[1]));
}
else{
delete_all($files);
}
return 1;
}

///////////////////////////////
function downsel($files,$filename,$pass)
{
$file=explode("*/",$files);
if(count($file)>1){
$files=getfspath($file[0],$file[1]);
}
if(is_dir($files)){
tarfile::downloaddir($files,"$filename/",1);
} else {
$fp=fopen($files,"r");
if(!$fp)return "";
fseek($fp,0,SEEK_SET);
echo tarfile::makehead(0,$filename,fsize($files),$pass);
$beftell=ftell($fp);
while(!feof($fp)){
$red=fread($fp,512);
if($beftell>=ftell($fp)) break;
$beftell=ftell($fp);
echo pack("a512",$red);
}
fclose($fp);
}
}

function setchmod($path,$chmod){

$oo=explode("*",$path);
if(count($oo)>1){
$rrr=explode("*/",$path);
if($rrr[1]) $oo=$rrr;
$oo[1]=trim($oo[1]);

$ccmd=0;
if(substr($chmod,0,1)=="r") $ccmd+=1;
if(substr($chmod,1,1)=="w") $ccmd+=2;
if(substr($chmod,2,1)=="x") $ccmd+=4;
if(substr($chmod,3,1)=="r") $ccmd+=8;
if(substr($chmod,4,1)=="w") $ccmd+=16;
if(substr($chmod,5,1)=="x") $ccmd+=32;
if(substr($chmod,6,1)=="r") $ccmd+=64;
if(substr($chmod,7,1)=="w") $ccmd+=128;
if(substr($chmod,8,1)=="x") $ccmd+=256;
if(is_dir(getfspath($oo[0],$oo[1]))==0)
$fp=fopen(getfspath($oo[0],$oo[1]),"rb+");
else
$fp=fopen(getfspath($oo[0],$oo[1])."/DIRSET","rb+");
if(!$fp)return "";
fseek($fp,256+512,SEEK_SET);
$owner=trim(fread($fp,20));
$group=trim(fread($fp,20));
if($owner!=$_SESSION[uid] && $group!=$_SESSION[uid]){
fclose($fp);
return 0;
}
fseek($fp,296+512,SEEK_SET);
fwrite($fp,inttochar($ccmd,2));
fclose($fp);
} else {
chmod($path,octdec($chmod));
}
}
function getfsinfo($nowpath){
$info="";
if(is_dir($nowpath)==0)
$fp=fopen($nowpath,"r");
else{
if(file_exists($nowpath."/DIRSET")==0){
//fsconv($nowpath,"ROOT");
}
$fp=fopen($nowpath."/DIRSET","r");
}
if(!$fp){return "";
}
$header=fread($fp,1024);
fseek($fp,512,SEEK_SET);
$info[fname]=trim(fread($fp,256));
fclose($fp);
$info[size]=chartoint(substr($header,512+298,8),8);
$info[uid]=trim(substr($header,512+256,8));
$info[gid]=trim(substr($header,512+276,8));
$info[access]=chartoint(substr($header,512+296,2),2);
return $info;
}
/////

function getperm($nowpath, $type){

$fname=explode("/",$nowpath);
$fname=$fname[count($fname)-1];
if(issdir($nowpath)==0){
$uupath=substr($nowpath,0,strlen($nowpath)-strlen($fname)-1);
if(getperm($uupath,$type)==0)
return 0;
}

if(file_exists($nowpath)==0)
return 1;

$info=getfsinfo($nowpath);

//if(trim($info['password']) && trim($info['password'])!=substr(md5($_SESSION[key]),0,20))
//return 0;
if($info['uid']==$_SESSION[uid]){
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
else if($info['gid']==$_SESSION[gid]){
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
///////
function changename($dir,$filename,$filename2,$pass){
if(!canwrite($dir)) return 0;
$oo=explode("*",$dir);
if(count($oo)>1){
$rrr=explode("*/",$dir);
if($rrr[1]) $oo=$rrr;
$oo[1]=trim($oo[1]);

if(!issdir("$dir/$filename")){
//rename(getfspath($oo[0],$oo[1]."/".$filename),getfspath($oo[0],$oo[1]."/".$filename2));
clearstatcache();
fsreconv(getfspath($oo[0],$oo[1]."/".$filename),$filename2);
fsconv(getfspath($oo[0],$oo[1]."/".$filename2),$filename2);
} else {
//rename(getfspath($oo[0],$oo[1]."/".$filename),getfspath($oo[0],$oo[1]."/".$filename2));

clearstatcache();
fsreconv(getfspath($oo[0],$oo[1]."/".$filename),$filename2);
fsconv(getfspath($oo[0],$oo[1]."/".$filename2),$filename2);
}
return 1;
}
rename("$dir/$filename","$dir/$filename2");
clearstatcache();
return 1;
}



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
///////////////
function topassing($path){
if(canwrite($path)==0) return 0;
$uuu=explode("/",$path);
$fname=$uuu[count($uuu)-1];
$oo=explode("*",$path);
if(count($oo)>1){
$rrr=explode("*/",$path);
if($rrr[1])$oo=$rrr;
$oo[1]=trim($oo[1]);
fsreconv(getfspath($oo[0],$oo[1]),$fname);
fsconv(getfspath($oo[0],$oo[1]),$fname,$_SESSION[key]);
}
}
function tosolving($path){
if(canwrite($path)==0) return 0;
$uuu=explode("/",$path);
$fname=$uuu[count($uuu)-1];
$oo=explode("*",$path);
if(count($oo)>1){
$rrr=explode("*/",$path);
if($rrr[1])$oo=$rrr;
$oo[1]=trim($oo[1]);
fsreconv(getfspath($oo[0],$oo[1]),$fname,$_SESSION[key]);
clearstatcache();
fsconv(getfspath($oo[0],$oo[1]),$fname);
}
}
////////////////////////////////////////////////////////////////////////////
function passblock($block,$shakey){
$len=strlen($block);
$ret="";
for($i=0 ; $i<ceil($len/512)*512 ; $i+=512){
$ret.=makepass(substr($block,$i,512),$shakey);
}
return substr($ret,0,$len);
}
function solveblock($block,$shakey){
$len=strlen($block);
$ret="";
for($i=0 ; $i<ceil($len/512)*512 ; $i+=512){
$ret.=passsolve(substr($block,$i,512),$shakey);
}
return substr($ret,0,$len);
}
?>
