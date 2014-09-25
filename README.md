WebMUM - Web Mailserver User Manager
======

WebMUM is a web frontend based on PHP which helps you to manage mail accounts via MySQL. The software is licensed under a GNU-GPL 3.0 license.

## Demo

There is a demo site available at https://webmumdemo.trashserver.net/

Username: admin@domain.tld
Password: webmumpassword

Please note that there are some limitations: You cannot change the password for the admin user or delete his account / domain. Have fun!

## Installing

[Download the ZIP archive](https://github.com/ThomasLeister/webmum/archive/master.zip) and extract it into your webserver's virtual host root directory:

	wget https://github.com/ThomasLeister/webmum/archive/master.zip<br/>
	unzip master.zip
	mv master/ webmum/
	
Configure your webserver. URL rewriting is required.

For Nginx (webmum is located in subdirectory "webmum/"):

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

Without "webmum/" subdirectory in URL:

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

## Configuring

Configure WebMUM via the configuration file at "config/config.inc.php". 

### MySQL

At first the database access has to be configured.

	/*
	 * MySQL server and database settings
	 */
	
	define("MYSQL_HOST", "localhost");
	define("MYSQL_USER", "vmail");
	define("MYSQL_PASSWORD", "vmail");
	define("MYSQL_DATABASE", "vmail");

... then define the table names according to your own setup:

	/*
	 * Database table names
	 */
	
	// Table names
	define("DBT_USERS", "users");
	define("DBT_DOMAINS", "domains");
	define("DBT_ALIASES", "aliases");

... and finally the table column names:

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

### Paths

Define the URL of the web application, and it's subfolder:

	/*
	 * Frontend paths
	 */
	
	define("FRONTEND_BASE_PATH", "http://localhost/webmum/");
	define("SUBDIR", "webmum/");

In the example above, WebMUM is located in a subfolder named "webmum/". If you don't want to use a subfolder, but install WebMUM directly into the domain root,
set the settings like this:

	define("FRONTEND_BASE_PATH", "http://localhost/");
	define("SUBDIR", "");

### Admin e-mail address

Only the user with this specific e-mail address will have access to the administrator's dashboard and will be able to create, edit and delete users, domains and redirects.

	/*
	 * Admin e-mail address
	 */
	
	define("ADMIN_EMAIL", "admin@domain.tld");

### Minimal required password length

	/*
	 * Minimal password length
	 */
	
	define("MIN_PASS_LENGTH", 8);

## Which password scheme does WebMUM use?

WebMUM uses the SHA512-CRYPT password scheme, which is known as a very secure scheme these days. Support for more password schemes will be added soon.

