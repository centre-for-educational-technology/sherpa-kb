# SHERPA Knowledge Base

Knowledge Base (KB) is used for managing Questions, Answers and dealing with submitted Pending Questions suggested by the
end-users.
Application has three main roles, which are:
* Administrator - system administrators that are allowed to manage user accounts + anything else that lesser roles can
do
* Master Expert - a role that is dealing with final review and assignment of Published state to both Questions and
Answers. Experts are also dealing with reviewing Pending Questions and either marking those as Completed, which would
also create a new Question, marking them as Cancelled, which would be the end game for the Pending Question flow, or
changing the state back to Pending, which should be considered as sending it back to the Language Expert. Users with
this role are also able to see the statistics for Questions and Answers, how many have been translated to certain
language and how many Question-Answer pairs are harvested by the Chatterbot by using an API. A Master Expert can choose
to open a Language Expert view for any of the languages, thus temporarily emulating an expert for any of the languages.
* Language Expert - is generally dealing with Questions and Answers by working with corresponding translations to their
language. They are also the ones that would be initially responsible for dealing with Pending Questions, either changing
the working, adding texts in English and sending for review by Master Expert, or deleting the unnecessary ones.

KB has an API that is mostly responsible for exposing Question and Answer data for the Chatterbot. In addition to that,
there also are endpoints for submitting new Pending Questions, tracking user activity on the Chatterbot Helper UI,
mostly storing the questions submitted to the Chatterbot and answers received, and storing user ratings for the answers
they get. The data is stored in the database without being exposed anywhere and would later be used to analyse the
correctness of answers and how useful are those considered by the end-users.

## Requirements

Please note that this might not be the most current information and codebase files could prove to be the most reliable source of current state of affairs. These are the versions that are currently being used for development and production environments:

* PHP version 7.3 (7.4 should also work just fine)
* Laravel version 8
* Node.js version 14 (used in development for building static assets)
* MySQL version 8 (5.7+ should also be just fine)
* Apache web server (Nginx is also suitable, please check [Laravel documentation](https://laravel.com/docs/8.x/deployment#nginx) for configuration)

## Installation

Please read the [Laravel Documentation](https://laravel.com/docs/8.x) for information about server requirements and getting the framework running.

Please check the **composer.json** for Laravel version requirement. The version being used might not be the latest available release of the framework.

Copy .env.example file to .env and fill in all the necessary configurations. Environment should be configured for production with initial portion of configurations declaring that and disabling debugging. This could be used as a basis:

```
APP_NAME="SHERPA Knowledge Base"
APP_ENV=production
APP_KEY=<SOME-UNIQUE-KEY>
APP_DEBUG=false
APP_URL=https://<SOME_ADDRESS>
```

In addition to that you will need to configure the database connection, mailer, and [reCAPTCHA](https://developers.google.com/recaptcha/). Other configurations should be suitable and redis is not being used, at least for now. You could change the configuration yourself and use redis for cache and session storage.

Make sure that the database has been configured properly before running database migrations. Go to the application home catalog and run these commands in order to set up the application (make sure you use the code from **production** branch outside of development):

```
composer install --no-dev
php artisan key:generate
php artisan migrate
php artisan db:seed
```

This should get the packages installed, generate new secret key and update the .env file, run all the database migrations and create all the required tables, fill the database with data presets (categories, roles and some other data).

It might also be a good idea to configure [caching](https://laravel.com/docs/8.x/configuration#configuration-caching) to improve performance. **Please note that caches would need to be reset after updates!**

Once that is done, you would need to create an account and assign it an administrator role. After that all the user management could be done through the user management UI. That would include creating new user account and assigning roles. This command should get the job done (please replace the arguments with correct values):

```
php artisan auth:create-admin {name} {email} {password}
```

Log in to the application and create any additional accounts that are needed. One additional step would be setting up a scheduler according to the [documentation](https://laravel.com/docs/8.x/scheduling#starting-the-scheduler). Scheduled jobs will be used for sending daily and weekly notification emails to Language Experts.

### Configuring live-updates

Live-updates are achieved with use of [Laravel Echo](https://laravel.com/docs/8.x/broadcasting#client-side-installation) using [Pusher](https://pusher.com/) as a service provider. Any [Pusher-js](https://github.com/pusher/pusher-js) compatible implementation should be suitable as well.

The easiest way would be to just create a [Pusher Channels](https://pusher.com/channels/pricing) account and use a suitable plan. After that, there would also be a need to change the `.env` file and provide all the required configuration data.

## Development

Follow the installation instructions without adding the **--no-dev** to composer command. Make sure that Node.js runtime with NPM package manager is present and run `npm install`. You will be able to run all the commands that are outlined in **package.json** file as soon as the dependencies are installed. The ones that make most sense are `npm run watch` and `npm run prod`. The first one will watch for changes and rebuild static assets during development and the second one will create a production build.

Development is done on any branch except for **production**, which is a special branch that has stable code ready for use in the live environment. This branch should also include the latest build of static assets (styles, scripts and possibly some others).
