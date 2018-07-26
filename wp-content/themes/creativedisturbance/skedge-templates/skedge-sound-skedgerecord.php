<?php
  /* Template Name: Skedge-Sound-SkedgeRecord */

  #connect
  require_once get_template_directory()."/skedge-api/Database.php";
  $conn = dbconn();

  if(!empty($_GET["id"])){
    #get skedge records
    $skedge = new Skedge;
    $forms = array();
    $forms["skedge"] = $skedge->retrieveRecordByID($conn, $_GET["id"]);
    $skedge->retrieveAssociatedRecords($conn, $_GET["id"], $forms);
  }

  if (isset( $_POST['form-type']) && !empty($_GET['id']) && $forms['skedge']['stage'] == 4) {
    #save forms
    if($_POST['form-type'] == 'voice'){
      #save voice form
      $guest = new Guest();
      $g               = $guest->retrieveByID($conn, $_POST['voice-id']);
      $guest->id       = $g['id'];
      $guest->link     = $_POST['voice-link'];
      $guest->archived = 1;
      $guest->saveRecord($conn, $user_login);

      #delete photo from temp storage directory
      if(!empty($g['photo_url'])) unlink($photos_dir. $g['photo_url']);

      #make history state
      $guest_name = $g['first_name'] ." ". $g['last_name'];
      $history = new History();
      $history->skedge_id         = $_GET['id'];
      $history->action            = "added $guest_name to the Creative Disturbance voices. See them here: $guest->link";
      $history->action_creator    = $user_login;
      $history->makeHistoryState($conn);
    }else{
      #save channel form
      $channel = new Channel();
      $c                  = $channel->retrieveByID($conn, $_POST['channel-id']);
      $channel->id        = $c['id'];
      $channel->link      = $_POST['channel-link'];
      $channel->archived  = 1;
      $channel->saveRecord($conn, $user_login);

      #delete photo from temp storage directory
      if(!empty($c['photo_url'])) unlink("$photos_dir". $c['photo_url']);

      #make history state
      $channel_name               = $c['channel_name'];
      $history = new History();
      $history->skedge_id         = $_GET['id'];
      $history->action            = "added $channel_name to the Creative Disturbance channels. See it here: $channel->link";
      $history->action_creator    = $user_login;
      $history->makeHistoryState($conn);
    }
    #check if this record is complete
    $skedge = new Skedge();
    if($skedge->checkFormCompletion($conn, $_GET['id'])){
      $skedge->completeRecord($conn, $_GET['id'], $user_login);
    }
    #fetch new data
    $forms["skedge"] = $skedge->retrieveRecordByID($conn, $_GET["id"]);
    $skedge->retrieveAssociatedRecords($conn, $_GET["id"], $forms);
  }
  get_header();
?>

<script>
  var submitForm = function(el){
    var form = $(el).parent()
    $(form).submit()
  }
</script>

<div id="content">
  <h2>Skedge Record</h2>
  <p>You've been assigned a skedge record. Below are the things that need to be taken care of.</p>
  <hr>

  <!--Channel Form-->
  <form id="channel-form" method="POST" enctype="multipart/form-data">
    <?php if(!empty($forms["channel"])){ ?>
      <h4><b>Create New Channel</b></h4>
      <p>The producer has indicated that they would like to start a new channel.</p>
      <form method="POST" enctype="multipart/form-data">
        <?php if($forms['channel']['archived'] == 0){ ?>
        <ul>
          <li>
            Start by reviewing the channel form to make sure all the information looks correct.
            <br>
            <a href="<?php echo add_query_arg('id', $forms['channel']['id'], 'index.php/skedge/pendingchannel') ?>">View Form</a>
          </li>
          <br>
          <li>
            Next, follow <a href="/wp-admin/post-new.php?post_type=channel">this link</a> to create a WordPress page for the new channel.
          </li>
          <br>
          <li>
            Once the page has been created, paste the URL of the new channel in the box below to complete the process.
            <input name="channel-link" class="form-control" type="text">
            <input name="channel-id" value="<?php echo $forms['channel']['id'] ?>" style="display: none">
            <input name="form-type" value="channel" style="display: none">
            <br><br>
            <button onclick="submitForm(this)">Save Channel Link</button>
          </li>
        </ul>
        <?php }else{ ?>
          <span class="glyphicon glyphicon-check"></span> The channel has been added to Creative Disturbance and can be viewed <a href="<?php echo $forms['channel']['link'] ?>">here</a>.
        <?php } ?>
      </form>
    <?php } ?>
  </form>

  <br><br>

  <!--Voice Form-->
  <section>
    <?php if(!empty($forms["guests"])){ ?>
    <h4><b>Add New Voices</b></h4>
    <p>New voices have joined Creative Disturbance. Review each of the forms and add them to WordPress.</p>

    <ul>
      <?php foreach ($forms['guests'] as $guest) { ?>
      <li>
        <form method="POST" enctype="multipart/form-data">
          <?php if($guest['archived'] == 0){ ?>
          <span class="guest_name"><?php echo $guest['first_name'] ." ". $guest['last_name']; ?></span>
          <br>
          <a target="_blank" href="<?php echo add_query_arg('id', $guest['id'], 'index.php/skedge/pendingvoice') ?>">View Form</a>
          <br>
          <a target="_blank" href="/wp-admin/post-new.php?post_type=voice">Create WordPress draft</a>
          <br>
          <input name="voice-link" class="form-control" type="text">
          <br>
          <br>
          <input name="voice-id" value="<?php echo $guest['id'] ?>" style="display: none">
          <input name="form-type" value="voice" style="display: none">
          <button onclick="submitForm(this)">Save Voice Link</button>
          <?php }else{ ?>
          <span class="glyphicon glyphicon-check"></span> <?php echo $guest['first_name'] ." ". $guest['last_name'] ?> has been added to Creative Disturbance and can be viewed <a href="<?php echo $guest['link'] ?>">here</a>.
          <?php } ?>
        </form>
      </li>
      <?php } ?>
    </ul>
    <?php } ?>
  </section>
  <hr>

  <!--Activity Feed-->
  <form id="comment-form" method="POST" enctype="multipart/form-data">
    <h4><b>Activity Feed</b></h4>
    <div>
      <ul>
      <?php foreach ($forms['history'] as $state) { ?>
        <li>
          <?php if(!empty($state['action'])){ ?>
            <div><?php echo $state['action_creator'] ." ". $state['action'] ." ". $state['action_target']?></div>
            <em><?php echo $state['timestamp'] ?></em>
          <?php }else{ ?>
            <div><?php echo $state['username'] ." said on ". $state['timestamp'] ?></div>
            <div><?php echo $state['comment'] ?></div>
          <?php } ?>
        </li>
      <?php } ?>
      </ul>
      <textarea name="" id="" rows="4"></textarea>
      <br>
      <br>
      <button>Comment</button>
    </div>
  </form>
</div>

<?php get_footer(); ?>
