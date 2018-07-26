<?php
  /* Template Name: Skedge-User-SkedgeRecord */

  #connect
  require_once get_template_directory()."/skedge-api/Database.php";
  $conn = dbconn();

  $response = array();
  if(!empty($_GET["id"])){
    #get skedge records
    $skedge = new Skedge;
    $response["skedge"] = $skedge->retrieveRecordByID($conn, $_GET["id"]);
    $skedge->retrieveAssociatedRecords($conn, $_GET["id"], $response);
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
  <hr>

  <div id="status" class="alert alert-info">
    <div>Status: <b><?php echo $response['skedge']['status_title'] ?></b></div>
    <br>
    <div><?php echo $response['skedge']['status_desc'] ?></div>
  </div>

  <?php if(!empty($response['channel'])){ ?>
    <h4>Channel</h4>
    <div><?php echo $response['channel']['channel_name'] ?></div>
    <?php echo "<a href=".add_query_arg('id', $response['channel']['id'], 'pendingchannel').">View Form</a>"; ?>
    <?php }else{ ?>
    <div data-bind="text: viewModel.podcast.channel"></div>
  <?php } ?>
  <br><br>

  <?php if(!empty($response['guests'])){ ?>
    <h4>Voices</h4>
    <div>
      <ul>
        <?php foreach ($response['guests'] as $guest) {?>
        <li>
          <div><?php echo $guest['first_name'] ." ". $guest['last_name'] ?></div>
          <?php echo "<a href=".add_query_arg('id', $guest['id'], 'pendingvoice').">View Form</a>"; ?>
        </li>
        <?php } ?>
      </ul>
    </div>
  <?php } ?>
  <hr>

  <!--Activity Feed-->
  <form id="comment-form" method="POST" enctype="multipart/form-data">
    <h4><b>Activity Feed</b></h4>
    <div>
      <ul>
      <?php foreach ($response['history'] as $state) { ?>
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
