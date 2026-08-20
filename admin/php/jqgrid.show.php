<?php
    include $_SERVER["DOCUMENT_ROOT"] . "/php/config.php";
    include $_SERVER["DOCUMENT_ROOT"] . "/php/functions.php";
    include $_SERVER["DOCUMENT_ROOT"] . "/admin/php/functions.admin.php";

    admin_require_auth();
    db_connect();

    $tblName = isset($_GET["tblName"]) ? $_GET["tblName"] : "";
    $table = new ObjectTable($tblName);

    echo $table->showData();

    db_disconnect();
?>