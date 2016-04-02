<?

include "head.php";


zero2gfs("oun_best","oun_best");
zero2gfs("oun_exam","oun_exam");
for($k=1 ; $k<=21 ; $k++)
zero2gfs("oun_exam_$k","oun_exam_$k");
zero2gfs("oun_exam_list","oun_exam_list");
zero2gfs("oun_first","oun_first");
zero2gfs("oun_freeboard","oun_freeboard");
zero2gfs("oun_glecture","oun_glecture");
zero2gfs("oun_introduce","oun_introduce");
zero2gfs("oun_lecture","oun_lecture");
zero2gfs("oun_memo","oun_memo");
zero2gfs("oun_notice","oun_notice");
zero2gfs("oun_problems","problems");
zero2gfs("oun_problems_brightly","problems_brightly");
zero2gfs("oun_problems_brs","problems_brs");
zero2gfs("oun_problems_eriveri","problems_eriveri");
zero2gfs("oun_problems_gmc","problems_gmc");
zero2gfs("oun_problems_imindong","problems_imindong");
zero2gfs("oun_problems_nirvana","problems_nirvana");
zero2gfs("oun_problems_suby","problems_suby");
zero2gfs("oun_problems_sun8282","problems_sun8282");
zero2gfs("oun_problems_workbs","problems_workbs");
zero2gfs("oun_problems_qna","problems_qna");
zero2gfs("oun_problems_second","problems_second");
zero2gfs("oun_problems_solve","problems_solve");
zero2gfs("oun_problems_third","problems_third");
zero2gfs("parpaall","parpaall");
zero2gfs("parpadream","parpadream");
zero2gfs("parpaesp","parpaesp");
zero2gfs("parpagi","parpagi");
zero2gfs("parpahyp","parpahyp");
zero2gfs("parpfreepds","parpfreepds");
zero2gfs("parpguest","parpguest");
zero2gfs("parpmemo","parpmemo");
zero2gfs("parpmp3","parpmp3");
zero2gfs("parpmsg","parpmsg");
zero2gfs("parpnotice","parpnotice");
zero2gfs("parppdsetc","parppdsetc");
zero2gfs("parppic","parppic");
zero2gfs("parpprog","parpprog");
zero2gfs("parpqna","parpqna");
zero2gfs("parpres","parpres");
zero2gfs("parpwho","parpwho");
zero2gfs("parpaall","parpaall");

function zero2gfs($tablename,$where){
$where=$tablename;
//makedir("/home/owner/com/data/backup",$tablename);
//htmlsave("/home/owner/com/data/backup/$tablename/database.txt","$where|$where");
//return 0;
global $sqlserver,$sqlid,$sqlpass,$sqldb;
$sql=mysql_connect($sqlserver,$sqlid,$sqlpass);
flush();
echo "<br>$tablename : ".mysql_query("use $sqldb",$sql);

$sqll=mysql_query("select max(num) as mm from iladmenu",$sql);
$maxnum=mysql_fetch_array($sqll);
$maxnum=$maxnum[mm]+1;
//echo "<br> ILADMENU : ".mysql_query("insert into iladmenu(num,name,path,upnum) values ($maxnum,'$tablename','book#$tablename',2)",$sql);

$sqll=mysql_query("select max(num) as mm from iladbook",$sql);
$maxnum=mysql_fetch_array($sqll);
$maxnum=$maxnum[mm]+1;

$sqll=mysql_query("select ismember,password,no,memo,ip,name,subject,reg_date from zetyx_board_$tablename",$sql);
while ($result=mysql_fetch_array($sqll)){
$result[memo]=addslashes(iconv('euc-kr','utf-8',$result[memo]));
$result[subject]=addslashes(iconv('euc-kr','utf-8',$result[subject]));
$result[name]=iconv('euc-kr','utf-8',$result[name]);
$qur=mysql_query("select user_id from zetyx_member_table where no=$result[ismember]",$sql);
$uuu=mysql_fetch_array($qur);
$iid="guest";
if($uuu) $iid=$uuu[user_id];
$ddate=date("Y-m-d H:i:s",$result[reg_date]);
$qur="insert into iladbook(pass,subject,num,user,nick,time,default_dir,file,content,reply,ip) 
values ('$result[password]','$result[subject]',".($maxnum+$result[no]).",'$iid','$result[name]','$ddate','$where','','$result[memo]',-1,'$result[ip]')";
$nnn++;
//echo $qur."<br>";
echo "<br>QUR : ".mysql_query("$qur",$sql);
}
$sqll=mysql_query("select max(num) as mm from iladbook",$sql);
$nnn=mysql_fetch_array($sqll);
$nnn=$maxnum[mm]+1;

$sqll=mysql_query("select ismember,password,no,memo,ip,name,reg_date,parent from zetyx_board_comment_$tablename",$sql);
while ($result=mysql_fetch_array($sqll)){
$nnn++;
$result[memo]=addslashes(iconv('euc-kr','utf-8',$result[memo]));
$result[name]=iconv('euc-kr','utf-8',$result[name]);
$qur=mysql_query("select user_id from zetyx_member_table where no=$result[ismember]",$sql);
$uuu=mysql_fetch_array($qur);
$iid="guest";
if($uuu) $iid=$uuu[user_id];
$ddate=date("Y-m-d H:i:s",$result[reg_date]);
$qur="insert into iladbook(pass,num,user,nick,time,default_dir,file,content,reply,ip) 
values ('$result[password]',".($nnn).",'$iid','$result[name]','$ddate','$where','','$result[memo]',$result[parent]+$maxnum,'$result[ip]')";
//echo $qur."<br>";
echo "<br>QURCOMMENT : ".mysql_query("$qur",$sql);
}
mysql_close();
}
?>
