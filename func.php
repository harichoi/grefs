<?
function logwrite($logs,$temp)
{
$fp=fopen("log.log","a");
fwrite($fp,$logs,$temp);
fclose($fp);
}
function isblank($str) {
		$temp=str_replace("&#65533;&#65533;&#65533;","",$str);
		$temp=str_replace("\n","",$temp);
		$temp=strip_tags($temp);
		$temp=str_replace("&nbsp;","",$temp);
		$temp=str_replace(" ","",$temp);
		return 1;
	}
function encoval($ch){
$ch=ceil($ch-0.9999999999);
if($ch<10) return chr($ch+ord("0"));
return chr($ch-10+ord("a"));
}
function decoval($ch){
if(ord($ch)>=ord("a") && ord($ch)<=ord("z")) return ord($ch)-ord("a")+10;
return ord($ch)-ord("0");
}
function escape($str) 
    { 
return $str;
      $len = strlen($str); 
      for($i=0,$s='';$i<$len;$i++) { 
          $ck = substr($str,$i,1); 
          $ascii = ord($ck); 
          if($ascii > 127) $s .= '%u'.toUnicode(substr($str, $i++, 2)); 
          else $s .= (in_array($ascii, array(42, 43, 45, 46, 47, 64, 95))) ? $ck : '%'.strtoupper(dechex($ascii)); 
      } 
      return $s; 
    } 
    
    function toUnicode($word) { 
      $word = iconv('UHC', 'UTF-16LE', $word); 
      return strtoupper(str_pad(dechex(ord(substr($word,1,1))),2,'0',STR_PAD_LEFT).str_pad(dechex(ord(substr($word,0,1))),2,'0',STR_PAD_LEFT));
    } 

function enco($str){
$result="";
$str=escape($str);
for($i=0 ; $i<strlen($str) ; $i++){
$result.=encoval(ord($str[$i])/16).encoval(ord($str[$i])%16);
}
return $result;
}
function Unescape($str){
return $str;
    return urldecode(preg_replace_callback('/%u([[:alnum:]]{4})/', 'UnescapeFunc', $str));
}

function UnescapeFunc($str){
    return iconv('UTF-16LE', 'euc-kr', chr(hexdec(substr($str[1], 2, 2))).chr(hexdec(substr($str[1],0,2))));
}


function deco($str){
$result="";
for($i=0 ; $i<strlen($str) ; $i+=2){
$result.=chr(decoval($str[$i])*16+decoval($str[$i+1]));
}
$result=Unescape($result);
return $result;
}
function urlen($str){

$str=str_replace("//","/",$str);
//$str=base64_encode($str);
return enco($str);
}
function trans($str){

$result=$str;
if(substr($str,-2,2)==".."){
$result="";
$oo=explode("/",$str);
for($i=0 ; $i<count($oo)-3 ; $i++){
$result.="$oo[$i]/";
}
$result.=$oo[count($oo)-3];
}
if(trim($result)==""){
$result=".";
if(substr($str,0,1)=="/")
$result="/";
}
$result=str_replace("..","Error",$result);
return $result;
}
function urlde($str,$must){
$str=deco($str);
//$str=base64_decode($str);
//$str=str_replace("..","Error",$str);
$str=str_replace("//","/",$str);
$str=trans($str);
if($must){
if(strstr($str,$must)==false)
$str=$must;
}
return $str;
}
function topass($file,$file2){
if($file==$file2) return "error";
$fp=fopen($file,"r");
$fp2=fopen($file2,"w");
if(!$fp)return;
if(!$fp2) return;
fwrite($fp2,"grecuspassbase64enco",20);
while(!feof($fp)){
$data=fread($fp,1000);
$data=base64_encode($data);
$num=strlen($data)."          ";
fwrite($fp2,$num,10);
fwrite($fp2,$data,strlen($data));
}
fclose($fp);
fclose($fp2);
echo $file."]".$file2;
unlink($file);
}

