<?
include "head.php";

$mode=$HTTP_POST_VARS[ispass];
echo $mode;
$default_dir=$HTTP_POST_VARS[default_dir];
if(!is_dir("data")) { 
	@mkdir("data",0777);
	@chmod("data",0706);
}
if($HTTP_POST_FILES[file1]) 
{
$id=1;
		$file1 = $HTTP_POST_FILES[file1][tmp_name];
		$file1_name = $HTTP_POST_FILES[file1][name];
		$file1_size = $HTTP_POST_FILES[file1][size];
		$file1_type = $HTTP_POST_FILES[file1][type];		
		if(!is_uploaded_file($file1)) echo "error";
 	$s_file_name1=$file1_name;		
	$file1=eregi_replace("\\\\","\\",$file1);

			$s_file_name1=str_replace(" ","_",$s_file_name1);
			$s_file_name1=str_replace("-","_",$s_file_name1);

if(!uploadfile($file1,$default_dir,$s_file_name1,$mode,$_SESSION[key]))
move_uploaded_file($file1,"$default_dir/$s_file_name1");
echo "$default_dir/$s_file_name1";

}


?>

<script>
this.location.replace("list.php"); 
</script>
