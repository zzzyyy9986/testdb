<?php
    include $_SERVER["DOCUMENT_ROOT"] . "/php/config.php";
    include $_SERVER["DOCUMENT_ROOT"] . "/php/functions.php";
    include $_SERVER["DOCUMENT_ROOT"] . "/admin/php/functions.admin.php";

    admin_require_auth();
    admin_require_post_header();
    db_connect();

    $tbl = isset($_REQUEST["table"]) ? $_REQUEST["table"] : "";
    if (!is_allowed_admin_table($tbl)) {
        http_response_code(400);
        die("Invalid table");
    }

    $tblEsc = db_real_escape_string($tbl);
    $query = "SELECT AUTO_INCREMENT FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '" . DBNAME . "' AND TABLE_NAME = '" . $tblEsc . "'";
    $res = db_query($query);
    $row = db_fetch_assoc($res);
    $id = isset($row["AUTO_INCREMENT"]) ? (int)$row["AUTO_INCREMENT"] : 1;

    $query = "ALTER TABLE " . $tbl . " AUTO_INCREMENT = " . ($id + 1);
    db_query($query);

    echo $id;

    db_disconnect();
