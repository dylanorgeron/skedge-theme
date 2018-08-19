<?php
  /* Template Name: Skedge-PendingVoice */

  #connect
  require_once get_template_directory()."/skedge-api/Database.php";
  $conn = dbconn();

    if(!empty($_GET["id"])){
      $id = $_GET["id"];
      $channel = array();
      $channel = new Channel();
      $channel = $channel->retrieveByID($conn, $id);
    }

    get_header();
?>

<h1>Voice Form</h1>
<hr>
<br>
  <?php
    foreach ($channel as $key => $value) {
     echo "<div>$key: $value";
   }
  ?>

<?php get_footer(); ?>
