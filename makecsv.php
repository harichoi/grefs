<?
 Define( "DATABASE_SERVER", "localhost" ); //MySQL이 있는 서버의 주소죠 저의 경우에는 localhost에서 했어요
 Define( "DATABASE_USERNAME", "root" ); //MySQL DB의 ID입니다. 저는 root id로 ㅋ;
 Define( "DATABASE_PASSWORD", "iladnuksql" ); //ID에 따른 패스워드죠 apmsetup이라고 되 있는 이유는 제가 APMSETUP5를 사용하면서 따로 패스워드를 지정해주지 않

 Define( "DATABASE_NAME", "iladnuk" ); //MySQL DB Table의 이름입니다.
 
$fp=fopen("iladbook","w");  
   $mysql = mysql_connect(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD);mysql_select_db( DATABASE_NAME,$mysql);
   $qry = "select * from iladbook"; //airplane이라는 테이블의 모든 자료를 불러오는 쿼리입니다.
   $result = mysql_query($qry,$mysql); //바로 윗줄의 쿼리르 실행하는 명령이죠~ mysql_query(변수)
 while($obj=mysql_fetch_array($result)) //반복문이죠~! 반복조건으로는 mysql_fetch_object($result)를 주었는데요~ 이 명령은 행의 결과를 객체로 얻는다는건데.. 

{
echo fputcsv($fp,$obj);
//print_r($obj);


    }
 //printf($return); //C를 해보셨다면 친숙한 명령이죠 printf ㅋ; 위의 $return에 저장되 있는 문자열을 화면에 출력해줍니다.
 //echo "</node>"; //최상위 노드를 닫아줍니다.
?>
