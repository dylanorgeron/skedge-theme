<?php
    /* Template Name: Wipe-DB */

    require_once get_template_directory()."/skedge-api/Database.php";
    $conn = dbconn();

    $sql = $conn->prepare("DELETE FROM `alerts`");
    $sql->execute();
    $sql = $conn->prepare("DELETE FROM `channel`");
    $sql->execute();
    $sql = $conn->prepare("DELETE FROM `comments`");
    $sql->execute();
    $sql = $conn->prepare("DELETE FROM `coproducer`");
    $sql->execute();
    $sql = $conn->prepare("DELETE FROM `guest`");
    $sql->execute();
    $sql = $conn->prepare("DELETE FROM `history`");
    $sql->execute();
    $sql = $conn->prepare("DELETE FROM `podcast`");
    $sql->execute();
    $sql = $conn->prepare("DELETE FROM `producer`");
    $sql->execute();
    $sql = $conn->prepare("DELETE FROM `skedge`");
    $sql->execute();

    header("Location: /index.php/skedge/user/");

?>
