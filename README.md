# nxter-sigbro-auth

Wordpress plugin which add authorization via Ardor Token

## Installation

* Download zip archive into plugin directroy. Unzip it into `nxter-sigbro-auth`. Or you can `git clone` this repo. 
* Create simlink for two files in  directroy `theme` into the current theme directory. You can copy this files but it is not recommended for easier update procedure.
* Create blank page and setup template `SIGBRO Auth` and `sigbro-auth` permalink.
* Create another blank page with template `SIGBRO Profile Page` and `sigbro-profile` permalink.
* Enable plugin in settings
* That is all you need to setup our plugin.

## Changelog

#### 2019-04-10 version 0.2.2
* Change redirect from `/wp-admin/` to `/wp-login.php`

#### 2019-04-10 version 0.2.1
* Deisgn auth form with bootstrap

#### 2019-04-10 version 0.2.0
* Change Nxter logo
* Hide login form when do redirect via our plugin

#### 2019-04-08 version 0.1.0
* Basic auth via SIGBRO Mobile app or by yourself via SIGBRO Offline. 
