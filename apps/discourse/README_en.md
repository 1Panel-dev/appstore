## Notes
- Accessing via port will cause the console to not display. Please modify it to use domain access.

## Configuration

### Set Language
Access the URI `/admin/site_settings/category/required` in your browser and set `default locale` to `Simplified Chinese`.

### Configure SMTP
Manually edit `docker-compose` during application installation to modify SMTP-related configurations.

### Install Plugins
**Go to the `Containers` page, find the Discourse application, click the `Terminal` button in the right action column, and execute the following commands in the container terminal:**
```shell
cd /opt/bitnami/discourse
# Install plugin, replace PLUGIN_REPO_URL with the plugin URL
sudo RAILS_ENV=production bundle exec rake plugin:install repo=PLUGIN_REPO_URL
# Precompile new assets for plugin usage
sudo RAILS_ENV=production bundle exec rake assets:precompile
```

### Uninstall Plugins
**Go to the `Containers` page, find the Discourse application, click the `Terminal` button in the right action column, and execute the following commands in the container terminal:**
```shell
cd /opt/bitnami/discourse/plugins
# Delete plugin directory, replace PLUGIN-DIR with the plugin directory
rm -rf PLUGIN-DIR
# Precompile new assets
sudo RAILS_ENV=production bundle exec rake assets:precompile
```