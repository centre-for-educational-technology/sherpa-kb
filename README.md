# SHERPA Knowledge Base

## Installation

Please read the [Laravel Documentation](https://laravel.com/docs/8.x) for information about server requirements and getting the framework running.

Please check the **composer.json** for Laravel version requirement. the version being used might not be the latest available release of the framework.

Copy .env.example file to .env and fill in all the necessary configurations. Make sure that the database has been configured properly before running database migrations. Go to the application home catalog and run these commands in order to setup the application (make sure you use the code from **production** branch outside of development):

```
composer install --no-dev
php artisan key:generate
php artisan migrate
php artisan db:seed
```

This should get the packages installed, generate new secret key and update the .env file, run all the database migrations and create all the required tables, fill the database with some of the data presets (categories, roles and some other data).

Once that is done, you would need to create an account and assign it an administrator role. After that all the user management could be done through the user management UI. That would include creating new user account and assigning roles. This command should get the job done (please replace the arguments with correct values):

```
php artisan auth:create-admin {name} {email} {password}
```

Log in to the application and create any additional accounts that are needed. One additional step would be setting up a scheduler according to the [documentation](https://laravel.com/docs/8.x/scheduling#starting-the-scheduler). Running it every minute might not be needed as the only job that is currently present is the daily sending of email to Language Experts.

## Development

Follow the installation instructions without adding the **--no-dev** to composer command. Make sure that Node.js runtime with NPM packkage manager is present and run `npm install`. You will be able to run all the commands that are outlined in **package.json** file as soon as the dependencies are installed. The ones that make most sense are `npm run watch` and `npm run prod`. The first one will watch for changes and rebuild static assets during development and the second one will create a production build.

Development is done on any branch except for **production**, which is a special branch that has stable code ready for use in the live environment. This branch should also include the latest build of static accets (styles, scripts and possibly some others).