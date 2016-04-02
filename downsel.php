<?

include "head.php";


$fls=explode(",",$_POST[oo]);
if(count($fls)==1 && issdir(urlde($fls[0],$_SESSION[mustdir]))==0){
echo "<script>this.location.replace('download.php?file=".$fls[0]."');</script>";
}
else{
header("Content-Type: Application/x-tar");
for($i=0 ; $i<count($fls) ; $i++){
$fle=urlde("$fls[$i]",$_SESSION[mustdir]);
$fle2=explode("/",$fle);
downsel($fle,$fle2[count($fle2)-1],$_SESSION[key]);
}
echo pack("a512","\x00");
}
?>