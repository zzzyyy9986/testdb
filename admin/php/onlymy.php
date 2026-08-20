<?php
    include $_SERVER["DOCUMENT_ROOT"] . "/php/config.php";
    include $_SERVER["DOCUMENT_ROOT"] . "/php/functions.php";
    include $_SERVER["DOCUMENT_ROOT"] . "/admin/php/functions.admin.php";

    admin_require_auth();
    db_connect();

    $_SESSION["onlymy"] = isset($_POST["onlymy"]) && $_POST["onlymy"] === "true" ? "true" : "false";

    db_disconnect();
