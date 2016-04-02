<?
include "head.php";
?>
<p><p><p>solving..<p> 
<?
$fol=explode(",",$_POST[fol]);
for($i=0 ; $i<count($fol) ; $i++){
tosolving(urlde("$fol[$i]",$_SESSION[mustdir]));
}
echo "<script>this.location.replace('list.php');</script>";
?>