<?php
    include $_SERVER["DOCUMENT_ROOT"] . "/php/config.php";
    include $_SERVER["DOCUMENT_ROOT"] . "/php/functions.php";
    include $_SERVER["DOCUMENT_ROOT"] . "/admin/php/functions.admin.php";

    admin_require_auth();
    db_connect();

    $allowedExt = ["jpg", "jpeg", "png", "gif", "webp", "pdf", "doc", "docx"];
    $blacklist = [".php", ".phtml", ".php3", ".php4", ".php5", ".htaccess"];

    $baseFileName = "";
    if (isset($_FILES["files"])) {
        $baseFileName = $_FILES["files"]["name"][0];
    } elseif (isset($_FILES[0]["name"])) {
        $baseFileName = $_FILES[0]["name"];
    } else {
        http_response_code(400);
        die("No file");
    }

    foreach ($blacklist as $item) {
        if (preg_match("/" . preg_quote($item, "/") . "$/i", $baseFileName)) {
            http_response_code(400);
            die("Error: disallowed file type");
        }
    }

    $baseTmpName = "";
    if (isset($_FILES["files"])) {
        $baseTmpName = $_FILES["files"]["tmp_name"][0];
        $extSource = $_FILES["files"]["name"][0];
    } else {
        $baseTmpName = $_FILES[0]["tmp_name"];
        $extSource = $_FILES[0]["name"];
    }

    $extParts = explode(".", $extSource);
    $ext = strtolower($extParts[count($extParts) - 1]);
    if (!in_array($ext, $allowedExt, true)) {
        http_response_code(400);
        die("Error: disallowed extension");
    }

    $tbl = isset($_REQUEST["table"]) ? $_REQUEST["table"] : "";
    if (!is_allowed_admin_table($tbl)) {
        http_response_code(400);
        die("Invalid table");
    }

    $col = isset($_REQUEST["col"]) ? preg_replace('/[^A-Z0-9_]/', '', $_REQUEST["col"]) : "";
    $dir = strtolower(str_replace("NL_", "", $tbl));
    $date = date("ymd_his", time());
    $id = isset($_REQUEST["id"]) ? db_int($_REQUEST["id"]) : 0;

    $uploadDir = $_SERVER["DOCUMENT_ROOT"] . "/img/" . $dir;
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $filename = "/img/" . $dir . "/" . $col . "_" . $id . "_" . $date . "." . $ext;
    $uploadfile = $_SERVER["DOCUMENT_ROOT"] . $filename;

    if (is_uploaded_file($baseTmpName) && move_uploaded_file($baseTmpName, $uploadfile)) {
        echo '"' . $filename . '"';
    } else {
        http_response_code(500);
        die("Error: File uploading failed.");
    }

    db_disconnect();
