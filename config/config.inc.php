<?php

/*
 * MySQL server and database settings
 */

define("MYSQL_HOST", "localhost");
define("MYSQL_USER", "");
define("MYSQL_PASSWORD", "");
define("MYSQL_DATABASE", "");


/*
 * Database table names
 */

// Table names
define("DBT_USERS", "users");
define("DBT_DOMAINS", "domains");
define("DBT_ALIASES", "aliases");

// Users table columns
define("DBC_USERS_ID", "id");
define("DBC_USERS_USERNAME", "username");
define("DBC_USERS_DOMAIN", "domain");
define("DBC_USERS_PASSWORD", "password");
define("DBC_USERS_MAILBOXLIMIT", "mailbox_limit");

// Domains table columns
define("DBC_DOMAINS_ID", "id");
define("DBC_DOMAINS_DOMAIN", "domain");

// Aliases table columns
define("DBC_ALIASES_ID", "id");
define("DBC_ALIASES_SOURCE", "source");
define("DBC_ALIASES_DESTINATION", "destination");


/*
 * Frontend paths
 */

define("FRONTEND_BASE_PATH", "http://localhost/webmum/");
define("SUBDIR", "/webmum/");


/*
 * Admin e-mail address
 */

define("ADMIN_EMAIL", "admin@server.tld");


/*
 * Minimal password length
 */

define("MIN_PASS_LENGTH", 8);

?>