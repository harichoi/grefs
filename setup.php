﻿﻿<?
include "ooconfig.php";
error_reporting(E_ALL);
ini_set("display_errors", 1);
?>
<pre>
<?
echo $sql=mysql_connect($sqlserver,$sqlid,$sqlpass);
echo mysql_query("create database $sqldb",$sql);
echo $ssql=mysql_query("show databases",$sql);

echo "<br> USE : ".mysql_query("use $sqldb",$sql);
echo "<br> ILADBOOK : ".mysql_query("create table iladbook(num int,user varchar(50) character set utf8 collate utf8_general_ci, pass varchar(50) character set utf8 collate utf8_general_ci, subject varchar(256) character set utf8 collate utf8_general_ci,nick varchar(50) character set utf8 collate utf8_general_ci, time 
datetime,default_dir varchar(256) character set utf8 collate utf8_general_ci,file varchar(256) character set utf8 collate utf8_general_ci,content text character set utf8 collate utf8_general_ci,reply int,ip varchar(40))",$sql);
echo "<br>ILADMENU : ". mysql_query("create table iladmenu(num int,name varchar(50) character set utf8 collate utf8_general_ci,path text character set utf8 collate utf8_general_ci, upnum int,lasttime datetime)",$sql);
echo "<br>ILADMEMBER : ". mysql_query("create table iladmember(num int,id varchar(50) character set utf8 collate utf8_general_ci,password varchar(60) character set utf8 collate utf8_general_ci, memo text character set utf8 collate utf8_general_ci, uid varchar(20) character set utf8 collate utf8_general_ci, gid varchar(20) character set utf8 collate utf8_general_ci, level int,point int)",$sql);
echo "<br>rootset : ". mysql_query("insert into iladmember(num,id,password,memo,uid,gid,level,point) values (1,'root',password('root'),'root is root','root','root',0,0)",$sql);
echo "<br>guestset : ". mysql_query("insert into iladmember(num,id,password,memo,uid,gid,level,point) values (2,'guest',password('guest'),'guest is guest','guest','com',0,0)",$sql);
echo "<br>ILADGROUP : ".mysql_query("create table iladgroup(num int,name varchar(50) character set utf8 collate utf8_general_ci,mustdir text character set utf8 collate utf8_general_ci,homedir text character set utf8 collate utf8_general_ci)",$sql);
echo "<br>comgroupset : ". mysql_query("insert into iladgroup(num,name,mustdir,homedir) values (1,'com','','/컴퓨터/정보올림피아드')",$sql);

echo "<br>";
$ssql=mysql_query("show tables");
echo mysql_query("select * from book",$sql);

while($result=mysql_fetch_array($ssql)){
print_r($result);
}
//num,user,time,default_dir,file,content,reply) 
?>
