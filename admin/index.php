<?
    include $_SERVER["DOCUMENT_ROOT"] . "/php/config.php";
    include $_SERVER["DOCUMENT_ROOT"] . "/php/functions.php";
    include $_SERVER["DOCUMENT_ROOT"] . "/admin/php/functions.admin.php";
    
    db_connect();
    if ((isset($_POST["login"])) && (isset($_POST["password"]))) {
        user_auth($_POST["login"], $_POST["password"]);
        header("Location: /admin/", true, 303);
        die();
    }
    if ((!isset($_SESSION["ID_NL_USER"]) || (!isset($_SESSION["ID_NL_USER_PERMISSION"]))) && ($url[0] != "/admin/login/")) {
        header("Location: /admin/login/", true, 303);
        die();
    }
?>
<!doctype html>
<html lang="ru" class="html-<?= $page ?>">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Административная панель</title>
    <meta name="viewport" content="width=700, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="/admin/css/normalize.min.css">
    <link rel="stylesheet" href="/admin/css/jquery-ui.min.css">
    <link rel="stylesheet" href="/admin/css/ui.jqgrid.min.css">
    <link rel="stylesheet" href="/admin/css/chosen.min.css">
    <link rel="stylesheet" href="/admin/css/jquery.datetimepicker.min.css">
    <link rel="stylesheet" href="/admin/css/jquery.arcticmodal.min.css">
    <link rel="stylesheet" href="/admin/css/quill.snow.min.css">
    <link rel="stylesheet" href="/admin/css/admin.min.css">
    <link rel="stylesheet" href="/admin/css/print.min.css">
    <script src="/admin/js/vendor/jquery-1.12.4.min.js"></script>
    <script src="/admin/js/vendor/jquery.json.min.js"></script>
    <script src="/admin/js/vendor/jquery-ui.min.js"></script>
    <script src="/admin/js/vendor/jquery.jqGrid.min.js"></script>
    <script src="/admin/js/vendor/grid.locale-ru.min.js"></script>
    <script src="/admin/js/vendor/jquery.cookie.min.js"></script>
    <script src="/admin/js/vendor/chosen.jquery.min.js"></script>
    <script src="/admin/js/vendor/jquery.datetimepicker.full.min.js"></script>
    <script src="/admin/js/vendor/jquery.arcticmodal.min.js"></script>
    <script src="/admin/js/vendor/quill.js"></script>
    <script src="/admin/js/fileapi/FileAPI.min.js"></script>
    <script src="/admin/js/fileapi/FileAPI.exif.js"></script>
    <script src="/admin/js/fileapi/jquery.fileapi.min.js"></script>
    <script src="/admin/js/vendor/FileSaver.min.js"></script>
    <script src="/admin/js/admin.js"></script>
</head>
<body class="admin admin-<?= $page ?>">
    <? includeAdminPartsByLvl(); ?>
    <script src="//api-maps.yandex.ru/2.1/?lang=ru_RU"></script>
</body>
</html>
<? db_disconnect(); ?>