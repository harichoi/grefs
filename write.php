<?
include "head.php";
$ram=$HTTP_POST_VARS[ram];
$gra=$HTTP_POST_VARS[gra];
$ss=$HTTP_POST_VARS[ss];
$user=$HTTP_POST_VARS[user];
$comp=$HTTP_POST_VARS[comp];
$model=$HTTP_POST_VARS[model];
$doip=$HTTP_POST_VARS[doip];
$mac=$HTTP_POST_VARS[mac];
$serial=$HTTP_POST_VARS[serial];
$con=mysql_connect("localhost","root","apmsetup");
mysql_select_db("computer",$con);

if(mysql_error())
{
echo "error1";
exit;
}
if($HTTP_SESSION_VARS[id]!="grecus") {
echo "Permission denied";
exit;
}
if($ttt=mysql_fetch_array(mysql_query("select * from coms where ip=$HTTP_POST_VARS[ips]",$con)))
{
if(!isblank($ram))
mysql_query("update coms set ram=\"$ram\" where ip=$HTTP_POST_VARS[ips] limit 1",$con);
if(!isblank($gra))
mysql_query("update coms set gra=\"$gra\" where ip=$HTTP_POST_VARS[ips] limit 1",$con);
if(!isblank($ss))
mysql_query("update coms set ss=\"$ss\" where ip=$HTTP_POST_VARS[ips] limit 1",$con);
if(!isblank($user))
mysql_query("update coms set user=\"$user\" where ip=$HTTP_POST_VARS[ips] limit 1",$con);
if(!isblank($etc))
mysql_query("update coms set etc=\"$etc\" where ip=$HTTP_POST_VARS[ips] limit 1",$con);
if(!isblank($comp))
mysql_query("update coms set comp=\"$comp\" where ip=$HTTP_POST_VARS[ips] limit 1",$con);
if(!isblank($model))
mysql_query("update coms set model=\"$model\" where ip=$HTTP_POST_VARS[ips] limit 1",$con);
if(!isblank($doip))
mysql_query("update coms set doip=\"$doip\" where ip=$HTTP_POST_VARS[ips] limit 1",$con);
if(!isblank($mac))
mysql_query("update coms set mac=\"$mac\" where ip=$HTTP_POST_VARS[ips] limit 1",$con);
if(!isblank($serial))
mysql_query("update coms set serial=\"$serial\" where ip=$HTTP_POST_VARS[ips] limit 1",$con);

if(mysql_error())
{
echo "error4";
exit;
}
}
else
{
mysql_query("insert into coms(ip,ram,gra,ss,user,etc,comp,model,doip,mac,serial) values (\"$HTTP_POST_VARS[ips]\",\"$ram\",\"$graphic\",\"$ss\",\"$user\",\"$etc\",\"$comp\",\"$model\",\"$doip\",\"$mac\",\"$serial\")",$con);
}
if(mysql_error())
{
echo "error2";
exit;
}

mysql_close($con);
logwrite("$HTTP_POST_VARS[ips]$ram$graphic$user$etc$ss",100);
?>
<body>
<script>
this.location.replace("index.php");
</script>
</body>