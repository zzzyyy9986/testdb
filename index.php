<?
    require_once __DIR__ . "/vendor/autoload.php";

    include __DIR__ . "/php/config.php";
    include __DIR__ . "/php/functions.php";

    db_connect();

    $filterHouse = isset($_GET["house"]) ? db_int($_GET["house"]) : 0;
    $filterMaterial = isset($_GET["material"]) ? db_int($_GET["material"]) : 0;

    $houseTypes = [];
    $resHouseTypes = db_query(
        "SELECT ID_NL_HOUSES, NL_HOUSES_SHORT FROM NL_HOUSES ORDER BY NL_HOUSES_SHORT"
    );
    if ($resHouseTypes) {
        while ($row = db_fetch_assoc($resHouseTypes)) {
            $id = (int)$row["ID_NL_HOUSES"];
            $row["filter_url"] = landing_toggle_filter($filterHouse, $filterMaterial, "house", $id);
            $row["is_active"] = ($filterHouse === $id);
            $houseTypes[] = $row;
        }
    }

    $materials = [];
    $resMaterials = db_query(
        "SELECT ID_NL_MATERIAL, NL_MATERIAL_SHORT FROM NL_MATERIAL ORDER BY NL_MATERIAL_SHORT"
    );
    if ($resMaterials) {
        while ($row = db_fetch_assoc($resMaterials)) {
            $id = (int)$row["ID_NL_MATERIAL"];
            $row["filter_url"] = landing_toggle_filter($filterHouse, $filterMaterial, "material", $id);
            $row["is_active"] = ($filterMaterial === $id);
            $materials[] = $row;
        }
    }

    $houses = [];
    $resHouses = db_query(
        "SELECT h.ID_NL_HOUSES, h.NL_HOUSES_SHORT, h.NL_HOUSES_FULL,
                h.NL_HOUSES_FLOORS, h.NL_HOUSES_YEAR, m.NL_MATERIAL_SHORT
         FROM NL_HOUSES h
         LEFT JOIN NL_MATERIAL m ON m.ID_NL_MATERIAL = h.ID_NL_MATERIAL
         ORDER BY h.ID_NL_HOUSES"
    );
    if ($resHouses) {
        while ($row = db_fetch_assoc($resHouses)) {
            $houses[] = $row;
        }
    }

    $aptWhere = ["1=1"];
    if ($filterHouse > 0) {
        $aptWhere[] = "p.ID_NL_HOUSES = " . $filterHouse;
    }
    if ($filterMaterial > 0) {
        $aptWhere[] = "p.ID_NL_MATERIAL = " . $filterMaterial;
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
         WHERE " . implode(" AND ", $aptWhere) . "
         ORDER BY p.ID_NL_PROP_RESALE"
    );
    if ($resApts) {
        while ($row = db_fetch_assoc($resApts)) {
            $row["description_html"] = parse_quill_description($row["NL_PROP_RESALE_DESCRIPTION"]);
            $apartments[] = $row;
        }
    }

    $hasFilters = ($filterHouse > 0 || $filterMaterial > 0);

    $compileDir = __DIR__ . "/templates_c";
    if (!is_dir($compileDir)) {
        mkdir($compileDir, 0755, true);
    }

    $smarty = new Smarty();
    $smarty->setTemplateDir(__DIR__ . "/templates");
    $smarty->setCompileDir($compileDir);
    $smarty->assign("houses", $houses);
    $smarty->assign("apartments", $apartments);
    $smarty->assign("houseTypes", $houseTypes);
    $smarty->assign("materials", $materials);
    $smarty->assign("hasFilters", $hasFilters);
    $smarty->assign("filterResetUrl", "/");
    $smarty->display("landing.tpl");

    db_disconnect();
