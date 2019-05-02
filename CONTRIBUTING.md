# Contributing

## Translation

A weblate instance has been setup to translate this plugin
without using git.

Translate Matomo :

<https://weblate.nsupdate.info/projects/matomo-plugin-signup/plugin/>

## Development

To develop on this plugin, you need first a local instance.

You have to clone the older Matomo version this plugin supports.

### Install a specific version of Matomo

It requires:

- php
- composer
- MariaDB or MySQL database

```bash
# Check the minimal version of Matomo this plugin supports in [plugin.json](plugin.json).

# If you see:
#   "require": {
#     "piwik": ">=3.6.0-stable,<4.0.0-b1"
#   },
# then you should develop on Matomo 3.6.0:

git clone git@github.com:matomo-org/matomo.git --branch 3.6.0
cd matomo/

# Install dependencies
composer install

# Run PHP local server
php -S 0.0.0.0:8000
```

All this part, expect cloning a specific version of Matomo,
is also officially documented by Matomo:

<https://developer.matomo.org/guides/getting-started-part-1>

Then, follow the installation process.

### Clone Signup plugin

Once your Matomo instance is working, clone the Signup plugin inside `plugins/` folder.

Asuming you forked `matomo-plugin-signup`:

```bash
cd plugins/

git clone git@github.com:YOUR_GITHUB_NAME/matomo-plugin-signup.git Signup
cd Signup/
```

Now you should be able to enable the plugin in Matomo admin interface.
