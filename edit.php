<?
include "head.php";
if($_GET[file])
$_POST[filename]=urlde($_GET[file],$_SESSION[mustdir]);
if($_POST[mode]==2)
{
htmlsave($_POST[filename],$_POST[text]);
}


?>
<script src=php.js></script>
<script src=gre.js></script>
<script>
function save(){
//wri.filename.value=(wri.filename.value);
wri.text.value=(wri.text.value);
wri.mode.value=2;
wri.submit();
}
function load(){
//wri.filename.value=(wri.filename.value);
wri.mode.value=1;
wri.submit();
}
</script>

</form>
<form action="edit.php" name="wri" method="post" enctype=multipart/form-data>
<input type=hidden value="2" name="mode"></input>
<table border=1 bordercolor=blue><tr><td bgcolor=blue><font color=white>
Address : <? echo ($_POST[filename]) ?> [<a href=oo.php><font color=white>Back</font></a>]</font> 
</td></tr><tr width><td>
<input type=text size=50 name=filename value="<?=($_POST[filename])?>"></input><input type=button onClick=javascript:load() value="load"><input type=button onClick=javascript:save() value="save">
</input>
</td>
</tr>
<td>
<textarea name="text" cols=150 rows=40 class=input>
<?
$data=htmlload($_POST[filename]);
$data=str_replace("&","&amp;",$data);
$data=str_replace("<","&lt;",$data);
$data=str_replace(">","&gt;",$data);
echo $data;
?>
