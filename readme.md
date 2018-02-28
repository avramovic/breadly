# Breadly

The missing backend for App Inventor applications.

Breadly allows you to perform BREAD operations on your relational database tables (*B*rowse, *R*ead, *E*dit, *A*dd, *D*elete). It also provides a simple HTTP API for your App Inventor apps to consume.

### Features 

* extremely robust & flexible (based on Laravel 5.5)
* multiple [DB drivers](https://laravel.com/docs/5.5/database#introduction): mysql, postgre, sqlite, ...
* web based admin panel to manage database structure and data (*B*rowse, *R*ead, *E*dit, *A*dd, *D*elete)
* provides a base platform to integrate your app and your web site
* simple HTTP API (in CSV format) to use from within your App Inventor apps (with ordinary Web component)
* user registration system with roles and permissions out-of-the-box (JWT token)
* file uploads from your app to your server or S3 storage
* image uploads with automatic resizing
* file upload limits by file size and/or image dimensions
* ...and a lot more!

## Server requirements

Since it is based on Laravel, it has pretty much the same [requirements](https://laravel.com/docs/5.5/installation#server-requirements):

* PHP >= 7.0.0
* OpenSSL PHP Extension
* PDO PHP Extension
* Mbstring PHP Extension
* Tokenizer PHP Extension
* XML PHP Extension

Additionally, you will need a relational database (to manage) and some patience until I write additional documentation in the Wiki.

## Installation

First clone this repository:

`git clone https://github.com/avramovic/breadly.git`

Then install dependencies with composer:

`composer install`

## Setup

Once dependencies are installed, point your web site root to the `public` subfolder of this project and simply navigate to `http://yourwebsite.com/install`, then follow on-screen instructions to set up a database connection.

Admin user is automatically created with following credentials:

| E mail | Password |
|--------|----------|
| admin@admin.com | password |

### Important

Make sure to change admin e-mail and password at your first login!