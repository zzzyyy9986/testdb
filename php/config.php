<?
    /**
     * Локально используются значения по умолчанию.
     * В Docker переменные задаются через docker-compose.yml / .env
     */
    define("HOSTNAME", getenv("DB_HOST") !== false && getenv("DB_HOST") !== "" ? getenv("DB_HOST") : "localhost");
    define("USERNAME", getenv("DB_USER") !== false && getenv("DB_USER") !== "" ? getenv("DB_USER") : "testdb");
    define("PASSWORD", getenv("DB_PASSWORD") !== false ? getenv("DB_PASSWORD") : "testdb_local_2026");
    define("DBNAME", getenv("DB_NAME") !== false && getenv("DB_NAME") !== "" ? getenv("DB_NAME") : "testdb");
    define("AESKEY", getenv("AESKEY") !== false && getenv("AESKEY") !== "" ? getenv("AESKEY") : "aes_some_key_to_testdb777");

    $mysqli = null;

    mb_internal_encoding("UTF-8");
    date_default_timezone_set("Europe/Moscow");
