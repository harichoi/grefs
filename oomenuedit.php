<form action=oo.php method=post>
<input type=hidden name=what value=menuedit></input>
<input type=hidden name=what2 value=menuupdate></input>
num : <input type=text name=num></input>
name : <input type=text name=name></input>
path : <input type=text name=path></input>
upnum : <input type=text name=upnum></input>
upnum : <input type=submit></input>
</form>
<?
$sql=mysql_connect($sqlserver,$sqlid,$sqlpass);
$sqll=mysql_query("use $sqldb",$sql);
if($_POST[what2]=="menuupdate"){
$sqll=mysql_query("select * from iladmenu where num=$_POST[num]");
if(mysql_fetch_array($sqll)){
$qur="update iladmenu set num=$_POST[num] ";
if(trim($_POST[name])!="")
$qur.=", name='$_POST[name]' ";
if(trim($_POST[path])!="")
$qur.=", path='$_POST[path]' ";
if(trim($_POST[upnum])!="")
$qur.=", upnum='$_POST[upnum]' ";
$qur.="where num=$_POST[num]";
echo "UPDATE : ".mysql_query($qur,$sql)."<br>";
}
else{
echo "INSERT : ".mysql_query("insert into iladmenu(num,name,path,upnum) values ($_POST[num],'$_POST[name]','$_POST[path]',$_POST[upnum])",$sql)."<br>";
}
$uuu=explode("#",$_POST[path]);
makedir("$datadir/book",$uuu[1]);

}
$sqll=mysql_query("select * from iladmenu",$sql);
echo "<table><tr><th>num</th><th>name</th><th>path</th><th>upnum</th></tr>";
while($result=mysql_fetch_array($sqll)){
echo "<tr><td>$result[num]</td><td>$result[name]</td><td>$result[path]</td><td>$result[upnum]</td></tr>";
}
echo "</table>";

mysql_close($sql);
?>
