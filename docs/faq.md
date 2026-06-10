# FAQ

## I assigned icons but they don't appear. What's wrong?

Clear the Matomo cache: **System → General Settings → Clear cache**. If you're still seeing the old icons, make sure the browser cache is also cleared (hard refresh: Ctrl+F5 or Cmd+Shift+R).

## Can I use custom icons not included in Material Design?

Yes. See the README.md → Adding custom icons section for instructions. Any SVG can be added as long as it is placed in the `icons/material/` directory and the JSON index is rebuilt.

## Does this plugin work with Matomo 4?

No. The plugin targets Matomo 5.x (>=5.0.0-b1, <6.0.0-b1).

## Does the plugin send data to external servers?

No. All icons are bundled locally. No external API calls, no tracking, no phoning home.

## I see "Time on page: 0s" or no time at all.

The first action of each visit is the landing page – it has no time-on-page because there is no previous page. Subsequent actions show the time spent on the previous page. If all actions show no time, your tracking data may not include `time_spent_ref_action` (check if your tracker sends `ping` requests or if the page tracking is properly configured).

## How do I uninstall the plugin?

Deactivate it in **System → Plugins**, then delete the `plugins/EventIcons` directory. The database contains no custom tables – uninstalling leaves no traces.
