<?
if(!is_dir("ses")) {
			mkdir("ses", 0777);
			chmod("ses", 0777);
		}

session_save_path("ses");
session_cache_limiter('nocache, must_revalidate');
session_set_cookie_params(0,"/");
@session_start();
include "func.php";
$_SESSION[file]=$_GET[file];
$ii=urlde($_GET[file],$_SESSION[mustdir]);
$hjj=substr($ii,-3,3);
?>
<body bgcolor=black>
<table border=1 bordercolor=blue width=100% height=100%><tr><td bgcolor=blue><font color=white>
Address : <? echo urlde($_GET[file],$_SESSION[mustdir]) ?> [<a href=oo.php><font 

color=white>Back</font></a>]</font> 
</td></tr><tr><td width=100% height=100%>
<?
if($hjj=="wav" || $hjj=="pdf" || $hjj=="wma" || $hjj=="mp3"){
echo '<audio src="download.php?file='.$_GET[file].'" controls=controls>Cannot play</audio>';
}
else if($hjj=="wmv" || $hjj=="avi" || $hjj=="mp4"){
echo '<embed src="download.php?file='.$_GET[file].'" controls=controls>Cannot play</embed>';
}
 else{
echo '
<embed
  flashvars="file=down/'.$_GET[file].'/get.'.$hjj.'&autostart=true"
  allowfullscreen="true"
  allowscripaccess="always"
  id="player1"
  name="player1"
  src="player.swf"
  width="100%"
  height="100%"
/>';
}
?>
</td></tr></table>
			
