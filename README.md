Laravel 4 Envato API Wrapper
======

A Laravel 4 package for the Envato marketplaces - like themeforest.net.

----------------
#### Information

Visit www.gewora.net for more awesome products!
___

#### Installation

To get the latest version of Gewora/Envato simply require it in your `composer.json` file.

```
"gewora/envato": "dev-master"
```

After that, you'll need to run `composer install` to download the latest Version and updating the autoloader.

Once Gewora/Envato is installed, you need to register the ServiceProvider. To do that open `app/config/app.php` and add the following to the `providers` key.

```
'Gewora\Envato\EnvatoServiceProvider',
```

## How to use
First you need to publish the config file. To do that, type the following in the terminal:

```
php artisan config:publish Gewora\Envato
```

Now open: `app/config/packages/Gewora/config.php` and fill it with your data

```
return array(

    'username' => 'Your Envato Username',
    'api_key' => 'Your Envato API Key',

);
```

Now you can use the package like that:

```
$result = Envato::account_information();
dd($result);
```
