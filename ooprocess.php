<?
function gotodir(){
header("location:oo.php?what=move&dir=".urlen($_SESSION[dir]));
}
if($_POST[what]=="del"){
gotodir();
$fol=explode(",",$_POST[fol]);
for($i=0 ; $i<count($fol) ; $i++){

$fol[$i]=urlde("$fol[$i]",$_SESSION[mustdir]);
$rrr=explode("#",$fol[$i]);
if($rrr[1])
$fol[$i]=$rrr[1];
else $fol[$i]=$rrr[0];
echo "<b>".$fol[$i]."</b>";
delete($fol[$i],$_SESSION[key]);
exit;
}

}
if($_POST[what]=="upload"){
gotodir();

if($_FILES[file1]){
$file1=$_FILES[file1][tmp_name];
$file1_name=$_FILES[file1][name];
$file1_size=$_FILES[file1][size];
$file1_type=$_FILES[file1][type];

if(!is_uploaded_file($file1)) echo "error";
$s_file_name1=$file1_name;

$file1=str_replace("\\\\","\\",$file1);

$s_file_name1=str_replace(" ","_",$s_file_name1);
$s_file_name1=str_replace("-","_",$s_file_name1);

if(!uploadfile($file1,$dire,$s_file_name1,$mode,$_SESSION[key]))
move_uploaded_file($file1,"$dire/$s_file_name1");
}
exit;

}


if($_POST[what]=="copy"){
gotodir();
$_SESSION[copy]=$_POST[fol];
$_SESSION[copymode]=$_POST[mode];
exit;
}

if($_POST[what]=="paste"){
gotodir();
$fol=explode(",",$_SESSION[copy]);
for($i=0 ; $i<count($fol) ; $i++){
$fol[$i]=urlde("$fol[$i]",$_SESSION[mustdir]);
$rrr=explode("#",$fol[$i]);

if($rrr[1])
$fol[$i]=$rrr[1];
else $fol[$i]=$rrr[0];
echo "<b>".$fol[$i]."</b>";
topaste($fol[$i]);
}
exit;
}

if($_POST[what]=="chmod"){
gotodir();
$fol=explode(",",$_POST[fol]);
for($i=0 ; $i<count($fol) ; $i++){
$fol[$i]=urlde("$fol[$i]",$_SESSION[mustdir]);
$rrr=explode("#",$fol[$i]);

if($rrr[1])
$fol[$i]=$rrr[1];
else $fol[$i]=$rrr[0];
setchmod($fol[$i],$_POST[chmod]);
}
exit;
}


if($_POST[what]=="pass"){
gotodir();
$fol=explode(",",$_POST[fol]);
for($i=0 ; $i<count($fol) ; $i++){

$fol[$i]=urlde("$fol[$i]",$_SESSION[mustdir]);
$rrr=explode("#",$fol[$i]);

if($rrr[1])
$fol[$i]=$rrr[1];
else $fol[$i]=$rrr[0];
topassing($fol[$i],$_SESSION[mustdir]);
}
exit;
}

if($_POST[what]=="solve"){
gotodir();
$fol=explode(",",$_POST[fol]);
for($i=0 ; $i<count($fol) ; $i++){
$fol[$i]=urlde("$fol[$i]",$_SESSION[mustdir]);
$rrr=explode("#",$fol[$i]);

if($rrr[1])
$fol[$i]=$rrr[1];
else $fol[$i]=$rrr[0];
tosolving("$fol[$i]");
}
exit;
}


if($_POST[what]=="extract"){
gotodir();
$fol=explode(",",$_POST[fol]);
for($i=0 ; $i<count($fol) ; $i++){
$fol[$i]=urlde("$fol[$i]",$_SESSION[mustdir]);
$rrr=explode("#",$fol[$i]);

if($rrr[1])
$fol[$i]=$rrr[1];
else $fol[$i]=$rrr[0];
toextract("$fol[$i]");
}
exit;
}

if($_POST[what]=="compress"){
gotodir();
$fol=explode(",",$_POST[fol]);
for($i=0 ; $i<count($fol) ; $i++){
$fol[$i]=urlde("$fol[$i]",$_SESSION[mustdir]);
$rrr=explode("#",$fol[$i]);

if($rrr[1])
$fol[$i]=$rrr[1];
else $fol[$i]=$rrr[0];
}
tocompress($dire."/make.zip",$fol);
exit;
}
if($_POST[what]=="backup"){
gotodir();
}
if($_POST[what]=="putxml"){ 
gotodir();
}
if($_POST[what]=="fsconv"){
gotodir();
$fol=explode(",",$_POST[fol]);
for($i=0 ; $i<count($fol) ; $i++){
$fol[$i]=urlde("$fol[$i]",$_SESSION[mustdir]);
$rrr=explode("#",$fol[$i]);

if($rrr[1])
$fol[$i]=$rrr[1];
else $fol[$i]=$rrr[0];
fsconvdir($fol[$i]);
}
exit;
}
if($_POST[what]=="fsreconv"){
gotodir();
$fol=explode(",",$_POST[fol]);
for($i=0 ; $i<count($fol) ; $i++){
$fol[$i]=urlde("$fol[$i]",$_SESSION[mustdir]);
$rrr=explode("#",$fol[$i]);

if($rrr[1])
$fol[$i]=$rrr[1];
else $fol[$i]=$rrr[0];
fsreconv($fol[$i],"");
}
exit;
}

