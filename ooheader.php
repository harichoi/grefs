﻿﻿<?
$depthmenu=array();
$depthmenu2=array();

$rootdepth=0;
$rootdir=$_SESSION[homedir];


$sql=mysql_connect($sqlserver,$sqlid,$sqlpass);
$sqll=mysql_query("use $sqldb",$sql);
$sqll=mysql_query("select * from iladmenu where num=1",$sql);
$result=mysql_fetch_array($sqll);
echo "<div id=allmenu>";
//echo "<ul class=firstmenu><li><div width=100%>[ROOT]</div>";
recur("",$rootdepth+1,$result[num]);
//echo "</li></ul>";
echo "</div>";

mysql_close($sql);

function recur($path,$depth,$upid){
global $sql, $rootdir;

$show=0;
if($rootdir==substr($path,0,strlen($rootdir))) $show=1;

$sqll=mysql_query("select * from iladmenu where upnum=$upid");
if($show)
echo "<ul class=menus>";
while($result=@mysql_fetch_array($sqll)){
if($show){
if(trim($result[path])!=""){
$ggg=explode("#",$result[path]);
$new=0;
echo "<li><a href=oo.php?what=move&dir=".urlen($result[path]).">  <div width=100%>".$result[name]."";

//echo "<br>".strtotime($result[lasttime]).",".time();

if(strtotime($result[lasttime])>time()-400000)
$new=4;
if(strtotime($result[lasttime])>time()-150000)
$new=3;
if(strtotime($result[lasttime])>time()-72000)
$new=2;
if(strtotime($result[lasttime])>time()-36000)
$new=1;
if($new==1) echo "<font color=#ff0000>[N]</font>";
if($new==2) echo "<font color=#dd0000>[N]</font>";
if($new==3) echo "<font color=#880000>[N]</font>";
if($new==4) echo "<font color=#440000>[N]</font>";
echo "</div></a>\r\n";
}
else echo "<li><div width=100%>".$result[name]."</div>\r\n";

}
recur($path."/".$result[name],$depth+1,$result[num]);
if($show)
echo "</li>";
}
if($show)
echo "</ul>";
}
?>
