<?
if($_POST[what]=="bookget"){
include "head.php";
//$_GET[limit]
}
$limit=20;
if($_POST[limit]){
$limit=$_POST[limit];
}
?>
<?

if($_POST[what]!="bookget"){
echo "<h1>$showaddr</h1>
<hr>";
}
?>
<?
$database=$dire;
$showdatabase=array();
$showdatabase[0]=$dire;
?>
<?
if($_POST[what]!="bookget"){
echo "<form action=oo.php method=post enctype=multipart/form-data>";

if($perm[write]==1){
echo "<input type=hidden value=book name=what></input>";
echo "<input type=hidden value=-1 name=reply></input>";
echo "Nick : <input type=text name=nick value=$_SESSION[id]></input>";
if($_SESSION[uid]=="guest")
echo "Password : <input type=password name=pass></input>";
echo "<input type=text name=subject style='width:100%'></input><br>";
echo "<textarea name=content style='width:100%;height:250px;'></textarea>";
echo "<input type=file name=file1 style='width:100%'></input><br>";
echo "<input type=submit style='width:100%'></input>";
}
echo "</form>";
}
?>
<?
$uploadresult="";

if($_FILES[file1]){
$id=1;
$file1=$_FILES[file1][tmp_name];
$file1_name=$_FILES[file1][name];
$file1_size=$_FILES[file1][size];
$file1_type=$_FILES[file1][type];

if(is_uploaded_file($file1)){
$s_file_name1=$file1_name;

$file1=str_replace("\\\\","\\",$file1);

$s_file_name1=str_replace(" ","_",$s_file_name1);
$s_file_name1=str_replace("-","_",$s_file_name1);
////////////////////UNCOMPLETE////////////////////////
if($uploadresult=uploadfile($file1,"$datadir/book/$dire",$s_file_name1,$mode,$_SESSION[key]))
move_uploaded_file($file1,"$datadir/book/$dire/$s_file_name1");

}
}

$sql=mysql_connect($sqlserver,$sqlid,$sqlpass);
$sqll=mysql_query("use $sqldb",$sql);
$sqll=mysql_query("select max(num) as vvv from iladbook",$sql);
$maxnum=mysql_fetch_array($sqll);
$maxnum=$maxnum[vvv]+1;
if($_GET[what]==523 && $perm[write]==1){
$_GET[eee]=addslashes($_GET[eee]);
$sqll=mysql_query("delete from iladbook where num='$_GET[eee]' and (user='$_SESSION[uid]' and user!='guest' or user='guest' and pass='".md5($_POST[pass])."')");
echo $sqll;
if(mysql_fetch_array($sqll))
$sqll=mysql_query("delete from iladbook where reply='$_GET[eee]' and user='$_SESSION[uid]'");
}
if($_POST[what]==book && $perm[write]==1){
$setup[avoid_tag]="ui,p,b,strong,table,br,a,font,tr,td,tbody,l,em,u,strike,img,embed,video,audio";

$memo=$_POST[content];
$memo=str_replace("<","$lt;",$memo);

$tag=explode(",",$setup[avoid_tag]);
for($i=0; $i<count($tag) ; $i++){
if(!isblank($tag[$i])) {
$memo=eregi_replace("$lt;".$tag[$i]." ","<".$tag[$i]." ",$memo);
$memo=eregi_replace("$lt;".$tag[$i].">","<".$tag[$i].">",$memo);
$memo=eregi_replace("$lt;/".$tag[$i],"</".$tag[$i],$memo);
}
}
$memo=addslashes($memo);
$uploadresult=addslashes($uploadresult);
$_POST[reply]=addslashes($_POST[reply]);
$_POST[subject]=addslashes($_POST[subject]);
$_POST[pass]=addslashes($_POST[pass]);
$sqll=mysql_query("insert into iladbook(num,user,pass,subject,nick,time,default_dir,file,content,reply,ip) values ($maxnum,'$_SESSION[uid]','".md5($_POST[pass])."','$_POST[subject]','$_POST[nick]',now(),'$database','$uploadresult','$memo',$_POST[reply],'$_SESSION[ip]')");
mysql_query("update iladmenu set lasttime=now() where path='book#$database'",$sql);
}
$qur="select * from iladbook where default_dir LIKE \"$showdatabase[0]\" ";

$finish=0;
for($i=1 ; $i<count($showdatabase) ; $i++)
$qur.="or default_dir='$showdatabase[$i]' ";
$qur.="and reply=-1 order by num desc limit ".($limit-20).", ".(20);
$sqll=mysql_query($qur,$sql);
if(mysql_num_rows($sqll)!=20)
$finish=1;
?>
<?
if($_POST[what]!="bookget")
echo "<div id=plusarea>";
?>

