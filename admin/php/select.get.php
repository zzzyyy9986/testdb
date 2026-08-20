<?php
    include $_SERVER["DOCUMENT_ROOT"] . "/php/config.php";
    include $_SERVER["DOCUMENT_ROOT"] . "/php/functions.php";
    include $_SERVER["DOCUMENT_ROOT"] . "/admin/php/functions.admin.php";

    admin_require_auth();
    db_connect();

    $tblParent = isset($_POST["tblParent"]) ? $_POST["tblParent"] : "";
    $tblChild = isset($_POST["tblChild"]) ? $_POST["tblChild"] : "";
    $idParent = isset($_POST["idParent"]) ? db_int($_POST["idParent"]) : 0;

    if (!is_allowed_admin_table($tblParent) || !is_allowed_admin_table($tblChild)) {
        http_response_code(400);
        die("Invalid table");
    }

    $options = "";
    $query = "SELECT * FROM " . $tblChild . " WHERE ID_" . $tblParent . " = " . $idParent;
    $res = db_query($query);
    if ($res) {
        while ($row = db_fetch_assoc($res)) {
            $id = (int)$row["ID_" . $tblChild];
            $label = htmlspecialchars($row[$tblChild . "_SHORT"], ENT_QUOTES, "UTF-8");
            $options .= '<option value="' . $id . '">' . $label . '</option>';
        }
    }

    echo $options;

    db_disconnect();
