<?php
  /* Template Name: Skedge-Sound-Editing */

  #connect
  require_once get_template_directory()."/skedge-api/Database.php";
  $conn = dbconn();

  #get skedge records
  if(!empty($_GET['id'])){
    $skedge = new Skedge();
    $forms['skedge'] = $skedge->retrieveRecordByID($conn, $_GET['id']);
    $skedge->retrieveAssociatedRecords($conn, $_GET['id'], $forms);
  }
  get_header();
?>

<div id="content">
  <h2>Publish Date: <?php echo $forms['skedge']['schedule_date'] ?></h2>
  <h4>Podcast Title: <?php echo $forms['podcast']['podcast_title'] ?></h4>
  <h4>Podcast Channel: <?php echo $forms['podcast']['channel'] ?></h4>

  <br>

  <ul>
    <li>
      <?php if($forms['skedge']['stage'] == 6){ ?>
        <a href="<?php echo add_query_arg('id', $forms['skedge']['id'], 'soundcheck') ?>">Sound Check</a>
      <?php }else{ ?>
        Sound Check
        <?php if($forms['skedge']['stage'] > 6){echo "[Complete]";} ?>
      <?php } ?>
    </li>
    <li>
      <?php if($forms['skedge']['stage'] == 7){ ?>
        <a href="<?php echo add_query_arg('id', $forms['skedge']['id'], 'contentcheck') ?>">Content Check</a>
      <?php }else{ ?>
        Content Check
        <?php if($forms['skedge']['stage'] > 7){echo "[Complete]";} ?>
      <?php } ?>
    </li>
    <li>
      <?php if($forms['skedge']['stage'] == 8){ ?>
        <a href="<?php echo add_query_arg('id', $forms['skedge']['id'], 'upload') ?>">Edit and Upload</a>
      <?php }else{ ?>
        Edit and Upload
        <?php if($forms['skedge']['stage'] > 8){echo "[Complete]";} ?>
      <?php } ?>
    </li>
    <li>
      <?php if($forms['skedge']['stage'] == 9){ ?>
        <a href="<?php echo add_query_arg('id', $forms['skedge']['id'], 'finallisten') ?>">Final Listen</a>
      <?php }else{ ?>
        Final Listen
        <?php if($forms['skedge']['stage'] > 9){echo "[Complete]";} ?>
      <?php } ?>
    </li>
    <li>
      <?php if($forms['skedge']['stage'] == 10){ ?>
        <a href="<?php echo add_query_arg('id', $forms['skedge']['id'], 'publishprep') ?>">Prep for Publication</a>
      <?php }else{ ?>
        Prep for Publication
        <?php if($forms['skedge']['stage'] > 10){echo "[Complete]";} ?>
      <?php } ?>
    </li>
    <li>
      <?php if($forms['skedge']['stage'] == 11){ ?>
        <a href="<?php echo add_query_arg('id', $forms['skedge']['id'], 'finalize') ?>">Finalize</a>
      <?php }else{ ?>
        Finalize
        <?php if($forms['skedge']['stage'] > 11){echo "[Complete]";} ?>
      <?php } ?>
    </li>
  </ul>

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
            <div><?php echo $state['text'] ?></div>
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
