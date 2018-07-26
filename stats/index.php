<?php
    $root_path = "../resources/inc/"; 
    require('../wp-blog-header.php');
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Creative Disturbance Forms</title>
        <?php require($root_path.'head.php'); ?>
        <link href="../resources/css/jquery.scrollbar.css" rel="stylesheet">
        
        <style>
            .chart h3, #current-month, #current-week, #voices *, h3, #total-regions, #total-countries{
                color: rgba(62,72,99, .7);
            }
            @media (min-width: 991px){
                canvas{
                    /*max-width: 500px !important;*/
                }
            }
            .progress-bar{
                background-color: #FBA5D9;
            }
            .progress{
                background-color: #337AB7;
            }
            #monthly-list, #weekly-list, #episode-table ul, #region-table ul, #country-table ul{
                overflow-y: scroll;
                overflow-x: hidden; 
            }
            #monthly-list,#weekly-list{
                height:375px;
                padding-right: 15px;
            }
            #month-col, #month-col section, #week-col, #week-col section{
                min-height: 375px;
            }
            .month-row, .week-row{
                border-bottom: 1px solid #C5CDD2;
                padding: 2px 0;
            }
            .month-row:hover, .week-row:hover{
                background-color: #B4CBD8;
                border-color: #97BACD;
            }
        </style>
    </head>

    <body>
        <?php include $root_path.'header.php' ?>
        <?php
            $servername = "";
            $username = "";
            $password = "";
            $dbname = "";
            // Create connection
            $mysqli = new mysqli($servername, $username, $password, $dbname);
            // Check connection      
            if (mysqli_connect_error()) {
                $logMessage = 'MySQL Error: ' . mysqli_connect_error();
                // Call your logger here.
                die('Could not connect to the database');
            }
            // fetch total voices
            $total_voices_query= "SELECT `meta_key`, `meta_value` FROM `wp_postmeta` WHERE `meta_key` = 'Gender'";
            $total_voices = (mysqli_num_rows(mysqli_query($mysqli, $total_voices_query)));
            // fetch female voices
            $female_voices_query= "SELECT `meta_key`, `meta_value` FROM `wp_postmeta` WHERE `meta_value` = 'Female'";
            $female_voices = (mysqli_num_rows(mysqli_query($mysqli, $female_voices_query)));
            // fetch male voices
            $male_voices_query= "SELECT `meta_key`, `meta_value` FROM `wp_postmeta` WHERE `meta_value` = 'Male'";
            $male_voices = (mysqli_num_rows(mysqli_query($mysqli, $male_voices_query)));

            // query the latest people
            $people_query = "SELECT * FROM `wp_postmeta` WHERE `meta_key` = 'firstname' ORDER by `post_id` ASC LIMIT 10";
            $people_ids = mysqli_query($mysqli, $people_query);

            if (mysqli_num_rows($people_ids) > 0) {
                // output data of each row
                while($row = mysqli_fetch_assoc($people_ids)) {
                    // another sql 
                   $person_query = "SELECT `post_id`,`meta_key`, `meta_value` FROM `wp_postmeta` WHERE `post_id` = '". $row["post_id"] . "' AND `meta_key` IN ('lastname','firstname')";
                   $photo_query = "SELECT `post_id`,`meta_key`, `meta_value` FROM `wp_postmeta` WHERE `post_id` = '". $row["post_id"] . "' AND `meta_key` IN ('photo')";
                    $result = mysqli_query($mysqli, $person_query);
                    $photo_result = mysqli_query($mysqli, $photo_query);

                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            //echo $row["meta_value"] . " ";
                            //echo $row['post_id'];
                            //echo CCTM::filter( $row['post_id'], 'to_image_src');
                        }
                        while ($row = mysqli_fetch_assoc($photo_result)) {
                           // echo ;
                            //print CCTM::filter($row['post_id'], 'firstname lastname');
                            //echo get_custom_field('1840:to_image_src');
                            //$post = $row['meta_value'];
                            //print_custom_field($row['meta_value']->ID,'photo:to_image_src');
                            //echo $img;
                        }

                        //echo '<br>';                  
                    } else {
                        echo "0 results";
                    }
                    // spit it all out
                    //echo " " . $row["post_id"];
                }
            } else {
                echo "0 results";
            }
            //echo $people_ids;
            //echo $count;
            //print CCTM::filter(80, to_link);
        ?>
        <!--Blue-->
        <div class="container">
            <!--Total-->
            <section id="podcast">
                <h3 class="lead" style="color:white;">Total Downloads</h3>
                <h2 id="total-downloads" class="number" style="font-size:46px;"></h2>
            </section>
        </div> <!--End blue-->
        
        <article>
            <!--Grey-->
            <div class="grey">
                <div class="container">
                    <!--Monthly-->
                
                    <section>
                        <h3>Monthly Podcast Downloads</h3>
                        <p id="current-month" class="number"></p>
                        <p class="percent-change"><span id="current-month-change">%</span> this month</p>
                        <br><br>
                        <div class="row">
                            <div class="col-md-9" id="month-col">
                                <section class="chart" id="month">
                                    
                                    <canvas id="monthChart"></canvas>
                                    
                                    <!--<div id="month-table" class="table"></div>-->
                                    <br>
                                    <br>
                                    <center id="month-buttons">
                                        <button id="6" class="btn btn-primary" onclick="drawMonth(6)">6 Months</button>
                                        <button id="12" class="btn btn-default" onclick="drawMonth(12)">12 Months</button>
                                        <button id="999" class="btn btn-default" onclick="drawMonth(999)">All Time</button>
                                    </center>   
                                </section>
                            </div>
                            <div class="col-md-3">
                                <div id="monthly-list">
                                    
                                    <div class="list-container">
                                        
                                    </div>
                                </div>
                            </div>
                        
                        </div>
                    </section><!--End Monthly-->
                    <hr>
                    <br>
                    
                    <!--Weekly-->
                    <section>
                        <h3>Weekly Podcast Downloads</h3>
                        <p id="current-week" class="number"></p>
                        <p class="percent-change"><span id="current-week-change">%</span> this week</p>
                        <div class="row">
                            <!--Weekly-->
                            <div class="col-md-9" id="week-col">
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
                            </div>
                            <div class="col-md-3">
                                
                                <div id="weekly-list" class="">
                                    
                                    <div class="list-container">
                                    </div>
                                </div>
                            </div>
                        </div>            
                    </section><!--End Weekly-->         
                </div><!--End Container-->
            </div> <!--End Grey-->

            <div class="container">
            <div class="row">
                <!--Voices-->
                <div class="col-sm-6">
                    <section class="" id="voices">
                        <h3>Voices</h3>
                        <p id="total-voices" class="number"><?php echo $total_voices ?> voices </p>
                        <center>
                            <p><strong><?php echo Round(($female_voices / $total_voices) * 100) . "%" ?></strong> <small>Female</small> | <strong><?php echo Round(($male_voices / $total_voices) * 100) . "%" ?></strong> <small>Male</small></p>
                        </center>
                        
                        <canvas id="voice-chart" style="max-height: 300px;">
                            
                        </canvas>
                    </section>
                </div>
                <!--Episodes-->
                <div class="col-sm-6">
                    <section class="" id="episode">
                        <h3>Episode Details</h3>
                        <span id="total-episodes" class="number"></span>
                        <div id="episode-table" class="">
                            <ul></ul>
                        </div>
                    </section>              
                </div>
            
            </div>
            <hr>
            
            <div class="row">
            
                <!--Country-->
                <div class="col-sm-6">
                    <section class="" id="country">
                        <h3>Countries</h3>
                        <p id="total-countries" class="number"> countries</p>
                        <div id="country-table" class="">
                            <ul></ul>
                        </div>
                    </section>                    
                </div>
                <!--Regions-->
                <div class="col-sm-6">
                    <section class="" id="region">
                        <h3>Regions</h3>
                        <p id="total-regions" class="number"> locations</p>
                        <div id="region-table" class="">
                            
                            <ul></ul>
                        </div>
                    </section>
                </div>
            
            </div>
            <br>
            <br>
            <br>
            <?php include $root_path.'footer.php' ?>
            </div> <!-- end container -->
        </article>
        <script src="../resources/js/dashboard.js"></script>
        <script src="../resources/js/Chart.min.js"></script>
        <script src="../resources/js/jquery.scrollbar.min.js"></script>
        <script>
            $(document).ready(function(){
                $(".nav .active").removeClass("active");
                $("#stats-nav-link").addClass("active");
            })
            /*build with chart.js*/
            var voiceData = [
                {
                    value: 0,
                    color:"#F7464A",
                    highlight: "#FF5A5E",
                    label: "Male"
                },
                {
                    value: 0,
                    color: "#46BFBD",
                    highlight: "#5AD3D1",
                    label: "Female"
                },
            ]
            <?php 
                echo "voiceData[0].value = " . $male_voices . ";\n";
                echo "voiceData[1].value = " . $female_voices . ";\n";
            ?>
            var ctx = $("#voice-chart").get(0).getContext("2d");
            var dOptions = options;
            dOptions.maintainAspectRatio = true;
            dOptions.percentageInnerCutout = 70;
            var dChart = new Chart(ctx).Doughnut(voiceData, dOptions);
        </script>
    </body>
</html>