function unpass($file){
$ispass=0;
$fp=fopen($file,"r");
if(!$fp)
return;
$data=fread($fp,20);
if($data=="grecuspassbase64enco"){
$ispass=1;
$data="";
}
else{
}
while(!feof($fp)){
if($ispass==1){
$num=fread($fp,10);
if($num==0)break;
$datatemp=fread($fp,$num);
$datatemp=base64_decode($datatemp);
}
else{
$datatemp=fread($fp,10000);
}
$data.=$datatemp;
}
fclose($fp);
return $data;
}

function unpassfile($file,$file2){
$ispass=0;
$fp=fopen($file,"r");
$fp2=fopen($file2,"w");
if(!$fp)
return;
$data=fread($fp,20);
if($data=="grecuspassbase64enco")
$ispass=1;
else{
echo $data;
}
while(!feof($fp)){
if($ispass==1){
$num=fread($fp,10);
if($num==0)break;
$data=fread($fp,$num);
$data=base64_decode($data);
}
else{
$data=fread($fp,10000);
}
fwrite($fp2,$data,strlen($data));
}
fclose($fp);fclose($fp2);
}

function unpassecho($file){
$ispass=0;
$fp=fopen($file,"r");
if(!$fp)
return;
$data=fread($fp,20);
if($data=="grecuspassbase64enco")
$ispass=1;
else{
echo $data;
}
while(!feof($fp)){
if($ispass==1){
$num=fread($fp,10);
if($num==0)break;
$data=fread($fp,$num);
$data=base64_decode($data);
}
else{
$data=fread($fp,10000);
}
echo $data;
}
fclose($fp);
}

function formatbyte($bytes, $precision = 2) { 
    $units = array('B', 'KB', 'MB', 'GB', 'TB'); 
   
    $bytes = max($bytes, 0); 
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
    $pow = min($pow, count($units) - 1); 
   
    $bytes /= pow(1024, $pow); 
   
    return round($bytes, $precision) . ' ' . $units[$pow]; 
} 

function fsize($file) {
if(is_file($file)!=1) return 0;
clearstatcache();
        $INT = 2.0 * (PHP_INT_MAX + 1);//2147483647+2147483647+1;
        $size = filesize($file);
if($size==0) return 0;
        $fp = fopen($file, 'r');
        fseek($fp, 0, SEEK_END);
        if (ftell($fp)==0) $size += $INT;
        fclose($fp);
        if ($size<0) $size += $INT;
        return $size;


  // filesize will only return the lower 32 bits of
  // the file's size! Make it unsigned.
  $fmod = filesize($file);
  if ($fmod < 0) $fmod += 2.0 * (PHP_INT_MAX + 1);

  // find the upper 32 bits
  $i = 0;

  $myfile = fopen($file, "r");

  // feof has undefined behaviour for big files.
  // after we hit the eof with fseek,
  // fread may not be able to detect the eof,
  // but it also can't read bytes, so use it as an
  // indicator.
  while (strlen(fread($myfile, 1)) === 1) {
    fseek($myfile, PHP_INT_MAX, SEEK_CUR);
    $i++;
  }

  fclose($myfile);

  // $i is a multiplier for PHP_INT_MAX byte blocks.
  // return to the last multiple of 4, as filesize has modulo of 4 GB (lower 32 bits)
  if ($i % 2 == 1) $i--;
  
  // add the lower 32 bit to our PHP_INT_MAX multiplier
  return ((float)($i) * (PHP_INT_MAX + 1)) + $fmod;
}
function fseek64(&$fh, $offset)
{
    fseek($fh, 0, SEEK_SET);

    if ($offset <= PHP_INT_MAX)
    {
        return fseek($fh, $offset, SEEK_SET);
    }

    $t_offset   = PHP_INT_MAX;
    $offset     = $offset - $t_offset;

    while (fseek($fh, $t_offset, SEEK_CUR) === 0)
    {
        if ($offset > PHP_INT_MAX)
        {
            $t_offset   = PHP_INT_MAX;
            $offset     = $offset - $t_offset;
        }
        else if ($offset > 0)
        {
            $t_offset   = $offset;
            $offset     = 0;
        }
        else
        {
            return 0;
        }
    }

    return -1;
}

?>
