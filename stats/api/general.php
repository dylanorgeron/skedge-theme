<?php
    function getVoices($conn){
        $response = array();

        // fetch total voices
        $total_voices_sql= $conn->prepare("SELECT `meta_key`, `meta_value` FROM `wp_postmeta` WHERE `meta_key` = 'Gender'");
        $total_voices_sql->execute();
        $response['total_voices'] = $total_voices_sql->fetch(PDO::FETCH_ASSOC);

        
        // fetch female voices
        $female_voices_sql= $conn->prepare("SELECT `meta_key`, `meta_value` FROM `wp_postmeta` WHERE `meta_value` = 'Female'");
        $female_voices_sql->execute();
        $response['female_voices'] = $female_voices_sql->fetch(PDO::FETCH_ASSOC);
        
        // fetch male voices
        $male_voices_sql= $conn->prepare("SELECT `meta_key`, `meta_value` FROM `wp_postmeta` WHERE `meta_value` = 'Male'");
        $male_voice_sql->execute();
        $response['male_voices'] = $male_voice_sql->fetch(PDO::FETCH_ASSOC);

        return $response;
    }
?>