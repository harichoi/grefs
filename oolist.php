﻿<? 
//$showaddr, $isinfs, $addr[0], $addr[1], $perm[write], $perm[excute], $perm[read]
//$dire, $mode
?>
<?

$fp=@fopen("accesslog.txt","a");
if($fp){
$Y=@date("Y",@time());
$m=@date("m",@time());
$d=@date("d",@time());
$H=@date("H",@time());
$i=@date("i",@time());
@fwrite($fp,"id: ".$_SESSION[id]." IP: ".$_SERVER[REMOTE_ADDR]." dir: ".$_SESSION[dir]." ".$Y."-".$m."-".$d." ".$H.":".$i."\r\n");
@fclose($fp);
}
if($_SESSION[error]){
echo "<b>ERROR : ".$_SESSION[error]."</b><br>";
$fp=fopen("errorlog.txt","a");
fwrite($fp,$_SESSION[error],strlen($_SESSION[error]));
fclose($fp);
$_SESSION[error]="";
}
?>


<script src="php.js"></script>
<script>
var pathinfo=new Array();
var sel=new Array();
function chg(val){
wri.tx1.value=val;
}
function selectall(){
var us;
var i;
for(i=0 ; ; i++)
{
us=document.getElementById("blk"+i);
if(!us)
break;
flag=revsel(pathinfo[i]);
if(flag==-1)
us.style.background="#cccccc";
else
us.style.background="";
}
}
function addsel(path){
sel.push(path);
}
function delsel(path){
var i;
var flag=-1;
for(i=0 ; i<sel.length ; i++)
if(sel[i]==path){
flag=i;
}
if(flag>=0){
for(i=flag ; i<sel.length-1 ; i++)
sel[i]=sel[i+1];
sel.pop();
}
return flag;
}
function revsel(path){
var i;
var flag=-1;
for(i=0 ; i<sel.length ; i++)
if(sel[i]==path){
flag=i;
}
if(flag>=0){
for(i=flag ; i<sel.length-1 ; i++)
sel[i]=sel[i+1];
sel.pop();
}
else{
sel.push(path);
}
return flag;
}

function clk(objname,path){
var flag=revsel(path);
var obj=document.getElementById(objname);

if(flag==-1)
obj.style.backgroundColor="#cccccc";
else
obj.style.backgroundColor="";
}
function go(){
if(sel[0]){
var uu=5;
var flag=substr(sel[0],-6,6);
if(flag=="676673") uu=9;
dblclk(0,sel[0],0,0,0,uu);
}
}
function dblclk(objname,path,filename,realfilename,hjj,type){
if(type==5)
document.location="oo.php?what=move&dir="+path;
else if(type==9)
document.location="oo.php?what=move&dir="+path+"2a";
else{
if(hjj=="mp3" || hjj=="avi" || hjj=="wma" || hjj=="wmv" || hjj=="wav" || hjj=="mp4" || hjj=="flv" || hjj=="pdf")
document.location="view.php?file="+path;
else if(hjj=="html" || hjj=="ght")
document.location="htmledit.php?file="+path;
else if(hjj=="bin")
document.location="binary.php?file="+path;
else if(hjj=="gdb")
document.location="db.php?file="+path;
else if(hjj=="php" || hjj=="js" || hjj=="htaccess" || hjj=="ava" || hjj=="txt")
document.location="edit.php?file="+path;
else
document.location="download.php?file="+path;
}
}

function frmsubmit1(go,meth,name1,value1){
var frm=document.createElement("form");
var inpt1=document.createElement("input");
frm.setAttribute("action",go);
frm.setAttribute("method",meth);
inpt1.setAttribute("name",name1);
inpt1.setAttribute("value",value1);
document.body.appendChild(frm);
frm.appendChild(inpt1);
frm.submit();
}

function frmsubmit2(go,meth,name1,value1,name2,value2){
var frm=document.createElement("form");
var inpt1=document.createElement("input");
var inpt2=document.createElement("input");
frm.setAttribute("action",go);
frm.setAttribute("method",meth);
inpt1.setAttribute("name",name1);
inpt1.setAttribute("value",value1);
inpt2.setAttribute("name",name2);
inpt2.setAttribute("value",value2);
document.body.appendChild(frm);
frm.appendChild(inpt1);
frm.appendChild(inpt2);
frm.submit();
}

