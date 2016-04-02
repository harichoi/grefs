<?
if(!is_dir("ses")) {
			mkdir("ses", 0777);
			chmod("ses", 0777);
		}


session_save_path("ses");
session_cache_limiter('nocache, must_revalidate');
session_set_cookie_params(0,"/");
@session_start();
$_SESSION[id]="";
$_SESSION[ip]="";
@session_destroy();
?>
<script> this.location.replace("index.php");</script>