<?php
  /* Template Name: Skedge-Sound-ContentCheck */

  #connect
  require_once get_template_directory()."/skedge-api/Database.php";
  $conn = dbconn();

  #save form
  if (!empty($_POST)) {
    $skedge_id = $_GET['id'];
    $changes = $_POST['changes'];

    #iterate over the submitted changes and add each one as a comment
    for($i = 0; $i < $changes; $i++){
      if(!empty($_POST["timestamp_$i"])){
        $timestamp = $_POST["timestamp_$i"];
        $description   = $_POST["description_$i"];
        $comment = new Comment();
        $comment->skedge_id = $skedge_id;
        $comment->text = "Editing Note @ $timestamp: $description";
        $comment->saveRecord($conn, $user_login);
      }
    }
    #update skedge record
    $skedge = new Skedge();
    $skedge->id = $skedge_id;
    $skedge->stage = 8;
    $skedge->saveRecord($conn, $user_login);

    #redirect
    header( "Location: ../editing/?id=$skedge_id" );
  }

  #get skedge records
  if(!empty($_GET['id'])){
    $skedge = new Skedge();
    $forms['skedge'] = $skedge->retrieveRecordByID($conn, $_GET['id']);
    $skedge->retrieveAssociatedRecords($conn, $_GET['id'], $forms);
  }
  get_header();

?>

<script type="text/javascript">
  var changes = 0
  var removeIssue = function(index){
    $("#issue_"+index).remove()
  }
  var addChange = function() {
    changes++
    $("#changeCount").val(changes)
    $('#changes').prepend(
    "<div class='issue' id='issue_"+changes+"'>" +
      "<button type='button' onclick='removeIssue("+changes+")'>Remove</button><br>" +
      "Time" +
      "<input id='timestamp_"+changes+"' type='text' name='timestamp_"+changes+"' value=''>" +
      "Comment" +
      "<textarea id='description_"+changes+"' name='description_"+changes+"' rows='3'></textarea>" +
    "<br><br><hr></div>"
    )
  }
</script>

<div id="content">
  <h2>Content Review</h2>
  <p>If something significant was changed or removed during editing, make a note of it here for the producer.</p>
  <hr>

  <h4>Podcast Title: <?php echo $forms['podcast']['podcast_title'] ?></h4>
  <h4>Podcast Channel: <?php echo $forms['podcast']['channel'] ?></h4>
  <br>
  <audio controls src="/storage/raw_podcasts/<?php echo $forms['podcast']['raw_audio_url'] ?>"></audio>
  <br>
  <button type="button" name="button" onclick="addChange()">New Change</button>
  <br>
  <br>
  <b>Changelog</b>
  <br>
  <br>
  <form id="changes" method="POST" enctype="multipart/form-data">

    <br>
    <button style="float:right">Submit</button>
    <input id="changeCount" type="text" name="changes" value="0" style="display:none">
  </form>

</div>

<?php get_footer(); ?>
