<?php
#storage
$photos_dir 					= realpath($_SERVER["DOCUMENT_ROOT"])."/storage/photos/";
$bumpers_dir          = realpath($_SERVER["DOCUMENT_ROOT"])."/storage/bumpers/";
$edited_podcasts_dir  = realpath($_SERVER["DOCUMENT_ROOT"])."/storage/edited_podcasts/";
$final_podcasts_dir   = realpath($_SERVER["DOCUMENT_ROOT"])."/storage/final_podcasts/";


#user check
wp_get_current_user();
if(!$user_ID) header("Location: /wp-login.php");

#require api files
$root = get_template_directory();
require_once "$root/skedge-api/Alert.php";
require_once "$root/skedge-api/Channel.php";
require_once "$root/skedge-api/Comment.php";
require_once "$root/skedge-api/Guest.php";
require_once "$root/skedge-api/History.php";
require_once "$root/skedge-api/Podcast.php";
require_once "$root/skedge-api/Skedge.php";

#db connection
function dbconn()
{
	$dbname = "";
	$servername = "";
	$username = "";
	$password = "";

	$conn = new PDO("mysql:host=localhost;dbname=$dbname;charset=utf8mb4", $username, $password);

	return $conn;
}
?>
