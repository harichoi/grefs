<?
class Tarfile{
////////////////////////////////////////////////////////////////////////////
function passsolve($block,$shakey){
$len=strlen($block);
if(!$shakey) return $block;
$before=$block;
$block[0]=chr(ord($block[0])-ord($shakey[0]));
for($i=1 ; $i<strlen($block) ; $i++){
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
function init(){
header("Content-Type: Application/x-tar");
//header("Content-Type: Application/octet-stream");
}
////////////////////////////////////////////////////////////////////////////
function tarfile_indelete($file,$dir,$pass){
echo "[indelete]";
$fp=fopen($file,"r+");
$header=fread($fp,512);
if(substr($header,0,20)!="grecuspassmd5sha1enc"){
fseek($fp,0,SEEK_SET);
$shpass="";
}
else $shpass=sha1($pass);
while(!feof($fp)){
$header=fread($fp,512);
$header=tarfile::passsolve($header,$shpass);
$fname=trim(substr($header,0,100));
$fsize=octdec(substr($header,124,12));
$fsz=ceil(($fsize)/512)*512+512;
$fchksum=substr($header,148,7);
$ftype=substr($header,156,1);
if($ftype==0) $ftype=1;
if(octdec($fchksum)==octdec(tarfile::getchksum($header)) && $fname){
echo "[find_incomplete($fname,$dir)]";
if($fname==$dir){
echo "[find complete]";
fseek($fp,-512,SEEK_CUR);
while(true){
fseek($fp,$fsz,SEEK_CUR);
$block=fread($fp,512);
if(feof($fp))break;
fseek($fp,-$fsz-512,SEEK_CUR);
fwrite($fp,$block,512);
}
fseek($fp,-$fsz-512,SEEK_CUR);
ftruncate($fp,ftell($fp));
echo "[indelete_finish]";
return 1;
}


}

fseek($fp,ceil(($fsize)/512)*512,SEEK_CUR);
}
fclose($fp);
}

/////////////////////////////////////////////////////////////////////////////
function tarfile_inupload($src,$file,$dir,$pass){
echo "[tarfile_inupload]";
$beftell=0;
$fp=fopen($file,"r+");
if(!$fp){
echo " error";
return 0;
}
$header=fread($fp,512);
if(substr($header,0,20)!="grecuspassmd5sha1enc"){
fseek($fp,0,SEEK_SET);
$shpass="";
$pass="";
}
else $shpass=sha1($pass);
while(!feof($fp)){
$beftell=ftell($fp);
$header=fread($fp,512);
$header=tarfile::passsolve($header,$shpass);
$fname=trim(substr($header,0,100));
$fsize=octdec(substr($header,124,12));
$fchksum=substr($header,148,7);
$ftype=substr($header,156,1);
if($ftype==0) $ftype=1;
if(octdec($fchksum)!=octdec(tarfile::getchksum($header))){
fseek($fp,$beftell,SEEK_SET);
$fsp=fopen("$src","r");
$head=tarfile::makehead(0,$dir,filesize($src),$pass);
$head=tarfile::makepass($head,$shpass);
fwrite($fp, $head,512);
while(!feof($fsp)){
$block=fread($fsp,512);
$block=tarfile::makepass($block,$shpass);
fwrite($fp,$block,512);
}
fclose($fsp);
break;
}


fseek($fp,ceil(($fsize)/512)*512,SEEK_CUR);
}
fclose($fp);
echo "[tarfile_inupload finished]";
}

////////////////////////////////////////////////////////////////////////////
function solvefile($fp,$fname,$fsize,$ftype){
echo "Ftype : $ftype Fsize : $fsize Fname : $fname<br>";
if($ftype=="0"){
echo "makefile $fname<br>";

$sp=explode("/",$fname);
if(count($sp)>1){
$rn="$sp[0]";
if(!is_dir($rn)){
mkdir($rn,0777);
chmod($rn,0707);
echo "mkdir $rn<br>";
}
for($i=1 ; $i<count($sp)-1 ; $i++){
$rn.="/".$sp[$i];
if(!is_dir($rn)){
mkdir($rn,0777);
chmod($rn,0707);
echo "mkdir $rn<br>";
}
}
}

$fpw=fopen($fname,"w");
$cnt=0;
for($i=0 ; $i<$fsize/512 ; $i++){
$block=fread($fp,512);
$cnt+=512;
if($cnt<=$fsize)
fwrite($fpw,$block,512);
else
fwrite($fpw,$block,$fsize-$cnt+512);
}
fclose($fpw);
}
else if($ftype=="5"){
mkdir($fname,0777);
chmod($fname,0707);
echo "mkdir $fname<br>";
}
}
////////////////////////////////////////////////////////////////////////////
function getchksum($header){
for($i=0 ; $i<512 ; $i++){
if($i>=148 && $i<148+8) continue;
$chksum+=ord($header[$i]);
}
$chksum+=256;
$chksum=sprintf("%o",$chksum);
$len=strlen($chksum);
for($i=0 ; $i<=6-$len ; $i++){
$chksum="0".$chksum;
}
return $chksum;
}
////////////////////////////////////////////////////////////////////////////
function delete($files,$pass)
{
$file=explode("*/",$files);
if(count($file)>1){
tarfile::tarfile_indelete($file[0],$file[1],$pass);
}
else{return 0;
}
return 1;
}

////////////////////////////////////////////////////////////////////////////
function download($files,$pass)
{
$file=explode("*/",$files);
if(count($file)>1){
tarfile::tarfile_indown($file[0],$file[1],$pass);
}
else{
$fp=fopen($files,"r");
$header=fread($fp,512);
if(substr($header,0,20)=="grecuspassmd5sha1enc"){

while(!feof($fp)){
$shpass=sha1($pass);
if($pass=="") $shpass="";
echo tarfile::passsolve(fread($fp,512),$shpass);
}
}
else{
fseek($fp,0,SEEK_SET);
while(!feof($fp)){
echo fread($fp,512);
}
}
fclose($fp);
}
}
////////////////////////////////////////////////////////////////////////////

function download_withheader($files,$filename,$pass)
{
$file=explode("*/",$files);
if(count($file)>1){
tarfile::tarfile_indown_withheader($file[0],$file[1],$pass);
}
elseif(is_dir($files)){
tarfile::downloaddir($files,"$filename/",1);
}
else{
$fp=fopen($files,"r");
$header=fread($fp,512);
if(substr($header,0,20)=="grecuspassmd5sha1enc" && substr($header,400,15)==substr(md5($pass),0,15)){
echo tarfile::makehead(0,$filename,filesize($files),$pass);
while(!feof($fp)){
$shpass=sha1($pass);
if($pass=="") $shpass="";
echo tarfile::passsolve(fread($fp,512),$shpass);
}
}
else{
fseek($fp,0,SEEK_SET);
echo tarfile::makehead(0,$filename,filesize($files),$pass);
$beftell=ftell($fp);
while(!feof($fp)){
$red=fread($fp,512);
if($beftell>=ftell($fp)) break;
$beftell=ftell($fp);
echo pack("a512",$red);
}
}
fclose($fp);
}
}
////////////////////////////////////////////////////////////////////////////

function uploadfile($file,$dir,$filename,$mode,$pass){
echo "upload";
$oo=explode("*",$dir);
if(count($oo)>1){
$rrr=explode("*/",$dir);
if($rrr[1]) $oo=$rrr;
$oo[1]=trim($oo[1]);
if($oo[1]) $oo[1].="/";
echo "[1]";
tarfile::tarfile_indelete($oo[0],"$oo[1]$filename",$pass);
echo "[2]";
tarfile::tarfile_inupload($file,$oo[0],"$oo[1]$filename",$pass);
echo "[3]";
return 1;
}
if($pass=="" && $mode==1) return 0;
$fp=fopen($file,"r");
$head=fread($fp,512);
if(substr($head,0,20)=="grecuspassmd5sha1enc"){
return 0;
}
fseek($fp,0,SEEK_SET);
if(octdec(substr($head,148,7))!=octdec(tarfile::getchksum($head))){
if($mode==0)
 return 0;
$header=tarfile::passhead($pass);
$ffp=fopen("$dir/$filename".".tar","w");
fwrite($ffp,$header,512);
$header=tarfile::makehead(0,$filename,filesize($file),$pass);
$header=tarfile::makepass($header,sha1($pass));
fwrite($ffp,$header,512);
while(!feof($fp)){
$block=fread($fp,512);
$shpass=sha1($pass);
if($pass=="")$shpass="";
$block=tarfile::makepass($block,sha1($pass));
fwrite($ffp,$block,512);
}
fclose($ffp);
return 1;

}
if($mode==1){
$header=tarfile::passhead($pass);
$ffp=fopen("$dir/$filename","w");
fwrite($ffp,$header,512);
while(!feof($fp)){
$block=fread($fp,512);
$shpass=sha1($pass);
if($pass=="")$shpass="";
$block=tarfile::makepass($block,sha1($pass));
fwrite($ffp,$block,512);
}
fclose($ffp);
}
else{
while($head=fread($fp,512)){
$fname=substr($head,0,100);
$fsize=octdec(substr($head,124,12));
$ftype=substr($head,156,1);
Tarfile::solvefile($fp,"$dir/$fname",$fsize,$ftype);
}
}
fclose($fp);
return 1;
}
////////////////////////////////////////////////////////////////////////////
function makehead($isdir,$name,$length,$key){

$chksum=0;
$time=time();//"11625406072"
$header=pack("a100a8a8a8a12a12a8a1a355",sprintf("%s",$name),"0100444","0000002","0000002",sprintf("%11o",$length),sprintf("%11o",$time),"\x00",$isdir*5,"\x00");
$chksum=tarfile::getchksum($header);
$header=substr_replace($header,$chksum,148,7);
return $header;
}
////////////////////////////////////////////////////////////////////////////
function passhead($key){
$isdir=1;
$name="grecuspassmd5sha1enc";
$length="00000000000";
$chksum=0;
$time=time();//"11625406072"
$header=pack("a100a8a8a8a12a12a8a1a355",sprintf("%s",$name),"0100444","0000002","0000002",$length,$time,"\x00",$isdir*5,"\x00");
$md=md5($key);
$header=substr_replace($header,$md,400,15);
$chksum=tarfile::getchksum($header);
$header=substr_replace($header,$chksum,148,7);
return $header;
}
////////////////////////////////////////////////////////////////////////////
function tarfile_indown($file,$dir,$pass){
$fp=fopen($file,"r");
$header=fread($fp,512);
if(substr($header,0,20)!="grecuspassmd5sha1enc"){
fseek($fp,0,SEEK_SET);
$shpass="";
}
else $shpass=sha1($pass);
while(!feof($fp)){
$header=fread($fp,512);
$header=tarfile::passsolve($header,$shpass);
$fname=trim(substr($header,0,100));
$fsize=octdec(substr($header,124,12));
$fchksum=substr($header,148,7);
$ftype=substr($header,156,1);
if($ftype==0) $ftype=1;
if(octdec($fchksum)==octdec(tarfile::getchksum($header)) && $fname){
if($fname==$dir){
$cnt=0;
for($i=0 ; $i<$fsize/512 ; $i++){
$block=fread($fp,512);
$cnt+=512;
if($cnt>$fsize)
$block=substr($block,0,$fsize-$cnt+512);
echo tarfile::passsolve($block,$shpass);
}
break;
}
}

fseek($fp,ceil(($fsize)/512)*512,SEEK_CUR);
}
fclose($fp);
}
////////////////////////////////////////////////////////////////////////////
function tarfile_indown_withheader($file,$dir,$pass){
$temp=0;
$befpt=-1;
$fp=fopen($file,"r");
$header=fread($fp,512);
if(substr($header,0,20)!="grecuspassmd5sha1enc"){
fseek($fp,0,SEEK_SET);
$shpass="";
}
else $shpass=sha1($pass);
if(substr($header,400,15)!=substr(md5($pass),0,15)) $shpass="";
while(!feof($fp)){
$temp=0;
$header=fread($fp,512);
$header=tarfile::passsolve($header,$shpass);
$fname=trim(substr($header,0,100));
$fsize=octdec(substr($header,124,12));
$fchksum=substr($header,148,7);
$ftype=substr($header,156,1);
if($ftype==0) $ftype=1;
if(octdec($fchksum)==octdec(tarfile::getchksum($header)) && $fname){
if(strstr($fname,$dir)){
echo $header;
$cnt=0;
$temp=1;
for($i=0 ; $i<$fsize/512 ; $i++){
$block=fread($fp,512);
$block=pack("a512",$block);
echo tarfile::passsolve($block,$shpass);
}
}
}
if($temp==0)
fseek($fp,ceil(($fsize)/512)*512,SEEK_CUR);
if($befpt>=ftell($fp)) break;
$befpt=ftell($fp);
}
fclose($fp);
}
////////////////////////////////////////////////////////////////////////////

function tarfile_isdir($file,$dir,$key){
return 0;
}

////////////////////////////////////////////////////////////////////////////
function tarfile_list($file,$dir,$key){
$dirs=array();
$data=array();
$pass=1;
//$dir=tarfile::dirandfile($dir);

$fp=fopen($file,"r");
$header=fread($fp,512);
if(substr($header,0,20)!="grecuspassmd5sha1enc"){
$shkey="";
}
else{
 $shkey=sha1($key);
if(substr(md5($key),0,15)!=substr($header,400,15)){
echo "<b><p>[Error: Permission Denied]</b>";
return $data;
}
}
fseek($fp,0,SEEK_SET);
echo substr($header,148,7)."   ".tarfile::getchksum($header);
if(octdec(substr($header,148,7))==octdec(tarfile::getchksum($header))){

while(!feof($fp)){
$header=tarfile::passsolve(fread($fp,512),($shkey));
if(feof($fp))break;
$fname=tarfile::dirandfile(trim(substr($header,0,100)));
$fsize=octdec(substr($header,124,12));
$fchksum=substr($header,148,7);
$ftype=substr($header,156,1);
$num=0;
if(octdec($fchksum)==octdec(tarfile::getchksum($header)) && $fname[0]==$dir && $fname[1]){

$data[$num][2]=$ftype;
$data[$num][1]=trim($fname[1]);
$num++;
if($ftype==5) $dirs[$fname[1]]=1;
}

while(strlen(trim($fname[0]))>0){
$fname=tarfile::dirandfile($fname[0]);
if($fname[0]==$dir && $dirs[$fname[1]]==0){
$dirs[$fname[1]]=1;
$data[$num][2]=$ftype;
$data[$num][1]=trim($fname[1]);
$num++;
if($ftype==5) $dirs[$fname[1]]=1;
}
}
fseek($fp,ceil(($fsize)/512)*512,SEEK_CUR);
}
}
fclose($fp);
return $data;
}
////////////////////////////////////////////////////////////////////////////
function dirandfile($file){
$file=explode("/",trim($file));
$data=array();
$mm=1;
if(trim($file[count($file)-1])=="")
$mm=2;


for($i=0 ; $i<count($file)-$mm-1 ; $i++){
$data[0].=trim($file[$i])."/";
}
$data[0].=$file[count($file)-$mm-1];
$data[1]=$file[count($file)-$mm];
return $data;
}
////////////////////////////////////////////////////////////////////////////
function isdir($dir,$pass){
if(is_file($dir)) return 0;
$dir=str_replace("//","/",$dir);
if (!$dh = @opendir($dir)) {
if(strstr($dir,"*/"))
$rr=explode("*/",$dir);
else
$rr=explode("*",$dir);

return tarfile::tarfile_isdir($rr[0],$rr[1],$pass);
break;
}
return 1;
}

////////////////////////////////////////////////////////////////////////////
function file_list($dir,$pass){
$dir=str_replace("//","/",$dir);
$data=array();

if (!$dh = @opendir($dir)) {
if(strstr($dir,"*/"))
$rr=explode("*/",$dir);
else
$rr=explode("*",$dir);
echo $rr[0];
return tarfile::tarfile_list($rr[0],$rr[1],$pass);
break;
}
while (($file = readdir($dh)) !== false) {
if ($file == "." || $file == "..") continue;
$temp="$dir/$file";
$temp=str_replace("//","/",$temp);
if(is_dir("$temp"))
$data[]=5;
else if(substr($file,-3,3)=="tar") $data[]=9;
else $data[]=1;
$data[]=$file;
}
return $data;
}
////////////////////////////////////////////////////////////////////////////
function downloaddir($dir,$plusdir,$get_dir){
if (!$dh = @opendir($dir)) {
return;
}
while (($file = readdir($dh)) !== false) {
if ($file == "." || $file == "..") continue;
if(is_dir("$dir/$file")){
tarfile::addfile("$dir/$file","$plusdir$file","");
if($get_dir==1){
downloaddir("$dir/$file",$plusdir."$file/",$get_dir);
}
}
else{
tarfile::addfile("$dir/$file","$plusdir$file","");
}

}
}
/////////////////////////////////////////////////////////////////////////////
function tarfile_change($file,$dir,$key){
$dirs=array();
$data=array();
$pass=1;
//$dir=tarfile::dirandfile($dir);

$fp=fopen($file,"r+");
$header=fread($fp,512);
if(substr($header,0,20)!="grecuspassmd5sha1enc"){
$shkey="";
}
else{
 $shkey=sha1($key);
if(substr(md5($key),0,15)!=substr($header,400,15)){
echo "<b><p>[Error: Permission Denied]</b>";
return $data;
}
}
fseek($fp,0,SEEK_SET);
echo substr($header,148,7)."   ".tarfile::getchksum($header);
if(octdec(substr($header,148,7))==octdec(tarfile::getchksum($header))){
$num=0;
while(!feof($fp)){
$num++;
$header=tarfile::passsolve(fread($fp,512),($shkey));
if(feof($fp))break;
$fname=ceil($num/100)."/".$num.substr(trim(substr($header,0,100)),-4,4);
$fsize=octdec(substr($header,124,12));
$fchksum=substr($header,148,7);
$ftype=substr($header,156,1);
if($ftype==0) $ftype=0;
if($ftype==5) $ftype=1;
$hh=tarfile::makehead($ftype,$fname,$fsize,$shkey);

/////


if(octdec($fchksum)==octdec(tarfile::getchksum($header))){
fseek($fp,-512,SEEK_CUR);
fwrite($fp,tarfile::makepass($hh,sha1($key)),512);
$data[]=$ftype;
$data[]=trim($fname[1]);
if($ftype==5) $dirs[$fname[1]]=1;
}

while(strlen(trim($fname[0]))>0){
$fname=tarfile::dirandfile($fname[0]);
if($fname[0]==$dir && $dirs[$fname[1]]==0){
$dirs[$fname[1]]=1;
$data[]=5;
$data[]=trim($fname[1]);
}
}
fseek($fp,ceil(($fsize)/512)*512,SEEK_CUR);
}
}
fclose($fp);
return $data;
}
////////////////////////////////////////////////////////////////////////////
function addfile($fileurl,$filename,$shakey){
$isdir=0;
$name=$filename;
$length=filesize("$fileurl");
if(is_dir($fileurl)==1) $isdir=1;
if($isdir==0)
$fp=fopen("$fileurl","rb");

$length=sprintf("%o",$length);
$len=strlen($length);
for($i=0 ; $i<=10-$len ; $i++){
$length="0".$length;
}
$chksum=0;
$time=time();//"11625406072"
$header=pack("a100a8a8a8a12a12a8a1a355",sprintf("%s",$name),"0100444","0000002","0000002",$length,$time,"\x00",$isdir*5,"\x00");
$chksum=tarfile::getchksum($header);
$header=substr_replace($header,$chksum,148,7);
echo $header;
if($is_dir==0){
for($i=0 ; $i<(filesize("$fileurl")/512) ; $i++){
$block=pack("a512",fread($fp,512));
if($shakey)
$block=makepass($block,$shakey);
echo $block;
}
}
//echo pack("a512","\x00");
if($fp)
fclose($fp);

}
}
class gdb{
//////////////////////////////////////////////////////////////
function clean($file,$file2){
$fp=fopen($file,"r");
$fp2=fopen($file2,"w");
if($fp && $fp2){}else return 0;
while(!feof($fp)){
$block=fread($fp,512);
if(trim($block)!="")
fwrite($fp2,$block,512);
}
fclose($fp);
fclose($fp2);
unlink($file);
rename($file2,$file);
return 1;
}
//////////////////////////////////////////////////////////////
function issame($dt1,$dt2){
if($dt1==$dt2) return 1;
if(substr($dt1,0,1)=="*"){
if(substr($dt1,1,strlen($dt1)-1)==substr($dt2,-strlen($dt1)+1,strlen($dt1)-1))
return 1;
}
if(substr($dt1,-1,1)=="*"){
if(substr($dt1,0,strlen($dt1)-1)==substr($dt2,0,strlen($dt1)-1))
return 1;
}
return 0;
}
//////////////////////////////////////////////////////////////
function delname($file,$name,$key){
$rt=0;
$last=512;
$next=0;
$fsz=filesize($file);
$fp=fopen($file,"r+");
$he=fread($fp,512);
if(substr($he,0,20)!="grecuspassmd5sha1enc"){
$key="";
$last=0;
fseek($fp,0,SEEK_SET);
}
elseif(substr($he,400,15)!=substr(md5($key),0,15))
return 0;
while(!feof($fp)){
$beftell=ftell($fp);

$data=fread($fp,512);

$block=tarfile::passsolve($data,$key);
if(gdb::issame(trim($name),trim(substr($block,0,100)))==1) $rt=1;
if(gdb::issame(trim($name),trim(substr($block,0,100)))==1 || strlen(trim($data))==0){
//del
$bf=ftell($fp);
$nxtblock=fread($fp,512);
if($bf==ftell($fp))break;
fseek($fp,-1024,SEEK_CUR);
fwrite($fp,$nxtblock,512);
fwrite($fp,pack("a512","\x00"),512);
fseek($fp,-512,SEEK_CUR);
}
else $last=ftell($fp)+512;
}
//ftruncate($fp,$last);
fclose($fp);
return $rt;
}
//////////////////////////////////////////////////////////////
function getvalue($file,$name,$key){
$fp=fopen($file,"r");
$he=fread($fp,512);
if(substr($he,0,20)!="grecuspassmd5sha1enc"){
$key="";
fseek($fp,0,SEEK_SET);
}
elseif(substr($he,400,15)!=substr(md5($key),0,15))
return 0;
while(!feof($fp)){
$beftell=ftell($fp);
$block=tarfile::passsolve(fread($fp,512),$key);
if(trim(substr($block,0,100))==trim($name)){
fclose($fp);
return substr($block,100,100);
}
if($beftell>=ftell($fp)) break;
}
fclose($fp);
}
//////////////////////////////////////////////////////////////
function setvalue($file,$name,$value,$key){
if(canwrite($file)==0) return 0;
if(file_exists($file)){
$fp=fopen($file,"r+");
$he=fread($fp,512);
if(substr($he,0,20)!="grecuspassmd5sha1enc"){
$key="";
fseek($fp,0,SEEK_SET);
}
elseif(substr($he,400,15)!=substr(md5($key),0,15))
return 0;
}
else{
$fp=fopen($file,"w");
fclose($fp);
$fp=fopen($file,"r+");
if(trim($key)!=""){
$he=pack("a512","grecuspassmd5sha1enco");
$he=substr_replace($he,md5($key),400,15);
fwrite($fp,$he,512);
}
}
while(!feof($fp)){
$beftell=ftell($fp);
$block=tarfile::passsolve(fread($fp,512),$key);
if(trim(substr($block,0,100))==trim($name)){
$block=substr_replace($block,$value,100,100);
fseek($fp,$beftell,SEEK_SET);
fwrite($fp,tarfile::makepass($block,$key),512);
return 1;
}
if($beftell>=ftell($fp))break;
}
$block=pack("a100a100a312",$name,$value,"\x00");
fwrite($fp,tarfile::makepass($block,$key),512);
fclose($fp);
}
//////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////
}
?>
