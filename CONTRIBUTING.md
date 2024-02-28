# Contributing

## Translation

[![Translation current status](https://hosted.weblate.org/widgets/matomo/-/communityplugin-signup/multi-auto.svg)](https://hosted.weblate.org/engage/matomo/)

Translate this plugin :

<https://hosted.weblate.org/projects/matomo/communityplugin-signup/>

Or translate Matomo or any plugin :

<https://hosted.weblate.org/engage/matomo/>

## Development

To develop on this plugin, you first need to run
a local instance of Matomo.

You have to clone the oldest Matomo version this plugin supports.

### Install a specific version of Matomo

It requires:

- php
- composer
- MariaDB or MySQL database
- node (works better on v16 for me)

Check the minimal version of Matomo this plugin supports in [plugin.json](plugin.json):

If you see:

```bash
  "require": {
    "piwik": ">=4.8.0,<5.0.0-b1"
  },
```

then you should develop on Matomo 4.8.0, here is how to install it:

``` bash
git clone git@github.com:matomo-org/matomo.git --branch 4.8.0
cd matomo/

# Install dependencies
composer install

# Install frontend dependencies
npm install

# Run PHP local server
php -S 0.0.0.0:8000
```

- Then go to <http://0.0.0.0:8000/index.php>
- Follow the installation procedure.

``` bash
# Enable development mode (to avoid assets caching and enabling vue assets building)
php console development:enable
```

All this part is also officially documented by Matomo:

<https://developer.matomo.org/guides/getting-started-part-1>

### Clone Signup plugin

Once your Matomo instance is running, clone the Signup plugin inside `plugins/` folder.

Asuming you forked `matomo-plugin-signup`:

```bash
cd plugins/

git clone git@github.com:YOUR_GITHUB_NAME/matomo-plugin-signup.git Signup
cd Signup/
```

Now you should be able to enable the plugin in Matomo admin interface.

If you edit frontend assets (vue, js files...), run this commands to automatically recompile assets:

```bash
# Automatically build vue assets
php console vue:build Signup --watch
```
