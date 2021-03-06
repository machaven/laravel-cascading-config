Laravel 5 Cascading Config Files
=========================

Why? - The .env problem
--------

When continously developing and extending a laravel application, you may find that your .env file becomes large and hard to maintain. When multiple branches with .env changes are deployed, it becomes a nightmare to consolidate all the .env changes and update the live .env file.

This forced me to rethink configuration and what is truly important inside an .env file.


What to keep in your .env file

* Standard laravel .env settings
* Sensitive information, like login credentials for other apis.

What to take out of your .env file

* All custom settings that might seldom change.

Why use cascading config files?
--------

1. Cascading configuration files allow you to keep a set of config files for every environment. All environment configs override the base configs in the config folder. This allows you to setup a dynamic config for each environment.

2. Settings changes can be recorded on pull requests. This prevents settings changes from being lost when only changed on the server .env file.

Furthermore if you use the env() function in your config files, it will allow you to override any defaults in the config file with variables from the .env file if they exist.

Install
--------

Install the package through composer

```composer require machaven/laravel-cascading-config```

After installing, add the ServiceProvider to the providers array in app/config/app.php
(not required for laravel 5.5+)

```Machaven\LaravelCascadingConfig\CascadingConfigProvider::class,```

How this works
----

The service provider will check if the directory exists for the environment configured in your .env file. If the environment folder exists, then all configuration files will be read from it and merged over the configurations from the config folder.

Example for cascading config files
----

Firstly, create your default config file in the standard laravel config/ folder.

```config/example.php```

Now create a folder to override the example config for an environment. A folder can be created for any environment defined in your APP_ENV. In this example, we use local:

```config/local/```

Now create another example config in the local folder:

```config/local/example.php```


Example config file contents
----
config/example.php

```
<?php

return [
    'clientApi' => [
        'curlTimeout' => env('CLIENT_API_CURL_TIMEOUT', 5),
        'baseUrl' => env('CLIENT_API_BASE_URL', 'http://test.com/api/'),
        'username' => env('CLIENT_API_USERNAME'), // Always read username and password from .env
        'password' => env('CLIENT_API_PASSWORD'),
    ],
    'test' => env('TEST', 'prod'),
];
```

config/local/example.php

```
<?php

return [
    'clientApi' => [
        'baseUrl' => env('CLIENT_API_BASE_URL', 'http://test.local/api/'),
    ],
    'test' => env('TEST', 'local'),
];
```

In the local configuration above, we are not overriding the curlTimeout array key.

Example config results
----

When APP_ENV is local:

```
>>> config('example');
=> [
     "clientApi" => [
       "curlTimeout" => 5, // This is merged from the config/example.php config file
       "baseUrl" => "http://test.local/api/",
       "username" => "username in .env file",
       "password" => "password in .env file",
     ],
     "test" => "local",
   ]

```


When APP_ENV is prod:

```
>>> config('example');
=> [
     "clientApi" => [
       "curlTimeout" => 5,
       "baseUrl" => "http://test.com/api/",
       "username" => "username in .env file",
       "password" => "password in .env file",
     ],
     "test" => "prod",
   ]
```

As you can see, the above file is not overridden by the one in the config/local/ folder.


When APP_ENV is prod and TEST='ENV FILE' is added to .env file:
```
>>> config('example');
=> [
     "clientApi" => [
       "curlTimeout" => 5,
       "baseUrl" => "http://test.com/api/",
       "username" => "username in .env file",
       "password" => "password in .env file",
     ],
     "test" => "ENV FILE",
   ]
```

The test key is overridden here by the TEST variable in the .env file. This is because we are using the env() helper in our config files to override settings for any environment; if they exit in the .env.