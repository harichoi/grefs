<?
include "head.php";
?>
<p><p><p>copying..<p> 
<?
$_SESSION[copy]=$_POST[fol];
$_SESSION[copymode]=$_POST[mode];
echo "<script>this.location.replace('list.php');</script>";
?>