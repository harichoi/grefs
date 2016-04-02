<?
include "head.php";
if($_SESSION[imgmode]==1)
$_SESSION[imgmode]=0;
else
$_SESSION[imgmode]=1;
?>
<script>
this.location.replace("oo.php"); 
</script>
