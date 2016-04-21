WebMUM - Web Mailserver User Manager
======

***WebMUM is not compatible with the [new Mailserver-HowTo](https://thomas-leister.de/allgemein/sicherer-mailserver-dovecot-postfix-virtuellen-benutzern-mysql-ubuntu-server-xenial/)!***

WebMUM is a web frontend based on PHP which helps you to manage mail accounts via MySQL. This software is licensed under the MIT license. 


## Installation

Clone the WebMUM Repository to your webserver's virtual host root directory:

```php
git clone https://github.com/ThomasLeister/webmum
```
	
Now configure your webserver. URL rewriting to index.php is required.

### Nginx

Nginx config examples following, but you still need to change domain and path in config as explained in WebMum Config -> Paths.

With subdirectory `webmum/` in URL (e.g. `http://mydomain.tld/webmum/`):

```nginx
server {
    listen       80;
    server_name  mydomain.tld;

    root /var/www;
    index index.html index.php;

    location ~ \.php$ {
        fastcgi_pass   127.0.0.1:9000;
        fastcgi_index  index.php;
        fastcgi_param  SCRIPT_FILENAME  $document_root$fastcgi_script_name;
        include        fastcgi_params;
    }

    location /webmum {
            try_files $uri $uri/ /webmum/index.php?$args;
    }
}
```

Without subdirectory in URL (e.g. `http://webmum.mydomain.tld/`):

```nginx
server {
    listen       80;
    server_name  webmum.mydomain.tld;

    root /var/www/webmum;
    index index.html index.php;

    location ~ \.php$ {
        fastcgi_pass   127.0.0.1:9000;
        fastcgi_index  index.php;
        fastcgi_param  SCRIPT_FILENAME  $document_root$fastcgi_script_name;
        include        fastcgi_params;
    }

    location / {
            try_files $uri $uri/ /index.php?$args;
    }
}
```

### Apache 

Apache config examples following, but you still need to change domain and path in config as explained in WebMum Config -> Paths.

Please note: mod_rewrite must be enabled for URL rewriting:

```bash
sudo a2enmod rewrite
```

With subdirectory `webmum/` in URL (e.g. `http://mydomain.tld/webmum/`):

```apache
<VirtualHost *:80>
    ServerName domain.tld
    DocumentRoot /var/www/domain.tld

	RewriteEngine on
	RewriteCond %{REQUEST_FILENAME} !-d
	RewriteCond %{REQUEST_FILENAME} !-f
	RewriteRule ^\/webmum/(.*)\.css$ /webmum/$1.css [L]
	RewriteRule ^\/webmum/(.*)$ /webmum/index.php [L,QSA]
</VirtualHost>
```

Without subdirectory in URL (e.g. `http://webmum.mydomain.tld/`):

```apache
<VirtualHost *:80>
    ServerName webmum.domain.tld
    DocumentRoot /var/www/domain.tld/webmum

	RewriteEngine on
	RewriteCond %{REQUEST_FILENAME} !-d
	RewriteCond %{REQUEST_FILENAME} !-f
	RewriteRule (.*)\.css$ $1.css [L]
	RewriteRule ^(.*)$ /index.php [L,QSA]
</VirtualHost>
```

## WebMUM Configuration

Configure WebMUM via the configuration file at `config/config.inc.php`. 

### MySQL

At first the database access has to be configured.

```php
/*
 * MySQL server and database settings
 */

define("MYSQL_HOST", "localhost");
define("MYSQL_USER", "vmail");
define("MYSQL_PASSWORD", "vmail");
define("MYSQL_DATABASE", "vmail");
```

... then define the table names according to your own setup:

```php
/*
 * Database table names
 */

// Table names
define("DBT_USERS", "users");
define("DBT_DOMAINS", "domains");
define("DBT_ALIASES", "aliases");
```

... and finally the table column names:

```php
// Users table columns
define("DBC_USERS_ID", "id");
define("DBC_USERS_USERNAME", "username");
define("DBC_USERS_DOMAIN", "domain");
define("DBC_USERS_PASSWORD", "password");
//define("DBC_USERS_MAILBOXLIMIT", "mailbox_limit");


// Domains table columns
define("DBC_DOMAINS_ID", "id");
define("DBC_DOMAINS_DOMAIN", "domain");

// Aliases table columns
define("DBC_ALIASES_ID", "id");
define("DBC_ALIASES_SOURCE", "source");
define("DBC_ALIASES_DESTINATION", "destination");
//define("DBC_ALIASES_MULTI_SOURCE", "multi_source");
```

### Mailbox limit
If you have a "mailbox_limit" column to limit the size of your users' mailboxes, just comment in the line

```php
define("DBC_USERS_MAILBOXLIMIT", "mailbox_limit");
```

in your configuration. WebMUM will then show a new field "Mailbox limit" in the frontend.


### Multiple source redirect support

As mailservers can only process a single source address for redirects the database table for aliases / redirects can only hold a single source address in a row.
WebMum will, if you enabled the multiple source redirect support, do some magic so there is only a single address in a row even though multiple adresses where entered.
To make this work another column in the database table is required, which holds an identifier for the list of source adresses, so they can be edited like normal redirects.

By default you can only redirect a single address to a single or multiple destinations.
If you want to enable support for redirecting multiple source adresses to a destionation, just comment in the line

```php
define("DBC_ALIASES_MULTI_SOURCE", "multi_source");
```

in your configuration. And add the following column to your database table for aliases / redirects:

```sql
ALTER TABLE `aliases` ADD COLUMN `multi_source` VARCHAR(32) NULL DEFAULT NULL;
```

WebMUM will then show a larger field for source addresses in the frontend and you can not list emails in source field.

### Paths
(#path-config)

Define the URL of the web application, and it's subfolder:

```php
/*
 * Frontend paths
 */

define("FRONTEND_BASE_PATH", "http://mydomain.tld/webmum/");
define("SUBDIR", "webmum/");
```

In the example above, WebMUM is located in a subfolder named "webmum/". If you don't want to use a subfolder, but install WebMUM directly into the domain root,
set the settings like this:

```php
define("FRONTEND_BASE_PATH", "http://webmum.mydomain.tld/");
define("SUBDIR", "");
```

### Admin e-mail address

Only users with one of the specified email addresses will have access to the administrator's dashboard and will be able to create, edit and delete users, domains and redirects.

```php
/*
 * Admin e-mail address
 */

$admins = array("admin@domain.tld");
```

Admin email accounts must exist in the virtual user database on your own server. (=> an e-mail account on a foreign server won't give you access!). You can then login into the admin dashboard with that e-mail address and the corresponding password.

### Minimal required password length

```php
/*
 * Minimal password length
 */

define("MIN_PASS_LENGTH", 8);
```

### Logfile

When logging is enabled, WebMUM will write messages into a file "webmum.log" in a specified directory (e.g. when a login attempt fails).

To enable logging, comment in the lines

```php
# define("WRITE_LOG", true);
# define("WRITE_LOG_PATH","/var/www/webmum/log/");
```

... and make sure that PHP has permissions to write the log file to the directory defined in WRITE_LOG_PATH.

"Login-failed-messages" have the following scheme:

```
Dec 19 13:00:19: WebMUM login failed for IP 127.0.0.1
```

#### Fail2Ban support

If you want to use **Fail2Ban** with WebMUM, the filter has to be:

```
[Definition]
failregex = ^(.*)\: WebMUM login failed for IP <HOST>$
```

### General options

To **restrict source adresses to managed domains only**, which is totally optional but recommended, just uncomment the following line

```php
define("VALIDATE_ALIASES_SOURCE_DOMAIN_ENABLED", true);
```


### Frontend options

Choose delimiter beteween multiple email adresses: comma, semicolon or new line separated.

**Tip:** new line is helpfull for long lists of addresses.

```php
define("FRONTEND_EMAIL_SEPARATOR_TEXT", ', '); // possible values: ', ' (default), '; ', PHP_EOL (newline)
define("FRONTEND_EMAIL_SEPARATOR_FORM", ','); // possible values: ',' (default), ';', PHP_EOL (newline)
```

The input for addresses can be separated by `,`, `;`, `:`, `|`, `newline` and combinations since all of them will result in a valid list of adresses in database, magic.


## Update / Upgrade WebMUM

If you cloned WebMUM into your filesystem via `git clone https://github.com/ThomasLeister/webmum`:

```bash
git stash
git pull origin master
git stash pop
```
	
... and you are ready to go. Git might complain about conflicting files - you will have to resolve the merge conflict manually then.

If you downloaded WebMUM as a ZIP package, you have to update WebMUM manually.

**After every update:** 
Please check if your config.inc.php fits the current requirements by comparing your version of the file with the config.inc.php in the repository.

## FAQ

### Which password scheme does WebMUM use?

By default WebMUM uses SHA512-CRYPT password scheme. It cloud be change in the config file to SHA256-CRYPT or BLOWFISH-CRYPT.

```php
 /*
 * Select one of the following algorithms
 * SHA-512, SHA-256, BLOWFISH
 */
define("PASS_HASH_SCHEMA", "SHA-512");
```

### "login/ cannot be found"

Webserver rewrites have to be enabled on your server, because WebMUM does not use real URLs for the frontend, but virtual URLs based on URL rewriting.
When rewriting fails, you receive a 404 error message.
