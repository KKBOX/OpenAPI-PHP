# KKBOX Open API PHP SDK

The project helps you to access [KKBOX's Open API](https://developer.kkbox.com) using PHP programming languages.

## Installation

You can install the package by using [Composer](https://getcomposer.org/).

```
composer require kkbox/kkboxopenapi kkbox/kkboxopenapi
```

To use the development version, please add to your `composer.json`.

```json
{
  "require": {
    "kkbox/kkboxopenapi": "dev-master"
  },
  "repositories": [
    {
      "type": "vcs",
      "url": "git@github.com:kkbox/OpenAPI-PHP.git"
    }
  ]
}
```

And then run `composer install`.

## Usage

### instantiation

To start using the SDK, you need to register your app in KKBOX's [developer site](https://developer.kkbox.com) and obtain a valid client ID and client secret. Then, you can create an instance of `OpenAPI`.

```php
use KKBOX\KKBOXOpenAPI\OpenAPI;

$clientID = 'YOUR_CLIENT_ID';
$clientSecret = 'YOUR_CLIENT_SECRET';
$openAPI = new OpenAPI($clientID, $clientSecret);
```

### Fetch Access Token

Before doing API calls, you need to fetch an access token at first.

```php
$openAPI->fetchAndUpdateAccessToken();
```

### API Calls

When you are ready, you can make API calls like to search, or to fetch information of tracks like

```php
$response = $openAPI->search('Love');
```

The SDK adopts [Guzzle HTTP client](https://github.com/guzzle/guzzle), and the response objects conform to [PSR-7 HTTP message interfaces](https://www.php-fig.org/psr/psr-7/). So, if you want to get the JSON objects from the API response, you may have code like

```php
$response = $openAPI->search('Love');
$searchResults = json_decode($response->getBody());
var_dump($searchResults->tracks->data);
```

## Test the Package

Just run `vendor/bin/phpunit`.

## License

Copyright 2018 KKBOX Technologies Limited

   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.
