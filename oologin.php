﻿﻿<?
if($_SESSION[id]=="" || $_SESSION[id]=="guest"){
echo "<form action=oo.php method=post>";
echo "<input type=hidden name=what value=login></input>";
echo "ID : <input type=text name='id' size=5></input><br>";
echo "PASS : <input type=password name='password' size=5></input>";
echo "<input type=submit></input>";
echo "</form>";
echo "<a href=oo.php?what=join>회원가입</a>";
}
else{
echo " ID : <b>$_SESSION[id]</b>"."<br> UID : ".$_SESSION[uid]."<br> GID : ".$_SESSION[gid]."<br> logged <p><a href=logout.php>logout</a><br>";
if($_SESSION[uid]=="root"){
echo "<a href=oo.php?what=menu>메뉴동기화</a>";
echo "<a href=oo.php?what=menuedit>메뉴편집</a>";
}
}
?>
