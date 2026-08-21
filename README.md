# testdb.lc

Каталог недвижимости с админ-панелью (jqGrid), лендингом на Smarty и MySQL.

- **Публичный сайт:** `/`
- **Админка:** `/admin/` (логин `admin`, пароль `admin` после импорта SQL)

---

## Быстрый старт через Docker (сервер или локально)

Требования: [Docker](https://docs.docker.com/get-docker/) и Docker Compose v2.

```bash
git clone <repo-url> testdb.lc
cd testdb.lc

cp .env.example .env
# при необходимости отредактируйте .env (порт, пароли)

docker compose up -d --build
```

Откройте в браузере:

- сайт: http://localhost:8080/
- админка: http://localhost:8080/admin/

При **первом** запуске MySQL автоматически импортирует `sql/testdb.sql` (только если volume БД пустой).

### Полезные команды Docker

```bash
# логи
docker compose logs -f web

# остановка
docker compose down

# остановка и удаление БД (полный сброс данных)
docker compose down -v

# пересборка после изменений кода
docker compose up -d --build
```

### Деплой на сервер

1. Скопируйте проект на сервер.
2. Настройте `.env` (смените `DB_PASSWORD`, `MYSQL_ROOT_PASSWORD`, `AESKEY` на продакшене).
3. Запустите `docker compose up -d --build`.
4. Пробросьте порт `APP_PORT` через nginx/caddy или измените на `80:80` в `docker-compose.yml`.
5. Каталог `img/` монтируется как volume — загруженные файлы сохраняются между перезапусками.

---

## Локальный запуск без Docker

Подходит для любой ОС (Linux, macOS, Windows), если установлены нужные компоненты.

### Требования

| Компонент | Версия | Расширения PHP |
|---|---|---|
| PHP | 7.4+ | `mysqli`, `mbstring` |
| MySQL | 8.x | — |
| Composer | 2.x | — |
| Веб-сервер | Apache или nginx | mod_rewrite / try_files |

Проверка PHP:

```bash
php -v
php -m | grep -E 'mysqli|mbstring'
composer -V
mysql --version
```

### 1. Клонирование и зависимости

```bash
git clone <repo-url> testdb.lc
cd testdb.lc

composer install
mkdir -p templates_c img
chmod 755 templates_c img
```

### 2. База данных

Создайте пользователя и базу (выполните от имени администратора MySQL):

```sql
CREATE DATABASE IF NOT EXISTS testdb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'testdb'@'localhost' IDENTIFIED BY 'testdb_local_2026';
GRANT ALL PRIVILEGES ON testdb.* TO 'testdb'@'localhost';
FLUSH PRIVILEGES;
```

Импорт дампа:

```bash
mysql -u testdb -p testdb < sql/testdb.sql
```

Пароль по умолчанию для локальной разработки: `testdb_local_2026` (см. `php/config.php`).

Если MySQL на другом хосте или с другими учётными данными — задайте переменные окружения (см. таблицу ниже) или отредактируйте значения по умолчанию в `php/config.php`.

### 3. Веб-сервер

Корень сайта (DocumentRoot) — **каталог проекта**, где лежат `index.php` и `.htaccess`.

#### Apache

- Включите `mod_rewrite`, `AllowOverride All` для каталога проекта.
- Пример vhost: `apache/testdb.lc.conf` (адаптируйте пути и модуль PHP под вашу систему).
- Добавьте в `hosts` при необходимости: `127.0.0.1 testdb.lc`

#### nginx

Минимальный пример `server` (PHP-FPM на `127.0.0.1:9000`):

```nginx
server {
    listen 8080;
    server_name localhost;
    root /path/to/testdb.lc;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_pass 127.0.0.1:9000;
    }

    location ~ ^/(vendor|templates_c|sql)/ {
        deny all;
    }
}
```

#### Быстрая проверка (встроенный сервер PHP)

Только для разработки; `.htaccess` не обрабатывается:

```bash
php -S localhost:8080 -t .
```

Откройте http://localhost:8080/ и http://localhost:8080/admin/

### 4. Проверка

- Лендинг: http://localhost:8080/
- Админка: http://localhost:8080/admin/
- Логин: `admin` / пароль: `admin`

---

## Переменные окружения

| Переменная | Docker (`.env`) | Локально (по умолчанию) |
|---|---|---|
| `DB_HOST` | `db` | `localhost` |
| `DB_USER` | `testdb` | `testdb` |
| `DB_PASSWORD` | `testdb` | `testdb_local_2026` |
| `DB_NAME` | `testdb` | `testdb` |
| `AESKEY` | из `.env` | `aes_some_key_to_testdb777` |

`php/config.php` использует переменные окружения, если они заданы. Иначе — значения из таблицы «Локально».

Пример для локального запуска с нестандартной БД (Linux/macOS):

```bash
export DB_HOST=127.0.0.1
export DB_USER=testdb
export DB_PASSWORD=testdb_local_2026
export DB_NAME=testdb
php -S localhost:8080 -t .
```

---

## Структура проекта

| Путь | Назначение |
|---|---|
| `index.php` | Лендинг (Smarty) |
| `admin/` | Админ-панель |
| `php/config.php` | Настройки БД и AES |
| `sql/testdb.sql` | Дамп схемы и тестовых данных |
| `Dockerfile` | Образ PHP 7.4 + Apache |
| `docker-compose.yml` | Web + MySQL |
| `templates/` | Smarty-шаблоны |

---

## Зависимости (Composer)

- `smarty/smarty` — шаблонизатор лендинга
- `nadar/quill-delta-parser` — HTML из Quill-описаний

В Docker зависимости ставятся при сборке образа. Локально: `composer install`.
