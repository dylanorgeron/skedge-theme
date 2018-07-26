<?php
  /* Template Name: Skedge-Bumpers */

  #user check
  wp_get_current_user();
  if(!$user_ID) header("Location: /wp-login.php");

  #requires
  require_once get_template_directory()."/skedge-api/Database.php";

  #db connection
  $conn = dbconn();
  $bumpers_dir = realpath($_SERVER["DOCUMENT_ROOT"])."/storage/bumpers/";

  #upload new bumper
  if(!empty($_FILES)){
    #build filename
    $bumper_filename = time() . "--" . date('Y-m-d') . "--" . $_FILES['bumper']['name'];
    $bumper_target_file = $bumpers_dir . $bumper_filename;

    #grab the file extension
    $extension = pathinfo($_FILES["bumper"]["name"], PATHINFO_EXTENSION);

    #validate file and upload
    #make sure we dont get bullshit files
    $allowedExts = array("wav", "aac", "mp3");
    if(in_array($extension, $allowedExts)) move_uploaded_file($_FILES["bumper"]["tmp_name"], $bumper_target_file);
  }

  #fetch existing bumpers
  $bumpers = array_diff(scandir($bumpers_dir), array('..', '.'));

  get_header();
?>

<div id="content">
  <h2>Bumpers</h2>
  <p>Bumpers help set the mood, playing at the start and end of each podcast.</p>
  Current Bumpers
  <ul>
    <?php foreach ($bumpers as $bumper) {
      echo "<li><a href='/storage/bumpers/$bumper'>$bumper</a></li>";
    }?>
  </ul>
  <b>New Bumper</b>
  <br>
  <em>Name your bumper something descriptive! <br> Don't worry about the date, we'll take care of that for you.</em>
  <br>
  <form method="POST" enctype="multipart/form-data">
    <input type="file" name="bumper" value="">
    <br><br>
    <button type="submit" name="button">Upload</button>
  </form>
</div>

<?php get_footer(); ?>
