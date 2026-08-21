<?
    /* ==========================================================================
       DATABASE FUNCTIONS
       ========================================================================== */

    function db_connect() {
        global $mysqli;

        $mysqli = new mysqli(HOSTNAME, USERNAME, PASSWORD, DBNAME);

        if ($mysqli->connect_errno) {
            printf("Ошибка подключения к базе: %s\n", $mysqli->connect_error);
            exit();
        }

        if (!$mysqli->set_charset("utf8")) {
            printf("Ошибка загрузки кодировки utf8: %s\n", $mysqli->error);
            printf("Текущая кодировка: %s\n", $mysqli->character_set_name());
        }
    }

    function db_disconnect() {
        global $mysqli;
        $mysqli->close();
    }

    function db_data_seek($res, $row_number) {
        return $res->data_seek($row_number);
    }

    function db_query($query) {
        global $mysqli;
        return $mysqli->query($query);
    }

    function db_fetch_assoc($res) {
        return $res->fetch_assoc();
    }

    function db_num_rows($res) {
        return $res->num_rows;
    }

    /**
     * Логирует ошибку SQL и возвращает безопасное сообщение для клиента.
     *
     * @param string $query Текст запроса (пишется только в error_log).
     * @return string
     */
    function db_error($query) {
        global $mysqli;
        error_log("DB error: " . $mysqli->error . " | Query: " . $query);
        return "Database error";
    }

    function db_real_escape_string($escapestr) {
        global $mysqli;
        return $mysqli->real_escape_string($escapestr);
    }

    /**
     * Экранирует значение для безопасной подстановки в SQL-запрос.
     *
     * @param mixed $value Строка, число или null.
     * @return string SQL-литерал: NULL, число или экранированная строка в кавычках.
     */
    function db_escape($value) {
        global $mysqli;
        if ($value === null) {
            return "NULL";
        }
        if (is_int($value) || is_float($value)) {
            return (string)$value;
        }
        return "'" . $mysqli->real_escape_string((string)$value) . "'";
    }

    /**
     * Приводит значение к целому числу для использования в SQL-запросах.
     *
     * @param mixed $value Входное значение.
     * @return int
     */
    function db_int($value) {
        return (int)$value;
    }

    /**
     * Проверяет, разрешена ли таблица для работы через админку.
     *
     * @param string $tableName Имя таблицы (например, NL_PROP_RESALE).
     * @return bool
     */
    function is_allowed_admin_table($tableName) {
        static $allowed = [
            "NL_USER",
            "NL_VIEW",
            "NL_MATERIAL",
            "NL_HOUSES",
            "NL_PROP_RESALE",
        ];
        return in_array($tableName, $allowed, true);
    }

    /**
     * Проверяет авторизацию пользователя в админке.
     * При отсутствии сессии возвращает HTTP 403 и завершает выполнение.
     *
     * @return void
     */
    function admin_require_auth() {
        if (!isset($_SESSION["ID_NL_USER"]) || !isset($_SESSION["ID_NL_USER_PERMISSION"])) {
            http_response_code(403);
            die("Forbidden");
        }
    }

    /**
     * Проверяет, что POST-запрос к admin API отправлен из админки (базовая CSRF-защита).
     *
     * @return void
     */
    function admin_require_post_header() {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            return;
        }
        $header = isset($_SERVER["HTTP_X_ADMIN_REQUEST"]) ? $_SERVER["HTTP_X_ADMIN_REQUEST"] : "";
        if ($header !== "1") {
            http_response_code(403);
            die("Forbidden");
        }
    }

    /**
     * Whitelist имён страниц админки для includeAdminPartsByLvl.
     *
     * @param string $page
     * @return string
     */
    function admin_sanitize_page($page) {
        $allowed = ["", "login", "main", "journals", "dicts"];
        $page = preg_replace('/[^a-z]/', '', strtolower($page));
        return in_array($page, $allowed, true) ? $page : "";
    }

    /**
     * @param mixed $value
     * @return float
     */
    function db_float($value) {
        return (float)$value;
    }

    /**
     * Собирает URL лендинга с параметрами фильтра.
     *
     * @param int $houseId    ID_NL_HOUSES или 0.
     * @param int $materialId ID_NL_MATERIAL или 0.
     * @return string Относительный URL (/ или ?house=…&material=…).
     */
    function landing_build_filter_url($houseId, $materialId) {
        $params = [];
        if ($houseId > 0) {
            $params["house"] = $houseId;
        }
        if ($materialId > 0) {
            $params["material"] = $materialId;
        }
        return $params ? ("?" . http_build_query($params)) : "/";
    }

    /**
     * Переключает один фильтр лендинга (повторный клик снимает фильтр).
     *
     * @param int    $currentHouse    Текущий ID_NL_HOUSES.
     * @param int    $currentMaterial Текущий ID_NL_MATERIAL.
     * @param string $type            house или material.
     * @param int    $id              ID выбранного значения.
     * @return string
     */
    function landing_toggle_filter($currentHouse, $currentMaterial, $type, $id) {
        $id = db_int($id);
        if ($type === "house") {
            $newHouse = ($currentHouse === $id) ? 0 : $id;
            return landing_build_filter_url($newHouse, $currentMaterial);
        }
        $newMaterial = ($currentMaterial === $id) ? 0 : $id;
        return landing_build_filter_url($currentHouse, $newMaterial);
    }

    /**
     * Преобразует закодированное Quill Delta-описание в HTML.
     *
     * @param string|null $raw URL-encoded JSON из поля NL_PROP_RESALE_DESCRIPTION.
     * @return string HTML-разметка или экранированный текст при ошибке парсинга.
     */
    function parse_quill_description($raw) {
        if ($raw === null || trim($raw) === "") {
            return "";
        }
        $json = urldecode($raw);
        if (!class_exists('\\nadar\\quill\\Lexer')) {
            return htmlspecialchars($json, ENT_QUOTES, "UTF-8");
        }
        try {
            $lexer = new \nadar\quill\Lexer($json);
            return $lexer->render();
        } catch (Exception $e) {
            return htmlspecialchars($json, ENT_QUOTES, "UTF-8");
        }
    }