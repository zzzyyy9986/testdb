<?
    require_once __DIR__ . "/vendor/autoload.php";

    include __DIR__ . "/php/config.php";
    include __DIR__ . "/php/functions.php";

    db_connect();

    $houses = [];
    $resHouses = db_query(
        "SELECT h.ID_NL_HOUSES, h.NL_HOUSES_SHORT, m.NL_MATERIAL_SHORT
         FROM NL_HOUSES h
         LEFT JOIN NL_MATERIAL m ON m.ID_NL_MATERIAL = h.ID_NL_MATERIAL
         ORDER BY h.ID_NL_HOUSES"
    );
    if ($resHouses) {
        while ($row = db_fetch_assoc($resHouses)) {
            $houses[] = $row;
        }
    }

    $apartments = [];
    $resApts = db_query(
        "SELECT p.*,
                v.NL_VIEW_SHORT,
                h.NL_HOUSES_SHORT,
                m.NL_MATERIAL_SHORT
         FROM NL_PROP_RESALE p
         LEFT JOIN NL_VIEW v ON v.ID_NL_VIEW = p.ID_NL_VIEW
         LEFT JOIN NL_HOUSES h ON h.ID_NL_HOUSES = p.ID_NL_HOUSES
         LEFT JOIN NL_MATERIAL m ON m.ID_NL_MATERIAL = p.ID_NL_MATERIAL
         ORDER BY p.ID_NL_PROP_RESALE"
    );
    if ($resApts) {
        while ($row = db_fetch_assoc($resApts)) {
            $row["description_html"] = parse_quill_description($row["NL_PROP_RESALE_DESCRIPTION"]);
            $apartments[] = $row;
        }
    }

    $compileDir = __DIR__ . "/templates_c";
    if (!is_dir($compileDir)) {
        mkdir($compileDir, 0755, true);
    }

    $smarty = new Smarty();
    $smarty->setTemplateDir(__DIR__ . "/templates");
    $smarty->setCompileDir($compileDir);
    $smarty->assign("houses", $houses);
    $smarty->assign("apartments", $apartments);
    $smarty->display("landing.tpl");

    db_disconnect();
