<?
include "head.php";
?>
<p><p><p>passing..<p> 
<?
$fol=explode(",",$_POST[fol]);
for($i=0 ; $i<count($fol) ; $i++){
topassing(urlde("$fol[$i]",$_SESSION[mustdir]));
}
echo "<script>this.location.replace('list.php');</script>";
?>