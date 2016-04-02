<?
include "head.php";

$filename=$dire;
$filename=explode("/",$filename);
$filename=$filename[count($filename)-1];
$fsz=@filesize($filename);

$type=substr($filename,-3,3);

$fp=@fopen("accesslog.txt","a");

if($fp){
$Y=@date("Y",@time());
$m=@date("m",@time());
$d=@date("d",@time());
$H=@date("H",@time());
$i=@date("i",@time());
@fwrite($fp,"id: ".$_SESSION[id]." IP: ".$_SERVER[REMOTE_ADDR]." file: ".urlde("$_GET[file]",$_SESSION[mustdir])." ".$Y."-".$m."-".$d." ".$H.":".$i."\r\n");
@fclose($fp);
}

header("Cache-Control: "); 
header("Pragma: "); 
//header("Content-Type: application/octet-stream"); 
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
if($fsz!=0 && $fsz!="")
header("Content-Length: " .(string)($fsz)); 
header('Content-Disposition: attachment; filename="'.$filename.'"'); 
header("Content-Transfer-Encoding: binary\n"); 


if($type=="jpg" || $type=="gif" || $type=="bmp" || $type=="png")
header("Content-Type: Application/octet-stream");
else if($type=="wmv" || $type=="mp3" || $type=="avi" || $type=="wma" || $type=="wav" || $type=="mp4")
header("Content-Type: audio/mpeg");
else if($type=="h" || $type=="txt" || $type=="php")
header("Content-Type: text/plain");
else
header("Content-Type: Application/octet-stream");


getdownload(urlde("$_GET[file]",$_SESSION[mustdir]),$_SESSION[key]);
flush();

?>
