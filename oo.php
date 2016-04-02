<?
error_reporting(E_ALL^E_NOTICE);
ini_set("display_errors", 1);

header("charset:utf-8");
include "pclzip.lib.php";
include "head.php";

?>
<?

$third=time()+microtime();
$third=time()+microtime()-$third;
echo $mode. " ".$showaddr;
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
?>
<meta charset=UTF-8>
<style>
*{
font-size:13px;
}

ul>li{
    margin-left: -25px;
}
pre {
white-space: pre-wrap;       /* css-3 */
white-space: -moz-pre-wrap;  /* Mozilla, since 1999 */
white-space: -pre-wrap;      /* Opera 4-6 */
white-space: -o-pre-wrap;    /* Opera 7 */
word-wrap: break-word;       /* Internet Explorer 5.5+ */
}
fieldset{
padding:0px;
border:0px;
}
.files{
float:left;
width:250px;
padding:0px;
border:0px;
}
.menu{
}
.rep{
font-size : 12px;
background:#eeeeee;
}
.repwrite{
font-size : 12px;
padding : 2px;
background : #eeeeee;
}
.bookcon{
font-size : 12px;
color : #111111;
text-decoration : none;
}
.bookfir{
font-size : 12px;
color:blue;
text-decoration:none;
font-weight:bold;
float:left;
}
.booksec{
font-size:12px;
color:black;
text-decoration:none;
float:right;
}
.bookthi{
font-size:12px;
color:red;
text-decoration:none;
float:right;
}
.bookfor{
font-size:12px;
color:gray;
text-decoration:none;
float:right;
}
.bookrep{
font-size:12px;
color:gray;
text-decoration:none;
float:right;
}
hr{
size:1px;
color:gray;
}
a, a:link, a:visited, a:active, a:hover{
color:black;
text-decoration:none;
}
@font-face{
  font-family: 'NanumGothicWeb';
  src: url('NanumGothicBold.ttf');
}
body{
background:skyblue;
background:url(dp.jpg);
font-family:NanumGothicWeb;
}
#header:hover{
height:auto;
}
#header {
filter:alpha(opacity=80); 
opacity:0.8; 
-moz-opacity:0.8;
padding : 0px;
border : 1px solid black;
border-right : 5px solid black;
border-bottom : 5px solid black;
background : white;
position : absolute;
top : 100px;
left : 0px;
width : 180px;
height : auto;
z-index : 2;
overflow-x:scroll;
}

#main {
#filter:alpha(opacity=80); 
#opacity:0.8; 
#-moz-opacity:0.8;
border : 1px solid black;
border-right : 5px solid black;
border-bottom : 5px solid black;
background : white;
position : absolute;
top : 100px;
left : 200px;
width : 800px;
height : auto;
z-index : 1;
}

#login {
filter:alpha(opacity=80); 
opacity:0.8; 
-moz-opacity:0.8;
padding : 10px;
border : 1px solid black;
border-right : 5px solid black;
border-bottom : 5px solid black;
background : white;
position : absolute;
top : 100px;
left : 1050px;
width : 150px;
height : 150px;
}

</style>

<style>
#allmenu div:hover {
background : skyblue;
}
#allmenu{
display:block;
}
#allmenu a:hover{
color:blue;
background:skyblue;
}
#allmenu li{
list-style-type:none;
}
</style>
<script>
function menuopen(){
var ee=document.getElementById("allmenu");
ee.style.visibility="visible";
var hh=document.getElementById("header");
ee.style.width="auto";
}
function menuclose(){
var ee=document.getElementById("allmenu");
//ee.style.visibility="hidden";
var hh=document.getElementById("header");
//ee.style.width="50px";

//ee.style.zIndex=0;
}
</script>

<body onload=javascript:menuclose()>
<div id=header onmouseover=javascript:menuopen() onmouseout=javascript:menuclose()>
<a href="javascript:menuopen()">메뉴 펼치기</a>
<?
$forth=time()+microtime();
include "ooheader.php";
$forth=time()+microtime()-$forth;
?>
</div>

<div id=main>
<?
$fifth=time()+microtime();
if($_GET[what]==join){
include "oojoin.php";
}
else if($_GET[what]==menuedit || $_POST[what]==menuedit ){
include "oomenuedit.php";
}
else{
if($mode=="pds")
include "oolist.php";
else if($mode=="book")
include "oobook.php";
}
$fifth=time()+microtime()-$fifth;
?>
</div>

<div id=login>
<?
$sixth=time()+microtime();
include "oologin.php";
$sixth=time()+microtime()-$sixth;
?>
</div>

<?
echo "<p><p><p><p><p><br><br> <font color=white>실행시간 : $first, $second, $third, $forth, $fifth, $sixth<br>$_SESSION[eabb] ";
?>