function frmsubmit3(go,meth,name1,value1,name2,value2,name3,value3){
var frm=document.createElement("form");
var inpt1=document.createElement("input");
var inpt2=document.createElement("input");
var inpt3=document.createElement("input");
frm.setAttribute("action",go);
frm.setAttribute("method",meth);
inpt1.setAttribute("name",name1);
inpt1.setAttribute("value",value1);
inpt2.setAttribute("name",name2);
inpt2.setAttribute("value",value2);
inpt3.setAttribute("name",name3);
inpt3.setAttribute("value",value3);
document.body.appendChild(frm);
frm.appendChild(inpt1);
frm.appendChild(inpt2);
frm.appendChild(inpt3);
frm.submit();
}
function sending(){
if(sel[0]){
frmsubmit1("downsel.php","post","oo",sel.join(","));
}
}

function deleteing(){
if(sel[0] && confirm("Really delete?")){
frmsubmit2("oo.php","post","fol",sel.join(","),"what","del");
}
}
function tocutphp(){
if(sel[0]){
frmsubmit3("oo.php","post","fol",sel.join(","),"what","copy","mode","2");
}
}
function tocopyphp(){
if(sel[0]){
frmsubmit3("oo.php","post","fol",sel.join(","),"what","copy","mode","1");
}
}
function topastephp(){
frmsubmit1("oo.php","post","what","paste");
}
function topassphp(){
if(sel[0]){
frmsubmit2("oo.php","post","fol",sel.join(","),"what","pass");
}
}
function tosolvephp(){
if(sel[0]){
frmsubmit2("oo.php","post","fol",sel.join(","),"what","solve");
}
}

function compressphp(){
if(sel[0]){
frmsubmit2("oo.php","post","fol",sel.join(","),"what","compress");
}
}
function extractphp(){
if(sel[0]){
frmsubmit2("oo.php","post","fol",sel.join(","),"what","extract");
}
}
function fsconvphp(){
if(sel[0]){
frmsubmit2("oo.php","post","fol",sel.join(","),"what","fsconv");
}
}

function fsreconvphp(){
if(sel[0]){
frmsubmit2("oo.php","post","fol",sel.join(","),"what","fsreconv");
}
}
function chmodphp(){
if(sel[0]){
frmsubmit3("oo.php","post","fol",sel.join(","),"what","chmod","chmod",wri.tx1.value);
}
}

function touchphp(){
frmsubmit2("oo.php","post","what","touch","file",wri.tx1.value);
}

function mkdirphp(){
frmsubmit2("oo.php","post","file",wri.tx1.value,"what","mkdir");
}

function renamephp(){
if(sel[0]){
frmsubmit3("oo.php","post","file1",sel[0],"what","rename","file2",wri.tx1.value);
}
}
</script>

<?
echo "<h1>$showaddr</h1><br><hr><br>";
if($perm[write]==1){
echo"<form action=\"oo.php\" name=\"write\" method=\"post\" enctype=multipart/form-data><input type=hidden name=what value=\"upload\"></input><input type=file name=file1 size=50  maxlength=255 class=input></input><input type=submit></input></form><hr>";
}
?>
<a href='oo.php?what=move&dir=<? echo urlen("pds#$dire/.."); ?>'><img src=back.jpg></a>
<a href=javascript:go()><img src=go.jpg></a>
<a href=javascript:sending()><img src=down.jpg></a> 
<a href=javascript:selectall()><img src=selall.jpg></a>  
<a href=javascript:deleteing()><img src=del.jpg></a>  
<a href=javascript:topassphp()><img src=topass.jpg></a>  
<a href=javascript:tosolvephp()><img src=tosolve.jpg></a>
<a href=javascript:compressphp()>Compress</a>  
<a href=javascript:extractphp()>Extract</a>  
<a href=javascript:fsconvphp()>Fsconv</a>
<a href=javascript:fsreconvphp()>Fsreconv</a>
<a href=javascript:tocutphp()><img src=tocut.jpg></a>    
<a href=javascript:tocopyphp()><img src=tocopy.jpg></a>  
<a href=javascript:topastephp()><img src=topaste.jpg></a>
<a href=imgmode.php><img src=imgmode.jpg></a>    
<hr>
<?
echo "<b>FREE</b>: ".formatbyte(disk_free_space("."))." <b>/</b> ".(formatbyte(disk_total_space(".")))."<br><hr><br>";
?>
<fieldset id=filelist style="border:0px">
<?
$filelist=file_list($dire,$_SESSION[key]);
$blks=array();

