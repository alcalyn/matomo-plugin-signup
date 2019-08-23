# Contributing

## Translation

A weblate instance has been setup to translate this plugin
without using git.

Translate Matomo :

<https://weblate.nsupdate.info/projects/matomo-plugin-signup/plugin/>

Translations progression:

![Weblate translations progression of Matomo Signup Plugin](https://weblate.alcalyn.app/widgets/matomo-plugin-signup/-/multi-auto.svg)

## Development

To develop on this plugin, you first need to run
a local instance of Matomo.

You have to clone the oldest Matomo version this plugin supports.

### Install a specific version of Matomo

It requires:

- php
- composer
- MariaDB or MySQL database

Check the minimal version of Matomo this plugin supports in [plugin.json](plugin.json):

If you see:

```bash
  "require": {
    "piwik": ">=3.6.0-stable,<4.0.0-b1"
  },
```

then you should develop on Matomo 3.6.0, here is how to install it:

``` bash
git clone git@github.com:matomo-org/matomo.git --branch 3.6.0
cd matomo/

# Install dependencies
composer install

# Enable development mode (useful i.e to avoid assets caching)
php console development:enable

# Run PHP local server
php -S 0.0.0.0:8000
```

All this part, expect cloning a specific version of Matomo,
is also officially documented by Matomo:

<https://developer.matomo.org/guides/getting-started-part-1>

Then, follow the installation process.

### Clone Signup plugin

Once your Matomo instance is running, clone the Signup plugin inside `plugins/` folder.

Asuming you forked `matomo-plugin-signup`:

```bash
cd plugins/

git clone git@github.com:YOUR_GITHUB_NAME/matomo-plugin-signup.git Signup
cd Signup/
```

Now you should be able to enable the plugin in Matomo admin interface.
