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