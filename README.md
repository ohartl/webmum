WebMUM - Web Mailserver User Manager
======

WebMUM is a web frontend based on PHP which helps you to manage mail accounts via MySQL. The software is licensed under a GNU-GPL 3.0 license.


## Installation

Clone the WebMUM Repository to your webserver's virtual host root directory:

	git clone https://github.com/ThomasLeister/webmum
	
Now configure your webserver. URL rewriting to index.php is required.

### Nginx

Example configuration for Nginx with subdirectory in URL (e.g. http://mydomain.tld/webmum/):

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

Without "webmum/" subdirectory in URL (e.g. http://webmum.mydomain.tld/):

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

### Apache 

Please note: mod_rewrite must be enabled for URL rewriting:

	sudo a2enmod rewrite	

With subdirectory "webmum/" (e.g. http://mydomain.tld/webmum/):

	<VirtualHost *:80>
	    ServerName domain.tld
	    DocumentRoot /var/www/domain.tld
	
		RewriteEngine on
		RewriteCond %{REQUEST_FILENAME} !-d
		RewriteCond %{REQUEST_FILENAME} !-f
		RewriteRule ^\/webmum/(.*)\.css$ /webmum/$1.css [L]
		RewriteRule ^\/webmum/(.*)$ /webmum/index.php [L,QSA]
	</VirtualHost>
	
Without subdirectory "webmum/" (e.g. http://webmum.mydomain.tld/):

	<VirtualHost *:80>
	    ServerName webmum.domain.tld
	    DocumentRoot /var/www/domain.tld/webmum
	
		RewriteEngine on
		RewriteCond %{REQUEST_FILENAME} !-d
		RewriteCond %{REQUEST_FILENAME} !-f
		RewriteRule (.*)\.css$ $1.css [L]
		RewriteRule ^(.*)$ /index.php [L,QSA]
	</VirtualHost>


## WebMUM Configuration

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

If you have a "mailbox_limit" column to limit the size of your users' mailboxes, just comment in the line

	define("DBC_USERS_MAILBOXLIMIT", "mailbox_limit");
	
in your configuration. WebMUM will then show a new field "Mailbox limit" in the frontend.

### Paths

Define the URL of the web application, and it's subfolder:

	/*
	 * Frontend paths
	 */
	
	define("FRONTEND_BASE_PATH", "http://mydomain.tld/webmum/");
	define("SUBDIR", "webmum/");

In the example above, WebMUM is located in a subfolder named "webmum/". If you don't want to use a subfolder, but install WebMUM directly into the domain root,
set the settings like this:

	define("FRONTEND_BASE_PATH", "http://webmum.mydomain.tld/");
	define("SUBDIR", "");

### Admin e-mail address

Only the user with this specific e-mail address will have access to the administrator's dashboard and will be able to create, edit and delete users, domains and redirects.

	/*
	 * Admin e-mail address
	 */
	
	define("ADMIN_EMAIL", "admin@domain.tld");
	
The admin's e-mail account must be existent in the virtual user database on your own server. (=> an e-mail account on a foreign server won't give you access!).
You can then login into the admin dashboard with that e-mail address and the corresponding password.

### Minimal required password length

	/*
	 * Minimal password length
	 */
	
	define("MIN_PASS_LENGTH", 8);

### Logfile

When logging is enabled, WebMUM will write messages into a file "webmum.log" in a specified directory (e.g. when a login attempt fails).

To enable logging, comment in the lines
	
	# define("WRITE_LOG", true);
	# define("WRITE_LOG_PATH","/var/www/webmum/log/");
	
... and make sure that PHP has permissions to write the log file to the directory defined in WRITE_LOG_PATH.

"Login-failed-messages" have the following scheme:

	Dec 19 13:00:19: WebMUM login failed for IP 127.0.0.1
	
If you want to use **Fail2Ban** with WebMUM, the filter has to be:

	[Definition]
	failregex = ^(.*)\: WebMUM login failed for IP <HOST>$


## Update / Upgrade WebMUM

If you cloned WebMUM into your filesystem via `git clone https://github.com/ThomasLeister/webmum`:

	git stash
	git pull origin master
	git stash pop
	
... and you are ready to go. Git might complain about conflicting files - you will have to resolve the merge conflict manually then.

If you downloaded WebMUM as a ZIP package, you have to update WebMUM manually.

**After every update:** 
Please check if your config.inc.php fits the current requirements by comparing your version of the file with the config.inc.php in the repository.

## FAQ

### Which password scheme does WebMUM use?

WebMUM uses the SHA512-CRYPT password scheme, which is known as a very secure scheme these days. Support for more password schemes will be added soon.


### "login/ cannot be found"

Webserver rewrites have to be enabled on your server, because WebMUM does not use real URLs for the frontend, but virtual URLs based on URL rewriting.
When rewriting fails, you receive a 404 error message.
