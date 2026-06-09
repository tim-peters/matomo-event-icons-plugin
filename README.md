# Event Icons – Matomo Plugin

Replace generic event icons in Matomo's visitor log and real-time live widget with specific Material Design icons based on event category and action.

## Features

- Assign individual Material Design icons to event category/action pairs
- Icons appear in the **Visitor Log** and the **Live (Real-time)** widget
- Detects existing event types from recent tracking data (last 90 days)
- **2,100+ Material Design icons** included – searchable picker in the settings
- Works per website, mapping is global (applied to all sites)
- Lightweight – icons are served on-demand from a compact JSON file (~570 KB), no external dependencies

## Requirements

- Matomo 5.x (>=5.0.0-b1, <6.0.0-b1)

## Installation

1. Copy the `plugins/EventIcons` folder into your Matomo installation:
   ```bash
   cp -r plugins/EventIcons /path/to/matomo/plugins/EventIcons
   ```
2. Go to **System → Plugins** in the Matomo admin and activate **EventIcons**.
3. Clear the Matomo cache: **System → General Settings → Clear cache**.

## Usage

1. Navigate to **System → Event Icons** in the Matomo admin.
2. Select a website and click **Load event types** – detected categories/actions appear in a table.
3. Click the icon cell of a row to open the icon picker. Search or browse, then click an icon to select it.
4. Use **Bulk assign** to set the same icon for multiple rows at once.
5. Click **Save selected** to persist the mapping.

The icons are replaced immediately in the visitor log and live widget – no page reload needed.

## Development

A Docker development setup is included:

```bash
docker compose up -d
```

The plugin source is mounted live at `plugins/EventIcons`. Changes to PHP, Twig, JS, or CSS files take effect immediately. For JavaScript changes to appear in the merged asset bundle, clear the Matomo cache:

```bash
docker compose exec matomo sh -c 'rm -rf /var/www/html/tmp/templates_c/* /var/www/html/tmp/assets/* /var/www/html/tmp/cache/*'
```

## Adding custom icons

The plugin bundles 2,100+ Material Design icons, but you can add custom ones:

1. Place your SVG file in `plugins/EventIcons/icons/material/`.
2. Run the extraction script to rebuild `material-paths.json`:
   ```bash
   cd plugins/EventIcons/icons/material
   python3 -c "
   import os, json, re
   paths = {}
   for f in sorted(os.listdir('.')):
       if not f.endswith('.svg'): continue
       name = f[:-4]
       with open(f) as fh: content = fh.read()
       m = re.search(r'<svg[^>]*>(.*?)</svg>', content, re.DOTALL)
       if m: paths[name] = m.group(1).strip()
   with open('../material-paths.json', 'w') as fh:
       json.dump(paths, fh, separators=(',', ':'))
   print(f'Extracted {len(paths)} icons')
   ```
3. Delete the individual SVG files (optional – only `material-paths.json` is needed at runtime).
4. Clear the Matomo cache.

## License

GPL v3+
