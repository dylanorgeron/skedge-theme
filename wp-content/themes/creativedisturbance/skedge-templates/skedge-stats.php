<?php
  /* Template Name: Skedge-Stats */

  #connect
  require_once get_template_directory()."/skedge-api/Database.php";
  $conn = dbconn();

  get_header();
?>

<style>
  button.active{
    background: #007acc;
  }
  .scroll-box{
    height: 300px;
    overflow-y:scroll;
    padding:15px;
    border: 1px solid #eee;
  }
</style>

<!--Total-->
<section id="podcast">
    <center>
        <h3 class="lead" style="color:white;">Total Downloads</h3>
        <h2 id="total-downloads" class="number" style="font-size:46px;"></h2>
    </center>
</section>

<!--Monthly Downloads-->
<section>
    <center>
        <h3>Monthly Podcast Downloads</h3>
        <p id="current-month" class="number"></p>
        <p class="percent-change"><span id="current-month-change">%</span> this month</p>
    </center>
    <br><br>
    <section class="chart" id="month">
        <canvas id="monthChart"></canvas>
        <br>
        <br>
        <center id="month-buttons">
            <button id="6" class="active month-btn" onclick="drawMonth(6)">6 Months</button>
            <button id="12" class="month-btn" onclick="drawMonth(12)">12 Months</button>
            <button id="999" class="month-btn" onclick="drawMonth(999)">All Time</button>
        </center>
    </section>
    <br>
    <div id="monthly-list" class="scroll-box">
        <div class="list-container">
        </div>
    </div>
</section><!--End Monthly-->

<hr>
<br>

<!--Weekly-->
<section>
    <center>
        <h3>Weekly Podcast Downloads</h3>
        <p id="current-week" class="number"></p>
        <p class="percent-change"><span id="current-week-change">%</span> this week</p>
    </center>
    <!--Weekly-->
    <div id="week-col">
        <section class="chart" id="weekly">
            <canvas id="weekChart"></canvas>
            <!--<div id="week-table" class="table"></div>-->
            <br><br>
            <center id="week-buttons">
                <button class="12 btn btn-primary" onclick="drawWeek(12)">12 Weeks</button>
                <button class="36 btn btn-default" onclick="drawWeek(36)">36 Weeks</button>
                <button class="999 btn btn-default" onclick="drawWeek(999)">All Time</button>
            </center>
        </section>
        <div id="weekly-list" class="scroll-box">
            <div class="list-container">
            </div>
        </div>
    </div>
</section><!--End Weekly-->

<script src="/resources/js/Chart.min.js"></script>
<script src="/resources/js/dashboard.js"></script>

<?php get_footer(); ?>
