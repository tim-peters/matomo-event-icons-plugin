# Event Icons – Matomo Plugin Development Repository

This repository contains the **Event Icons** Matomo plugin source code plus a Docker development environment.

The plugin itself lives in `plugins/EventIcons/`. For installation and usage instructions, see the [plugin README](plugins/EventIcons/README.md).

## Development

```bash
docker compose up -d
```

The plugin source is mounted live at `plugins/EventIcons`. Changes take effect immediately after clearing the Matomo cache.

## License

GPL-3.0+ (see [LICENSE](LICENSE))

The bundled Material Design icons by Google are licensed under Apache 2.0 (see [NOTICE](plugins/EventIcons/NOTICE)).
