# Build
In order to build the application, you must have [Composer](https://getcomposer.org) and [NPM](https://www.npmjs.com/get-npm) installed.

When you have both of them installed, the next steps are:
1. Run `composer install` to install project's PHP dependencies defined in `composer.json`.
2. Run `npm install` to install project's JS dependencies defined in `package.json`.
3. Run `npm run prod` to build Vue.js front-end components.
4. Run `php artisan serve` for starting up the local server.

By default, the server will be available on the following address: `http://127.0.0.1:8000`.

# Configuration
In order to config the application, you have to rename the `.env.example` file to `.env` file.

There are multiple config variables, but the ones which need to be set, are the ones prefixed with `APP`, `DB` (for setting up the connection to DB) and `MAIL` (for sending e-mails from the application).

In order to have authentication working, you need to set `SANCTUM_STATEFUL_DOMAINS` variable in `.env` file to:
`localhost` when running from local machine or to domain, where the app is available from.

Note: Without setting the `APP_ENV` to `production`, sending e-mails for Litter Request Approvals feature won't work.

# Seeding fake data
A PHP library called `Faker` is responsible for creating fake data into the DB. In order to run in, you have to run the following command: `php artisan db:seed`.

WARNING: This will OVERWRITE the data in DB!

In process of putting fake data into the DB, 4 different system users with different permissions are created - those users are defined in the `UserSeeder.php` file.