<?
while($result=mysql_fetch_array($sqll)){
if($perm[read]==0) continue;
echo "<table width=100% style='table-layout:fixed;word-wrap:break-word'><tr><td bgcolor=#eeeeff height=30 style='border-bottom : 1px solid black;border-top : 1px solid black;'>";
echo "<div class=bookfir>$result[nick]($result[user])</div><div class=booksec>$result[default_dir]</div><div class=bookthi><a href='oo.php?what=523&eee=$result[num]'><font color=red>x</font></a></div><div class=bookfor>$result[time]</div>
<div class=bookfir><a href=javascript:openarticle('article$result[num]')> &nbsp;";
if(trim($result[subject])=="")
echo "제목 없음";
echo $result[subject];
echo " </a></div> </td></tr><td id=article$result[num] style='word-break:break-all;display:none'>";
echo "<div class=bookcon>";
if($result[file]!=""){
$hjj=substr($result[file],-3,3);
if($hjj=="jpg" || $hjj=="gif" || $hjj=="png" || $hjj=="bmp")
echo "<img src=download.php?file=".urlen("$datadir/book/$dire/".$result[file]).">";
else
echo "<a href=download.php?file=".urlen("$datadir/book/$dire/".$result[file]).">FILE : $result[file]</a><br>";
}
echo "<pre>".nl2br(str_replace("<!--","",$result[content]))."</pre>";
echo "</div>";
$sqlll=mysql_query("select * from iladbook where reply=$result[num] order by num desc",$sql);
echo "<table width=100% style='table-layout:fixed'>";
while($resultt=mysql_fetch_array($sqlll)){
echo "<tr class=rep><td align=left width=100><font color=gray size=2><b>$resultt[nick]</b></font></td><td width=500 style='word-break:break-all'><pre>".nl2br(str_replace("<!--","",$resultt[content]))."</pre></td><td align=right> $resultt[time]<a href='oo.php?what=523&eee=$resultt[num]'><font color=red size=2>x</font></a></td></tr>";
}
echo "</table>";
echo "<div class=repwrite>";
if($perm[write]==1){
echo "<form action=oo.php method=post>";
echo "<input type=hidden value=book name=what></input>";
echo "<input type=hidden value=$result[num] name=reply></input>";
if($_SESSION[id]=="guest"){
echo "&nbsp;&nbsp;Nick : <input type=text name=nick value=$_SESSION[id] style='width:100px'></input>Password : <input type=password name=pass style='width:100px'></input><input type=text name=content style='width:70%'></input>";
}
else
echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nick : <input type=text name=nick style='width:100px'></input><input type=text name=content style='width:70%'></input>";
echo "<input type=submit></input>";
echo "</form>";
}
echo "</div>";
echo "</td></tr></table>";
}
mysql_close($sql);
?>
<?
if($_POST[what]!="bookget")
echo "</div>";
?>
<div id=hid>
<input type=hidden id=limi value=<?=$limit?>></input>
<input type=hidden id=fini value=<?=$finish?>></input>
</div>
<?
if($finish==0 && $_POST[what]!="bookget")
echo "<input type=button value='더보기' style='width:100%;height:50' onclick=gettingb();></input>";
echo "<script src=prototype.js></script>
<script>
function gettingb(){
                var div = document.getElementById('plusarea');
getting(div);
}
function getting(div){
var lmt=document.getElementById('limi').value;
var fini=document.getElementById('fini').value;
var hid=document.getElementById('hid');
hid.innerHTML=div.innerHTML+'<h1><center>Loading...</center></h1>';
for(var i=0 ; i<20 ; i++)
lmt++;
var opt=new Array();
opt['method']='post';
opt['parameters']={'what':'bookget', 'limit':lmt};
opt['onComplete']=function(ee){
hid.innerHTML='';
hid.parentNode.removeChild(hid);
div.innerHTML=div.innerHTML+ee.responseText;};
opt['onFailure']=function(){};
new Ajax.Request('oobook.php',opt);
}
checkTargetScroll();
            function checkTargetScroll(){
                var div = document.getElementById('plusarea');
var fini=document.getElementById('fini').value;

                if(div.scrollHeight-window.scrollY<1500){
//getting(div);
      }
//if(fini==0)
            //    setTimeout('checkTargetScroll()',1000);
            }
function openarticle(ids){
var ttt=document.getElementById(ids);
if(ttt.style.display=='none')
ttt.style.display='block';
else
ttt.style.display='none';
}
</script> ";
?>
