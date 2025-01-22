# Filament Inverse Shield

This plugin is aimed to users of the Filament Shield plugin, who want to persist their settings in a file, and load them back later.

## Introduction

By default, the Filament Shield plugin saves roles and roles/permission relationships in the database.
This makes it quite difficult to manage the roles and permissions in a version control system, or to share them between different instances of the same application.

This plugin allows you to save the roles and permissions in a seeder file, which can be loaded back later.

## Installation

To install this plugin, you need to add it to your `composer.json` file:

```bash
composer require drafolin/filament-inverse-shield
```

## Usage

To save the roles and permissions in a seeder file, you need to run the following command:

```bash
php artisan inverse-shield
```

This will create a seeder file in the `database/seeds` directory.

To load the roles and permissions from the seeder file, you need to run the following command:

```bash
php artisan db:seed --class=ShieldSeeder
```

## Parameters

The `inverse-shield` command supports the default artisan parameters, as well as the following custom parameters:

- `--seeder`: The name of the seeder file to create. Default: `ShieldSeeder`.
- `--panel`: The name of the filament panel to use, if you renamed it. Default: `web`.

## License

This plugin is open-sourced software licensed under the [MIT license](LICENSE.md).
