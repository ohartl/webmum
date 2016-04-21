# WebMUM - Web Mailserver User Manager

[![Build Status](https://travis-ci.org/ThomasLeister/webmum.svg)](https://travis-ci.org/ThomasLeister/webmum)

***WebMUM is not compatible with the [new Mailserver-HowTo](https://thomas-leister.de/allgemein/sicherer-mailserver-dovecot-postfix-virtuellen-benutzern-mysql-ubuntu-server-xenial/)!***

WebMUM is a web frontend based on PHP which helps you to manage e-mail server via MySQL. This software is licensed under the MIT license.

Lead and started by [ThomasLeister](https://github.com/ThomasLeister), a passionate [blogger](https://thomas-leister.de/) specialized topics like linux, open-source, servers etc., this project is developed together with [ohartl](https://github.com/ohartl) and the [contributes](https://github.com/ThomasLeister/webmum/graphs/contributors).

Feel free to send in issues and pull requests, your support for this project is much appreciated!


## Installation

Clone the WebMUM Repository to your webserver's virtual host root directory:

```bash
git clone https://github.com/ThomasLeister/webmum
```

A update / upgrade guide can be found [here](#update--upgrade-webmum).


### Webserver

Now configure your webserver. URL rewriting to index.php is required.

#### Nginx

Nginx config examples following, but you still need to change domain and path in config as explained [here](#paths).

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

    # protect the codebase by denying direct access
    location ^~ /webmum/include/php {
        deny all;
        return 403;
    }
    location ^~ /webmum/config {
        deny all;
        return 403;
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

    # protect the codebase by denying direct access
    location ^~ /include/php {
        deny all;
        return 403;
    }
    location ^~ /config {
        deny all;
        return 403;
    }
}
```

#### Apache

Apache config examples following, but you still need to change domain and path in config as explained [here](#paths).

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

Access to the codebase is denied with a `.htaccess` file, that can be found in `/include/php`.



## WebMUM Configuration

Configure WebMUM via the configuration file at `config/config.inc.php`.

### MySQL

At first the database access has to be configured under the config key `mysql`.

Check if you've got the same database schema as configured in the config key `schema`.

### Mailbox limit (Optional)

If you want to use your "mailbox_limit" column to limit the size of your users' mailboxes, just enable mailbox limit in the options.

```php
'options' => array(
    ...
    'enable_mailbox_limits' => true,
    ...
),
```

WebMUM will then show a new field "Mailbox limit" in the frontend.


### Multiple source redirect support (Optional)

As mailservers can only process a single source address for redirects the database table for aliases / redirects can only hold a single source address in a row.
WebMum will, if you enabled the multiple source redirect support, do some magic so there is only a single address in a row even though multiple addresses where entered.
To make this work another column in the database table is required, which holds an identifier for the list of source addresses, so they can be edited like normal redirects.

By default you can only redirect a single address to a single or multiple destinations.
If you want to enable support for redirecting multiple source addresses to a destination, just enable it in the options:

```php
'options' => array(
    ...
    'enable_multi_source_redirects' => true,
    ...
),
```

And add the following column to your database table for aliases / redirects:

```sql
ALTER TABLE `aliases` ADD COLUMN `multi_source` VARCHAR(32) NULL DEFAULT NULL;
```

WebMUM will then show a larger field for source addresses in the frontend and you can not list emails in source field.


### Admin domain limits (Optional)

If you share your mailserver with others, host their domains and they should be able to manage their domains, but not all domains on that mailserver then this is the right option for you. 
You have to add that user to the `admins` array in your configuration and enable admin domain limits in the options:

```php
'options' => array(
    ...
    'enable_admin_domain_limits' => true,
    ...
),
```

also you have to make an entry in the `admin_domain_limits` array, for example `peter@his.tld` should be able to manage his domains `his.tld` and `his-company.tld` then configure the following:

```php
'admin_domain_limits' => array(
    'peter@his.tld' => array('his.tld', 'his-company.tld'),
);
```

Admins that have been listed in `admin_domain_limits` don't have access to the "Manage domains" pages, otherwise they could delete domains they are managing, but maybe someone else owns.

### Paths

The `base_url` is the URL your WebMUM installation is accessible from outside, this also includes subdirectories if you installed it in a subdirectory for that specific domain.

```php
'base_url' => 'http://localhost/webmum',
```

In the example above, WebMUM is located in a subdirectory named "webmum/". If your WebMUM installation is directly accessible from a domain (has its own domain), then set the `FRONTEND_BASE_PATH` to something like this:

```php
'base_url' => 'http://webmum.mydomain.tld',
```


### Admin e-mail address

Only users with one of the specified email addresses will have access to the administrator's dashboard and will be able to create, edit and delete users, domains and redirects.

```php
'admins' = array(
    'admin@domain.tld',
);
```

Admin email accounts must exist in the virtual user database on your own server. (=> an e-mail account on a foreign server won't give you access!). You can then login into the admin dashboard with that e-mail address and the corresponding password.

### Minimal required password length

```php
'password' => array(
    ...
    'min_length' => 8,
    ...
),
```

### Logfile

When logging is enabled, WebMUM will write messages into a file "webmum.log" in a specified directory (e.g. when a login attempt fails).

Enable logging by setting it to enabled in the options:

```php
'options' => array(
    ...
    'enable_logging' => true,
    ...
),
```

... and set a log path where the PHP user has permission to write the log file:

```php
'log_path' => '/var/www/webmum/log/',
```

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

### Validate that source addresses of redirects must be from the managed domains only

```php
'options' => array(
    ...
    'enable_validate_aliases_source_domain' => true,
    ...
),
```


### Frontend options

Choose delimiter between multiple email addresses: comma, semicolon or new line separated.

**Tip:** new line is helpful for long lists of addresses.

```php
'frontend_options' => array(
    // Separator for email lists
    'email_separator_text' => ', ', // possible values: ', ' (default), '; ', PHP_EOL (newline)
    'email_separator_form' => ',', // possible values: ',' (default), ';', PHP_EOL (newline)
),
```

The input for addresses can be separated by `,`, `;`, `:`, `|`, `newline` and combinations since all of them will result in a valid list of addresses in database, magic.


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

### Which password hash algorithm does WebMUM use?

By default WebMUM uses the `SHA-512` hash algorithm for passwords. You can also choose between the alternatives `SHA-256` or `BLOWFISH` in the config.

```php
'password' => array(
    ...
    'hash_algorithm' => 'SHA-512', // Supported algorithms: SHA-512, SHA-256, BLOWFISH
    ...
),
```

### "login/ cannot be found"

Webserver rewrites have to be enabled on your server, because WebMUM does not use real URLs for the frontend, but virtual URLs based on URL rewriting.
When rewriting fails, you receive a 404 error message.
