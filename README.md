WebMUM - Web Mailserver User Manager
======

WebMUM is a web frontend based on PHP which helps you to manage mail accounts via MySQL. The software is licensed under a GNU-GPL 3.0 license.

## Installing

[Download the ZIP archive](https://github.com/ThomasLeister/webmum/archive/master.zip) and extract it into your webserver's virtual host root directory:

<code>
	wget https://github.com/ThomasLeister/webmum/archive/master.zip<br/>
	unzip master.zip
	mv master/ webmum/
</code>

Configure your webserver. URL rewriting is required.

For Nginx (webmum is located in subdirectory "webmum/"):

<code>
	server {
        listen       80;
        server_name  mydomain.tld;

        root /var/www;
        index index.html index.php;

        location ~ \.php$ {
            fastcgi_pass   127.0.0.1:9000;
            fastcgi_index  index.php;
            include        fastcgi.conf;
            fastcgi_param  SCRIPT_FILENAME  $document_root$fastcgi_script_name;
            include        fastcgi_params;
        }

        location /webmum {
                try_files $uri $uri/ /webmum/index.php?$args;
        }
    }
</code>

Without "webmum/" subdirectory in URL:

<code>
	server {
        listen       80;
        server_name  webmum.mydomain.tld;

        root /var/www/webmum;
        index index.html index.php;

        location ~ \.php$ {
            fastcgi_pass   127.0.0.1:9000;
            fastcgi_index  index.php;
            include        fastcgi.conf;
            fastcgi_param  SCRIPT_FILENAME  $document_root$fastcgi_script_name;
            include        fastcgi_params;
        }

        location / {
                try_files $uri $uri/ /index.php?$args;
        }
    }
</code>

## Configuring

Configure WebMUM via the configuration file at "config/config.inc.php". 

### MySQL

At first the database access has to be configured.

<code>
	/*
	 * MySQL server and database settings
	 */
	
	define("MYSQL_HOST", "localhost");
	define("MYSQL_USER", "vmail");
	define("MYSQL_PASSWORD", "vmail");
	define("MYSQL_DATABASE", "vmail");
</code>

... then define the table names according to your own setup:

<code>
	/*
	 * Database table names
	 */
	
	// Table names
	define("DBT_USERS", "users");
	define("DBT_DOMAINS", "domains");
	define("DBT_ALIASES", "aliases");
</code>

... and finally the table column names:

<code>
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
</code>

### Paths

Define the URL of the web application, and it's subfolder:

<code>
	/*
	 * Frontend paths
	 */
	
	define("FRONTEND_BASE_PATH", "http://localhost/webmum/");
	define("SUBDIR", "/webmum/");
</code>

In the example above, WebMUM is located in a subfolder named "webmum/". If you don't want to use a subfolder, but install WebMUM directly into the domain root,
set the settings like this:

<code>
	define("FRONTEND_BASE_PATH", "http://localhost/");
	define("SUBDIR", "/");
</code>

### Admin e-mail address

Only the user with this specific e-mail address will have access to the administrator's dashboard and will be able to create, edit and delete users, domains and redirects.

<code>
	/*
	 * Admin e-mail address
	 */
	
	define("ADMIN_EMAIL", "admin@domain.tld");
</code>

### Minimal required password length

<code>
	/*
	 * Minimal password length
	 */
	
	define("MIN_PASS_LENGTH", 8);
</code>

## Which password scheme does WebMUM use?

WebMUM uses the SHA512-CRYPT password scheme, which is known as a very secure scheme these days. Support for more password schemes will be added soon.

