# Lunar Geslib Integration

[![tests](https://github.com/numaxlab/lunar-geslib/actions/workflows/tests.yml/badge.svg)](https://github.com/numaxlab/lunar-geslib/actions/workflows/tests.yml)
[![linter](https://github.com/numaxlab/lunar-geslib/actions/workflows/lint.yml/badge.svg)](https://github.com/numaxlab/lunar-geslib/actions/workflows/lint.yml)

This package integrates [Geslib](https://editorial.trevenque.es/productos/geslib/) data with
the [Lunar](https://lunarphp.io/) e-commerce
platform, providing an online store solution for products managed in Geslib. It allows for the import and mapping of
Geslib INTER files into Lunar's product catalog.

The primary purpose of `numaxlab/lunar-geslib` is to:

- Adapt the Lunar e-commerce platform to work with Geslib's product data.
- Process Geslib INTER files.
- Map the data contained within them (such as articles/books, editorials, authors, topics, etc.) to corresponding Lunar
  models (e.g., Products, Brands, Collections, Attributes).
- Enable a Lunar-based e-commerce site to reflect the product catalog managed in Geslib.
- Provide the endpoints so Geslib is able to fetch the orders placed in Lunar, allowing for order management
  synchronization between Lunar and Geslib.

This package relies on the [`numaxlab/geslib-files`](https://github.com/numaxlab/geslib-files) library to parse the
Geslib INTER file format.

## Installation

**Requirements:**

- PHP ^8.4
- Laravel
- [LunarPHP](https://lunarphp.io/) (core and admin panel)

**Steps:**

1. **Install the package via Composer on a Laravel project:**
   ```bash
   composer require numaxlab/lunar-geslib
   ```
   The package service provider will be auto-discovered by Laravel.

2. **Add the Filament Plugin to the Lunar Panel in the register method of your `AppServiceProvider`:**
   ```php
   LunarPanel::panel(fn($panel) => $panel->path('admin')->plugin(GeslibPlugin::make()))->register();
   ```

3. **Run the installation command:**
   ```bash
   php artisan lunar:geslib:install
   ```

> [!NOTE]  
> This installation command overrides Lunar's one. Don't run lunar install before/after running this command.


This command will:

* Publish all the Lunar configuration files, including `config/lunar/geslib.php`.
* Run necessary database migrations.
* Set up essential Lunar data):
    * Create a default admin user.
    * Import country and currency data.
    * Create a default webstore channel language, currency, and a retail customer group.
    * Configure taxation settings for Spanish VAT.
* Create Lunar Collection Groups. These groups are used to categorize Geslib data within Lunar (e.g., "Tipos de
  artículos" (Article Types), "Idiomas" (Languages), "Formatos de encuadernación" (Binding Types), etc.).
* Create Lunar Attributes. A comprehensive set of attributes will be created for all the needed models.
* Create a "Geslib Article" Product Type. This Product Type will be preconfigured with the relevant attributes
  created above.
* Publish Filament assets.

## Configuration

After running `lunar:geslib:install`, the configuration file will be available at `config/lunar/geslib.php`. Check all
the items before running the import command.

> [!WARNING]
> If you are installing this package on an existing project check all the configuration items that need any database
> specific information.

## Usage - Importing Geslib Data

Once the package is installed and configured, you can import data from Geslib INTER files using the following Artisan
command:

```bash
php artisan lunar:geslib:import
```

**Process:**

1. **File Scanning:** The command scans the directory specified by `inter_files_path` on the `inter_files_disk` (from
   your `config/lunar/geslib.php` file) for any files.
2. **Tracking Processed Files:** It checks each found file against the `geslib_inter_files` table in your database. This
   table stores the name and last modification timestamp of files that have already been processed.
3. **Dispatching Jobs:** For each new file found:
    * A record is created in the `geslib_inter_files` table.
    * A `NumaxLab\Lunar\Geslib\Jobs\ProcessGeslibInterFile` job is dispatched to the queue.
4. **Job Execution:** The queued job (`ProcessGeslibInterFile`) performs the actual parsing of the INTER file and the
   mapping of its data into your Lunar store. It uses the `numaxlab/geslib-files` package to read the file line by line
   and then employs various internal command classes to handle different types of Geslib records (articles, authors,
   editorials, etc.).

It's recommended to run this command periodically via Laravel's scheduler to keep your Lunar catalog
synchronized with updates from Geslib. It is recomended to run the command some minutes after Geslib uploads the
INTER files to your server.

### Queue configuration

It's very important to configure your queue system properly to ensure that the `ProcessGeslibInterFile` jobs are
executed with a FIFO (First In, First Out) strategy. This is crucial because the order of processing Geslib INTER files
matters. For that we dispatch the jobs to a specific queue named `geslib-inter-files`.

We recommend using [Laravel Horizon](https://laravel.com/docs/horizon) or any queue driver that supports FIFO, such as
Redis and a process monitor like Supervisor. If you use
Supervisor here is a configuration example:

```ini
[program:lunar-geslib-inter-files]
process_name = %(program_name)s_%(process_num)02d
command = php /var/www/your-laravel-app/artisan queue:work --queue=geslib-inter-files --timeout=3600 --sleep=3
autostart = true
autorestart = true
user = www-data ; Or your web server user
numprocs = 1 ; <-- This should be set to 1 to ensure FIFO processing
redirect_stderr = true
stdout_logfile = /var/www/html/your-laravel-app/storage/logs/queue-geslib-inter-files.log
stopwaitsecs = 3600
```

You have more information about how to configure your queue system in
the [Laravel documentation](https://laravel.com/docs/queues).

## Testing

To run the automated test suite, use the following command:

```bash
composer run test
```

## Linting

This project uses [Laravel Pint](https://laravel.com/docs/pint) for code styling. To format your code, run:

```bash
vendor/bin/pint
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## License

This package is open-source software licensed under the [MIT license](LICENSE.md).

## Authors

This package is developed by:

- Adrián Pardellas Blunier ([adrian@numax.org](mailto:adrian@numax.org))
- Noa García Amado ([noa@numax.org](mailto:noa@numax.org))

Maintained by [Laboratorio NUMAX](https://laboratorio.numax.org/).
