# LaveroAdmin

LaveroAdmin is an administrative interface builder for Laravel, based on open-admin / laravel-admin. It provides a clean, modern, and extensible toolkit to build web dashboards, form builders, and data grids with ease.

Repository: [https://github.com/LaveroAdmin/LaveroAdmin](https://github.com/LaveroAdmin/LaveroAdmin)

## Requirements

- PHP >= 7.3 (supports PHP 8.0, 8.1, 8.2, 8.3, 8.4+)
- Laravel Framework: ^7.0 | ^8.0 | ^9.0 | ^10.0 | ^11.0 | ^12.0 | ^13.0

## Installation

```bash
composer require laveroadmin/laveroadmin
```

Publish assets and configuration:
```bash
php artisan admin:install
```

After installation, the administration panel will be available at:
`http://your-app-domain/admin`

Default credentials:
- **Username:** `admin`
- **Password:** `admin`

## Configuration

The configuration file is located at `config/admin.php`.

## Extensions

LaveroAdmin supports official extensions:
- [laveroadmin/helpers](https://github.com/LaveroAdmin/helpers) - Development helpers & code scaffolding
- [laveroadmin/log-viewer](https://github.com/LaveroAdmin/log-viewer) - Real-time application log viewer
- [laveroadmin/backup](https://github.com/LaveroAdmin/backup) - Database and file backups manager based on Spatie Backup
- [laveroadmin/marketplace](https://github.com/LaveroAdmin/marketplace) - Extension marketplace & lifecycle manager
- [laveroadmin/queue-manager](https://github.com/LaveroAdmin/queue-manager) - Queue monitor, failed jobs management & batch tracking
- [laveroadmin/activity-log](https://github.com/LaveroAdmin/activity-log) - User activity log and audit trail based on Spatie Activitylog
- [laveroadmin/translations](https://github.com/LaveroAdmin/translations) - Localization and translation manager (PHP & JSON)
- [laveroadmin/media-manager](https://github.com/LaveroAdmin/media-manager) - Media library & file manager
- [laveroadmin/reporter](https://github.com/LaveroAdmin/reporter) - Application exception tracker & reporter
- [laveroadmin/scheduling](https://github.com/LaveroAdmin/scheduling) - Cron task scheduling manager
- [laveroadmin/config](https://github.com/LaveroAdmin/config) - Database configuration settings manager
- [laveroadmin/ckeditor](https://github.com/LaveroAdmin/ckeditor) - Rich text CKEditor 5 integration
- [laveroadmin/grid-sortable](https://github.com/LaveroAdmin/grid-sortable) - Drag & drop sortable grid rows
- [laveroadmin/wafu](https://github.com/LaveroAdmin/wafu) - Web application firewall & IP access rules manager

## License

Licensed under [The MIT License (MIT)](LICENSE).

## Thanks

- Special thanks to [open-admin](https://github.com/open-admin-org/open-admin) for open-admin development.
- Special thanks to [z-song](https://github.com/z-song) for original development of laravel-admin.
