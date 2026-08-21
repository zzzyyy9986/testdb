<?php
    require_once $_SERVER["DOCUMENT_ROOT"] . "/php/config.php";
    require_once $_SERVER["DOCUMENT_ROOT"] . "/php/functions.php";
    require_once $_SERVER["DOCUMENT_ROOT"] . "/admin/php/functions.admin.php";

    if (!isset($_SESSION["ID_NL_USER"]) || !isset($_SESSION["ID_NL_USER_PERMISSION"])) {
        http_response_code(403);
        die("Forbidden");
    }
