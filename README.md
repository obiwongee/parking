# Soapbox Parking App

## Install

### Requirements
* PHP
* Mysql
* Phalcon
* Nginx

### Instructions

* Download and install [Phalcon][1] PHP framework
* Clone repo to web root
* Configure nginx
```
server {
    listen      80;
    server_name localhost.dev;
    root        /var/www/parking/public;
    index       index.php index.html index.htm;

    location / {
        try_files $uri $uri/ /index.php?_url=$uri&$args;
    }

    location ~ \.php {
        fastcgi_pass unix:/var/run/php5-fpm.sock;
        fastcgi_index /index.php;

        include fastcgi_params;
        fastcgi_split_path_info       ^(.+\.php)(/.+)$;
        fastcgi_param PATH_INFO       $fastcgi_path_info;
        fastcgi_param PATH_TRANSLATED $document_root$fastcgi_path_info;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    location ~ /\.ht {
        deny all;
    }
}

```
* Configure /etc/hosts file if necessary
* Restore mySQL DB from the schema directory
* Configure mySQL in public/index.php

## API Usage
### Park a car
**POST** http://localhost.dev/api/parking/park

**Params**
* parking_lot_id - int
* type - string (small, medium, large, super_size)
* license_plate - string (6-7 alphanumeric characters)

### Unpark a car
**PUT** http://localhost.dev/api/parking/park

**Params**
* parking_lot_id - int
* license_plate - string (6-7 alphanumeric characters)

### Get car status
**GET** http://localhost.dev/api/parking/park

**Params**
* license_plate - string (6-7 alphanumeric characters)

## UI
**URL**: http://localhost.dev/parking

[1]: https://phalconphp.com/en/download