$imgmode=0;
if($_SESSION[imgmode]==1)$imgmode=1;

$pagelen=200;
for($i=$_GET[page]*$pagelen ; $i<$_GET[page]*$pagelen+$pagelen ; $i++){

if($i>=$filelist['len'])break;
$uuu=explode(".",$filelist[$i][1]);
$hjj=strtolower($uuu[count($uuu)-1]);
$blks[$i]=urlen($dire."/".$filelist[$i][1]);
if($filelist[$i][2]==5 || $filelist[$i][2]==9)
echo "<fieldset id=blk$i class=files onclick=javascript:clk('blk$i','".urlen($dire."/".$filelist[$i][1])."') ondblclick=javascript:dblclk('blk$i','".urlen("pds#".$dire."/".$filelist[$i][1])."','".urlen($filelist[$i][1])."',\"none\",'".""."',".$filelist[$i][2].") >";
else
echo "<fieldset id=blk$i class=files onclick=javascript:clk('blk$i','".urlen($dire."/".$filelist[$i][1])."') ondblclick=javascript:dblclk('blk$i','".urlen($dire."/".$filelist[$i][1])."','".urlen($filelist[$i][1])."',\"none\",'".$hjj."',".$filelist[$i][2].") >";
echo "<table><tr><td width=50 height=50>";
unset($hjj);
if($filelist[$i][2]==5){
echo "<img src='folder.jpg' width=50 height=50>";
}
else if($filelist[$i][2]==9){
echo "<img src='gfs.jpg' width=50 height=50>";
}
else{

$uuu=explode(".",$filelist[$i][1]);
$hjj=strtolower($uuu[count($uuu)-1]);
if($hjj=="exe")
echo "<img src='exe.jpg' alt='$file' width=50 height=50>";
else if($hjj=="php")
echo "<img src='php.jpg' alt='$file' width=50 height=50>";
else if($hjj=="zip")
echo "<img src='zip.jpg' alt='$file' width=50 height=50>";
else if($hjj=="tar")
echo "<img src='tar.jpg' alt='$file' width=50 height=50>";
else if($hjj=="jpg" || $hjj=="bmp" || $hjj=="png" || $hjj=="gif"){
echo "<img src='download.php?file=".urlen($dire."/".$filelist[$i][1])."' alt='".$filelist[$i][1]."' ";
if($imgmode==0)
echo "width=50 height=50";
echo ">";
}
else
echo "<img src='file.bmp' alt='".$filelist[$i][1]."' width=50 height=50>";
}
echo "</td><td>";
if($filelist[$i][6]==1)
echo "<font size=2 color=red>";
else if($filelist[$i][6]==2)
echo "<font size=2 color=blue>";
else
echo "<font size=2 color=black>";
echo trim(substr($filelist[$i][1],0,128));
echo "</font><br><font color=gray>";
echo formatbyte($filelist[$i][3])."</font><br><font color=gray>".$filelist[$i][4]."</font><br><font color=gray>".$filelist[$i][5];
echo "</font></td></tr></table></fieldset>";
}

?>
</fieldset>
<center>
<?
if($filelist['len']>=$pagelen){
for($i=0 ; $i<$filelist['len']/$pagelen ; $i++)
echo "<a href=list.php?page=".$i.">".$i."</a> ";
}
?>
</center>
<br><hr>
<?
if($perm[write]==1){
echo"<form name=wri>
<table border=0 height=35><tr><td><input type=text name=tx1 size=50  maxlength=255 class=input></input></td><td><a href=javascript:mkdirphp()><img src=plus.jpg></a>
<a href=javascript:renamephp()><img src=chg.jpg></a> <a href=javascript:mkdbphp()><img src=adb.jpg></a> <a href=javascript:resizephp()><img src=resize.jpg></a> <a href=javascript:chmodphp()><img src=chmod.jpg></a> <a href=javascript:touchphp()><img src=touch.jpg></a></td></tr></table>
</form>";
}
?>
<script>
<?
for($i=0 ; $i<count($blks) ; $i++)
echo "pathinfo[$i]='$blks[$i]';";
?>
</script>
