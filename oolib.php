<?
if($_GET[dir]) $nextdir=urlde($_GET[dir],$_SESSION[mustdir]);
else if($_POST[dir]) $nextdir=urlde($_POST[dir],$_SESSION[mustdir]);
else $nextdir=$_SESSION[dir];
$_SESSION[dir]=$nextdir;
$_SESSION[dir]=str_replace("*nowdir*",$nowdir,$_SESSION[dir]);
$ff=explode("#",$_SESSION[dir]);
$mode=$ff[0];
$dire=$ff[1];
if($mode=="pds"){
$addr=explode("*/",$ff[1]);
if(count($addr)==1)
$addr=explode("*",$ff[1]);
$isinfs=1;
if(count($addr)==1)
$isinfs=0;
if($isinfs==0)
$showaddr=$addr[0];
else
$showaddr=$addr[1];
$perm=array();
$perm[excute]=1;
$perm[read]=1;
$perm[write]=canwrite();
if($isinfs){
$perm[excute]=getperm(getfspath($addr[0],$addr[1]),"x");
$perm[read]=getperm(getfspath($addr[0],$addr[1]),"r");
$perm[write]=getperm(getfspath($addr[0],$addr[1]),"w");
}
}
else if($mode=="book"){
$showaddr=$dire;
$perm[excute]=1;
$perm[read]=1;
$perm[write]=1;
}
?>
