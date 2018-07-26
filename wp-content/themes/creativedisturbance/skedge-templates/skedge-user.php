<?php
  /* Template Name: Skedge-User */

  #connect
  require_once get_template_directory()."/skedge-api/Database.php";
  $conn = dbconn();

  #get skedge records
  $forms = array();
  $skedge = new Skedge;
  $forms['skedge_records'] = $skedge->retrieveRecordsByUser($conn, $user_login);

  get_header();
?>

<div id="content">
  <h2><?php echo $current_user->first_name; ?>, let your voice be heard</h2>
  <p>Share your podcasts on the Creative Disturbance platform</p>
  <a href="/index.php/wipeDB">Wipe Skedge Database</a>
  <br>
  <br>

  <b>Roles</b>
  <br>
  <a href="../coordinator">Coordinator</a>
  <br>
  <a href="../sound">Sound</a>

  <br>
  <br>

  <h4>Voice Forms</h4>
  <a href="voiceform/">Submit new form</a>
  <ul>
    <?php foreach ($forms['skedge_records'] as $record) {
      if($record['type'] == 'guest' && $record['stage'] == 1){
    ?>
      <li>
        <a href="<?php echo add_query_arg('id', $record['id'], 'voiceform') ?>">
          Voice Form started on <?php echo $record['timestamp']; ?>
        </a>
      </li>
      <?php }else if ($record['type'] == 'guest' && $record['stage'] != 1){ ?>
      <li>
        <a href="<?php echo add_query_arg('id', $record['id'], 'skedgerecord') ?>">
          Voice Form submitted on <?php echo $record['timestamp']; ?>
        </a>
      </li>
    <?php }} ?>
  </ul>
  <br>

  <h4>Channel Forms</h4>
  <a href="channelform/">Submit new form</a>
  <ul>
    <?php foreach ($forms['skedge_records'] as $record) {
      if($record['type'] == 'channel' && $record['stage'] == 1){
    ?>
      <li>
        <a href="<?php echo add_query_arg('id', $record['id'], 'channelform') ?>">
          Channel Form saved on <?php echo $record['timestamp']; ?>
        </a>
      </li>
    <?php }else if ($record['type'] == 'channel' && $record['stage'] != 1){ ?>
      <li>
        <a href="<?php echo add_query_arg('id', $record['id'], 'skedgerecord') ?>">
          Channel Form submitted on <?php echo $record['timestamp']; ?>
        </a>
      </li>
    <?php }} ?>
  </ul>
  <br>


  <h4>Podcast Forms</h4>
  <a href="newpodcastchannel/">Submit new form</a>
  <ul>
    <?php foreach ($forms['skedge_records'] as $record) {
      if($record['type'] == 'podcast'){
        $link = "";
          switch ($record['stage']) {
            case 1:
              $link = add_query_arg('id', $record['id'], 'newpodcastchannel');
                break;
              case 2:
                $link = add_query_arg('id', $record['id'], 'newpodcastvoices');
                break;
              case 3:
              case 4:
              case 6:
                $link = add_query_arg('id', $record['id'], 'skedgerecord');
                break;
              case 5:
                $link = add_query_arg('id', $record['id'], 'newpodcastdetails');
                break;
          }
    ?>
      <li>
        <a href="<?php echo $link; ?>">
          Podcast Form saved on <?php echo $record['timestamp']; ?>
        </a>
      </li>
    <?php }} ?>
  </ul>
  <br>
</div>

<?php get_footer(); ?>
