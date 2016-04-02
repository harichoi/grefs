<?
include "head.php";
?>
<p><p><p>pasteing..<p> 
<?
$fol=explode(",",$_SESSION[copy]);
for($i=0 ; $i<count($fol) ; $i++){
topaste(urlde("$fol[$i]",$_SESSION[mustdir]));
}
echo "<script>this.location.replace('list.php');</script>";
?>