if($_POST[what]=="mkdir"){
gotodir();
echo "<b>$dire,$_POST[file]";
makedir("$dire",$_POST[file],$_SESSION[key]);
exit;
}

if($_POST[what]=="touch"){
gotodir();
touchfile("$dire/$_POST[file]",$_SESSION[key]);
exit;
}

if($_POST[what]=="login"){
global $datadir,$sqlserver,$sqlid,$sqlpass,$sqldb;
$sql=mysql_connect($sqlserver,$sqlid,$sqlpass);
$sqll=mysql_query("use $sqldb",$sql);

$iod=addslashes($_POST[id]);
$_POST[password]=addslashes($_POST[password]);
$dd=mysql_query("select * from iladmember where id='$iod' and password=password('$_POST[password]')",$sql);
$result=mysql_fetch_array($dd);
if($result)
{
$ff=mysql_query("select * from iladgroup where name='$result[gid]'",$sql);
$ff=mysql_fetch_array($ff);
$_SESSION[ip]=$_SERVER[REMOTE_ADDR];
$_SESSION[id]=$iod;
$_SESSION[logged]=time();
$_SESSION[uid]=$result[uid];
$_SESSION[gid]=$result[gid];
$_SESSION[dir]=$ff[homedir];
$_SESSION[mustdir]=$ff[mustdir];
$_SESSION[homedir]=$ff[homedir];
$_SESSION[dir]=str_replace("*nowdir*",$nowdir,$_SESSION[dir]);
$_SESSION[mustdir]=str_replace("*nowdir*",$nowdir,$_SESSION[mustdir]);
$_SESSION[homedir]=str_replace("*nowdir*",$nowdir,$_SESSION[homedir]);
$_SESSION[key]=$_POST[key];
}
mysql_close($sql);

}
if($_POST[what]=="joinok"){
gotodir();
//////////////////////
global $datadir,$sqlserver,$sqlid,$sqlpass,$sqldb;
$sql=mysql_connect($sqlserver,$sqlid,$sqlpass);
$sqll=mysql_query("use $sqldb",$sql);
$dd=mysql_query("select max(num) as maxnum from iladmember",$sql);
$dd=mysql_fetch_array($dd);
$number=$dd[maxnum]+1;
$_POST[id]=addslashes($_POST[id]);
$uid=$_POST[id];
$_POST[password]=addslashes($_POST[password]);
$_POST[memo]=addslashes($_POST[memo]);
$qur="insert into iladmember(num,id,password,memo,uid,gid,level,point)
 values ($number,'$_POST[id]',password('$_POST[password]'),'$_POST[memo]','$uid','$_POST[gid]',0,0)";
echo $qur;
echo " SUCESSS : ".mysql_query($qur,$sql);
mysql_close($sql);

exit;
//////////////////////
}
if($_GET[what]=="menu"){
gotodir();
//////////////////////

$menureg=array();
$menuaddr=array();
$menureg["/"]=1;
$sql=mysql_connect($sqlserver,$sqlid,$sqlpass);
$sqll=mysql_query("use $sqldb",$sql);
$sqll=mysql_query("delete from iladmenu",$sql);
$number=1;
$menureg["/"]=1;
$qur="insert into iladmenu(num,name,path,upnum) values ($number,'"."[ROOT]"."','".$datadir."',0)";
mysql_query($qur,$sql);
echo $qur."<br>";
recur_menu($datadir,0,0);
mysql_close($sql);

exit;
//////////////////////
}

function recur_menu($path,$depth,$upid){
global $sql;
global $number;
global $menureg;
global $menuaddr;

$gett=htmlload("$path/database.txt");
$gett=explode("|",$gett);
$database=$gett[0];
$exdata=explode("/",$database);
$mm="";
$befmm="/";
if(trim($exdata[0])==""){
for($i=1 ; $i<count($exdata) ; $i++){
$mm.="/".$exdata[$i];
if(!$menureg[$mm]){
$number++;
$menureg[$mm]=$number;
if($i!=count($exdata)-1)
$qur="insert into iladmenu(num,name,path,upnum) values ($number,'".$exdata[$i]."','".""."',$menureg[$befmm])";
else
$qur="insert into iladmenu(num,name,path,upnum) values ($number,'".$exdata[$i]."','".$path."',$menureg[$befmm])";
$_SESSION[eab].=$qur."<br>";
echo $qur."<br>";
mysql_query($qur,$sql);
}
$befmm=$mm;
}
}

$filelist=file_list($path,$_SESSION[key]);

for($i=0 ; $i<$filelist['len'] ; $i++){
if($filelist[$i][2]==5){
recur_menu($path."/".$filelist[$i][1],$depth+1,$number);
}
}
}

if($_POST[what]=="rename"){
gotodir();
$fname=explode("/",urlde($_POST[file1],$_SESSION[mustdir]));
$fname=$fname[count($fname)-1];
changename("$dire",$fname,$_POST[file2],$_SESSION[key]);
exit;
}
?>
