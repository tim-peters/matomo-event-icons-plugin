# Event Icons Documentation

## Overview

Event Icons lets you replace the generic event icons in Matomo's Visitor Log and Live (Real-time) widget with specific icons per event category and action.

## How icon replacement works

The plugin hooks into Matomo's JavaScript and CSS pipeline:

1. A **MutationObserver** watches the DOM for new visitor log and live widget entries.
2. When entries are detected, the plugin reads the event category/action from the element's title attribute (live widget) or text content (visitor log).
3. The category/action pair is looked up in the user-defined mapping, and the matching icon's `<img>` src is replaced.
4. An **AJAX call** fetches action timestamps (`server_time`, `time_spent_ref_action`) from the database and appends them to the tooltip.

## Settings page

The settings page at **System → Event Icons** provides:

- **Website selector** – choose which site's events to inspect.
- **Load event types** – scans the last 90 days of tracking data for existing category/action pairs.
- **Icon picker** – search through 2,100+ Material Design icons by name.
- **Bulk assign** – set the same icon for multiple rows at once.
- **Save selected** – persists the mapping immediately.

## Icons

Icons are stored in `icons/material-paths.json` as a JSON object mapping icon names to SVG path data. This single file (~570 KB) replaces 2,122 individual SVG files. Icons are served via PHP controller with `Cache-Control: public, max-age=86400`.

To add custom icons, see README.md → Adding custom icons.

## Database queries

The plugin performs two database queries:

1. **Event detection** (settings page): `SELECT cat.name, act.name, COUNT(*) FROM log_link_visit_action JOIN log_action ON ... GROUP BY cat.name, act.name` – scoped to last 90 days and a specific site.
2. **Action times** (tooltip enhancement): `SELECT server_time, time_spent_ref_action FROM log_link_visit_action WHERE idvisit = ? ORDER BY server_time ASC`.

## Limitations

- The first action of each visit always shows no time-on-page (landing page has no predecessor).
- The mapping is global – all sites share the same icon configuration.
