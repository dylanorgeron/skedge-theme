<?php
  /* Template Name: Skedge-PendingVoice */

  #connect
  require_once get_template_directory()."/skedge-api/Database.php";
  $conn = dbconn();

  if(!empty($_GET["id"])){
    $id = $_GET["id"];
    $response = array();
    $guest = new Guest();
    $response = $guest->retrieveByID($conn, $id);
  }
  get_header();
?>

<h1>Voice Form</h1>
<hr>
<br>
  <?php
    foreach ($response as $key => $value) {
     echo "<div>$key: $value";
   }
  ?>

<?php get_footer(); ?>
