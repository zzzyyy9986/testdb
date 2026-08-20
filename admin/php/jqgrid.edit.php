<?php
    include $_SERVER["DOCUMENT_ROOT"] . "/php/config.php";
    include $_SERVER["DOCUMENT_ROOT"] . "/php/functions.php";
    include $_SERVER["DOCUMENT_ROOT"] . "/admin/php/functions.admin.php";

    admin_require_auth();
    db_connect();

    $tblName = isset($_GET["tblName"]) ? $_GET["tblName"] : "";
    if (!is_allowed_admin_table($tblName)) {
        http_response_code(400);
        die("Invalid table");
    }

    $oper = isset($_POST["oper"]) ? $_POST["oper"] : "";
    if (!in_array($oper, ["add", "edit", "del"], true)) {
        http_response_code(400);
        die("Invalid operation");
    }

    $table = new ObjectTable($tblName);

    $id = isset($_POST["ID_" . $tblName]) ? $_POST["ID_" . $tblName] : 0;
    if ($oper == "del") {
        $id = isset($_POST["id"]) ? $_POST["id"] : 0;
    }

    $table->saveData($oper, $id, $_POST);

    db_disconnect();
?>