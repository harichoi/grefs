<?
if(!is_dir("../ses")) {
			mkdir("../ses", 0777);
			chmod("../ses", 0777);
		}

session_save_path("../ses");
session_cache_limiter('nocache, must_revalidate');
session_set_cookie_params(0,"/");
@session_start();

?>
<body bgcolor=black>
<embed
  flashvars="file=want.php&autostart=true"
  allowfullscreen="true"
  allowscripaccess="always"
  id="player1"
  name="player1"
  src="player.swf"
  width="480"
  height="270"
/>
<?
echo '<div id="mediaplayer">JW Player goes here</div>
	
	<script type="text/javascript" src="player/jwplayer.js"></script>
	<script type="text/javascript">
		jwplayer("mediaplayer").setup({
			flashplayer: "player/player.swf",
			file: "http://127.0.0.1/com/download.php?file='.$_GET[file].'",
			image: "player/preview.jpg"
		});
	</script>
';
?>


			