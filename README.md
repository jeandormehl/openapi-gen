# OpenApi Generator for Laravel, Lumen and Dingo.

## About

The `openapi-gen` package provides a convenient way to create OpenApi specifications for Laravel, Lumen or Dingo using a single configuration file. It is aimed at lightweight microservices and can be parsed by various packages to enable functionality such as gateway and relay routing.

The idea came from the lack of such packages and to stay away from polluting Controllers, Models and such with millions of lines of annotations. Annotations also lack the ability to use variables as values.

This package uses [cebe/php-openapi](https://github.com/cebe/php-openapi) for most of the heavy lifting, including validation of the specification. Thanks for the great package!

## Table of Contents

* [Features](#features)
* [Installation](#installation)
  * [Laravel](#laravel)
  * [Lumen](#lumen)
  * [Dingo](#dingo)
* [Configuration](#configuration)
  * [Route](#route)
  * [Yaml](#yaml)
  * [OpenApi](#openapi)
  * [Info](#info)
  * [Servers](#servers)
  * [Security](#security)
  * [Tags](#tags)
  * [ExternalDocs](#externalDocs)
  * [Components](#components)
    * [Schemas](#schemas)
    * [Responses](#responses)
    * [Parameters](#parameters)
    * [RequestBodies](#requestBodies)
    * [Headers](#headers)
    * [SecuritySchemes](#securitySchemes)
  * [Paths](#paths)
* [Todo](#todo)
* [License](#license)

## Features

* Auto generate basic schema definitions from model classes.
* Auto generate common HTTP responses used by most REST API's.
* Entire specification is stored nicely and neatly in one configuration file.
* Create any OpenApi object using simple arrays.

## Installation

Require `jeandormehl/openapi-gen` package in your composer.json and update your dependencies:

```sh
$ composer require jeandormehl/openapi-gen
```

### Laravel

Add `Rapid\OAS\Providers\ServiceProvider` to your `config/app.php` providers array.

Publish the minimum configuration:

```sh
$ php artisan vendor:publish --provider="Rapid\OAS\Providers\ServiceProvider"
```

**Note:** Package autodiscovery is not used because Dingo uses a different service provider to Laravel/Lumen. Will find a way to combine them all into one provider at some point.

### Lumen

Copy the config file:

```sh
$ mkdir -p config
$ cp -R vendor/jeandormehl/openapi-gen/config/oas.php config/oas.php
```

Load the configuration into `bootstrap/app.php`:

```php
$app->configure('oas');
```

Register the service provider:

```php
$app->register(Rapid\OAS\Providers\ServiceProvider::class);
```

### Dingo

Follow the instructions for either Laravel or Lumen, depending on what you're using.

When registering the service provider, replace with `Rapid\OAS\Providers\DingoServiceProvider::class`

**Note:** When using Dingo, your API_PREFIX will be prepended to the route that is registered.

## Configuration

The `oas.php` file contains your entire specification and depending on its contents, the specification will be generated. Basic validation is also provided so if you make a mistake in your config file, the package should give you an idea of where and how to correct the issue.

The file consists of arrays matching the OpenApi specification. When I created this package, OpenApi was currently on version `3.0.2`.

Here are useful links to ensure validity of your configuration:

* [OpenAPI Specification on Github](https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md)
* [OpenAPI Map](https://openapi-map.apihandyman.io/?version=3.0)

### route

The `route` key controls the route that is registered to access the specification. Here you enable/disable the route, set the path and prefix and add middleware if necessary.

```php
  ...

  'route' => [
      'enabled'    => true,
      'prefix'     => '',
      'path'       => 'docs',
      'middleware' => [],
  ],

  ...
```

**Note:** When using Dingo, your API_PREFIX will be prepended to the route that is registered.

### yaml

The `yaml` key defines the path to output the specification as a .yml file. By default, the file is placed in `storage/app/oas.yml`. To generate the yaml file, run the following artisan command:

```sh
$ php artisan oas:yaml
```

### openapi

The `openapi` key specifies the OAS version. This defaults to `3.0.2`.

```php
  ...

  'openapi' => \Rapid\OAS\OpenApi::VERSION,

  ...
```

Please see the [oasObject](https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#oasObject) for more information.

### info

The `info` key represent an OAS info object.

Minimum configuration:

```php
  ...

  'info' => [
      'title'   => 'OpenApi',
      'version' => env('API_VERSION') ?? env('APP_VERSION', 'v1'),
  ],

  ...
```

Full configuration:

```php
  ...

  'info' => [
      'title'          => 'OpenApi',
      'description'    => 'This is the OpenApi specification package.',
      'termsOfService' => 'http://localhost/termsOfService',
      'contact'        => [
          'name'  => 'John Smith',
          'url'   => 'http://localhost/me',
          'email' => 'john.smith@company.com',
      ],
      'license' => [
          'name' => 'Apache-2.0',
          'url'  => 'http://www.apache.org/licenses/LICENSE-2.0',
      ],
      'version' => env('API_VERSION') ?? env('APP_VERSION', 'v1'),
  ],

  ...
```

Please see the [infoObject](https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#infoObject) for more information.

### servers

The `servers` key contains arrays of OAS server objects.

Mimimum configuration:

```php
  ...

  'servers' => [
      [
          'url' => 'http://localhost:8080/v1',
      ],
  ],

  ...
```

Full configuration:

```php
  ...

  'servers' => [
      [
          'url'         => 'http://localhost:8080/v1',
          'description' => 'OpenApi HTTP Server',
          // server variables
          'variables'   => [
              'scheme' => [
                  'enum'        => ['http', 'https'],
                  'default'     => 'http',
                  'description' => 'The Transfer Protocol',
              ],
          ],
      ],
  ],

  ...
```

Please see the [serverObject](https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#serverObject) and [serverVariableObject](https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#serverVariableObject) for more information.

### security

The `security` key defines security requirement objects. The name used for each property MUST correspond to a securityScheme declared in the securitySchemes under the `components` object. These are required globally for use of the API. If the security scheme is of type `oauth2` or `openIdConnect`, then the value is a list of scope names required for the execution. For other security scheme types, the array MUST be empty.

Minimum configuration:

```php
  ...

  'security' => [
      'apiKey' => [],
      // if using oauth
      'oauth2' => [
          'view:users',
          'create:users',
      ],
  ],

  ...
```

Full configuration:


```php
  ...

  'security' => [
      'apiKey' => [],
      'http'   => [],
      'bearer' => [],
      'oauth2' => [
          'view:users',
          'create:users',
      ],
      'openIdConnect' => [
          'view:users',
          'create:users',
      ],
  ],

  ...
```

Please see the [securityRequirementObject](https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#securityRequirementObject) for more information.

### tags

The `tags` key contains arrays of OAS tag objects.

Minimum configuration:

```php
  ...

  'tags' => [
      [
          'name' => 'User',
      ],
  ],

  ...
```

Full configuration:

```php
  ...
  'tags' => [
      [
          'name'         => 'User',
          'description'  => 'API user models.',
          // see externalDocs section
          'externalDocs' => [
              'url'         => 'http://localhost/tags/users/externalDocs',
              'description' => 'User docs.',
          ],
      ],
  ],
  ...
```

Please see the [tagObject](https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#tagObject) for more information.

### externalDocs

The `externalDocs` key contains the external documentation OAS object.

Minimum configuration:

```php
  ...

  'externalDocs' => [
      'url' => 'http://localhost/externalDocs',
  ],

  ...
```

Full configuration:

```php
  ...

  'externalDocs' => [
      'url'         => 'http://localhost/externalDocs',
      'description' => 'External docs for OpenApi.',
  ],

  ...
```

Please see the [externalDocumentationObject](https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#externalDocumentationObject) for more information.

### components

The `components` key. Refer to [componentsObject](https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#componentsObject).

#### Schemas

The `schemas` key. Refer to [schemaObject](https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#schemaObject).

##### Auto generated model schemas.

This packages allows you to automatically generate basic schemas based on your Eloquent models. Providing an array of `Fully Qualified Class Names (FQCN)` inside a models array, the package will attempt to create the schemas for you which can later be referenced in other keys.

**Note:** Currently supports `MySQL`, `SQLite`, `PostgreSQL` and `Oracle`.

**Note:** These schema definitions are very basic, containing only the  following values:

* Title
* Description
* Required
* Properties

Properties will only contain the following attributes:

* Description
* Type
* Format
* Nullable
* Default

Properties defined inside the models hidden array and inside the configuration `hidden` tag will be excluded from the schema.

Minimum configuration:

```php
  ...

  'components' => [
      'schemas' => [
            ...

            'models' => ['App\\User' => []],

            ...
      ],
  ],

  ...
```

Full configuration:

```php
  ...

  'components' => [
      'schemas' => [
            ...

            'models' => [
                'App\\User' => [
                    'hidden' => ['password', 'updated_at', 'deleted_at']
                ],
            ],

            ...
      ],
  ],

  ...
```

##### Standard schema objects

The `schemas` key represent an array of OAS schema objects. Here you can define your custom schemas and reference them later in your configuration. It is highly recommended to define all your schemas here and simply reference them in the configuration as opposed to creating inline schemas, although the functionality does exist.

Example Enum schema:

```php
  ...

  'components' => [
      'schemas' => [
          ...

          'Status' => [
              'title'       => 'Status',
              'description' => 'Current status of the user.',
              'enum'        => ['Active', 'Pending', 'Disabled'],
              'default'     => 'Pending',
              'type'        => \cebe\openapi\spec\Type::STRING,
          ],

          ...
      ],
  ],

  ...
```

Example Object schema:

```php
  ...

  'components' => [
      'schemas' => [
          ...

          'User' => [
              'title'       => 'User',
              'description' => 'The User object.',
              'type'        => \cebe\openapi\spec\Type::OBJECT,
              'required'   => ['email', 'status'],
              'properties' => [
                  'email' => [
                      'type' => \cebe\openapi\spec\Type::STRING,
                      'title' => 'Email',
                      'description' => 'The users email address.',
                      // pattern => '',
                  ],
                  'first_name' => [
                      'type' => \cebe\openapi\spec\Type::STRING,
                      'title' => 'FirstName',
                      'description' => 'The users first name.',
                  ],
                  // $refs should always be an array as seen here
                  'status' => ['$ref' => '#/components/schemas/Status']
              ],
              'maxProperties' => 3,
              'minProperties' => 3,
          ],

          ...
      ],
  ],

  ...
```

**Note:** The `schema` OAS object contains many properties depending on the type of schema definition you are using.

Refer to [schemaObject](https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#schemaObject) for all additional properties and types.


#### Responses

##### Auto generated common HTTP responses

The `responses` key contains a key called `statusCodes` which defines common HTTP responses used in almost all REST APIs. This key is simply an array of HTTP status codes which will generate the responses. At this time, all the responses generated are `application/json` and can be referenced anywhere in your specification.

**Note:** Do not define a 200 OK response here unless that is all you wish to return to services consuming your API. 200 responses should generally be defined within `paths` or `operations` using `schema` and `requestBodies` $refs.

Configuration:

```php
  ...

  'components' => [
      ...

      'responses' => [
          ...

          // common responses use application/json content types.
          'statusCodes' => [400, 401, 403, 404, 405, 418, 422, 500, 502, 503],

          ...
      ],
  ],

  ...
```

##### Standard response objects

The `responses` key represent an array of OAS response objects. Here you can define your custom responses and reference them later in your configuration. It is highly recommended to define all your responses here and simply reference them in the configuration as opposed to creating inline responses, although the functionality does exist.

Configuration:

```php
  ...

  'components' => [
      ...

      'responses' => [
          ...

          'TokenResponse' => [
              'description' => 'The oauth2 token response.',
              'content'     => [
                  // mediaType object
                  'application/json' => [
                      // using schemas $ref. Try to stick to $refs but inline can also be used
                      'schema' => ['$ref' => '#/components/schemas/TokenResponse'],
                  ],
              ],
          ],

          ...
      ],
  ],

  ...
```

#### Parameters

The `parameters` key is used to specify parameters and can be referenced anywhere in your specification. It is highly recommended that you define all `parameters` in this key and reference them throughout your specification.

Configuration:

```php
  ...

  'components' => [
      ...

      'parameters' => [
          ...

          'Identifier' => [
              'name'            => 'Identifier',
              'in'              => 'path',
              'description'     => 'The model identifier',
              'required'        => true,
              'deprecated'      => false,
              'allowEmptyValue' => false,

              // you can use inline schema objects here but its highly recommended to use $refs to schema objects
              'schema' => ['$ref' => '#components/schemas/Identifier'],
              // 'schema' => [
              //     'type'    => \cebe\openapi\spec\Type::INTEGER,
              //     'format'  => \Rapid\OAS\Spec\Format::INT32,
              //     'example' => 1,
              // ],
          ],

          ...
      ],
  ],

  ...

```

Please refer to [parameterObject](https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#parameterObject) for more information.

#### RequestBodies

The `requestBodies` key represent an array of OAS requestBody objects. Its is recommended that all requestBodies are defined and simply referenced where needed within the specification.

Configuration:

```php
  ...

  'components' => [
      ...

      'requestBodies' => [
          ...

          'User' => [
              'description' => 'User request body.',
              'required'    => true,
              'content'     => [
                  'application/json' => [
                      // using schema $ref. Inline can also be specified.
                      'schema'  => ['$ref' => '#/components/schemas/User'],
                  ],
              ],
          ],

          ...
      ],

      ...
  ],

```

#### Headers

The `headers` key represent an array of OAS header objects. Its is recommended that all headers are defined and simply referenced where needed within the specification.

Minimum configuration:

```php
  ...

  'components' => [
      ...

      'headers' => [
          ...

          'X-User-Id' => [
              'description' => 'The User Identifier passed between microservices.',
              'required'   => true,
              'deprecated' => false,
              // use $ref wherever possible
              'schema' => ['$ref' => '#/components/schemas/Identifier'],
          ],

          ...
      ],

      ...
  ],

```

Full configuration:

```php
  ...

  'components' => [
      ...

      'headers' => [
          ...

          'Accept' => [
              'description' => 'The Accept header to pass to all requests.',
              'required'    => true,
              'deprecated'  => false,
              'content'     => [
                  // mediaType object
                  'application/json' => [
                      // using an inline schemas. Try to stick to $refs
                      'schema' => ['type' => \cebe\openapi\spec\Type::STRING],
                      'examples' => [
                          'application/json' => ['value' => 'application/json'],
                          'application/vnd.github.v3+json' => ['value' => 'application/vnd.github.v3+json'],
                      ],
                  ],
              ],
          ],

          ...
      ],

      ...
  ],

```

Please refer to the [headerObject](https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#headerObject) and the [mediaTypeObject](https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#mediaTypeObject) for more info on how these are created.

#### SecuritySchemes

The `securityScheme` key. These link directly to the `security` key and should be defined before being using in the `security` or `operation` objects.

Full configuration:

```php
  ...

  'components' => [
      ...

      'securitySchemes' => [
          ...

          // apiKey example
          'apiKey' => [
              'type'        => 'apiKey',
              'description' => 'Unique key used to authenticate against API.',
              'name'        => 'X-Application-Id',
              'in'          => 'header',
          ],

          // possible http schemes: basic, bearer
          // basic example
          'basic' => [
              'type'        => 'http',
              'description' => 'HTTP basic scheme to authenticate against API.',
              'scheme'      => 'basic',
          ],

          // bearer example
          'bearer' => [
              'type'         => 'http',
              'description'  => 'HTTP bearer scheme to authenticate against API.',
              'scheme'       => 'bearer',
              'bearerFormat' => 'bearer',
          ],

          // oauth2 example
          'oauth2' => [
              'type'        => 'oauth2',
              'description' => 'OAuth2 authentication flows to authenticate against API.',
              'flows' => [
                  // implicit
                  'implicit' => [
                      'authorizationUrl' => 'http://localhost/authorizationUrl',
                      'scopes'           => [
                          'view:users'   => 'View all user information',
                          'create:users' => 'Create a new user.',
                      ],
                  ],

                  // password
                  'password' => [
                      'tokenUrl'   => 'http://localhost/tokenUrl',
                      'refreshUrl' => 'http://localhost/refreshUrl',
                      'scopes'     => [
                          'view:users'   => 'View all user information',
                          'create:users' => 'Create a new user.',
                      ],
                  ],

                  // clientCredentials
                  'clientCredentials' => [
                      'tokenUrl'   => 'http://localhost/tokenUrl',
                      'refreshUrl' => 'http://localhost/refreshUrl',
                      'scopes'     => [
                          'view:users'   => 'View all user information',
                          'create:users' => 'Create a new user.',
                      ],
                  ],

                  // authorizationCode
                  'authorizationCode' => [
                      'authorizationUrl' => 'http://localhost/authorizationUrl',
                      'tokenUrl'         => 'http://localhost/tokenUrl',
                      'scopes'           => [
                          'view:users'   => 'View all user information',
                          'create:users' => 'Create a new user.',
                      ],
                  ],
              ],
          ],

          // openIdConnect example
          'openIdConnect' => [
              'type'             => 'openIdConnect',
              'description'      => 'OpenIdConnect authentication for API.',
              'openIdConnectUrl' => 'https://open.id/connect',
          ],

          ...
      ],

      ...
  ],

```

Please refer to [securitySchemeObject](https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#securitySchemeObject), [oauthFlowsObject](https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#oauthFlowsObject) and [oauthFlowObject](https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#oauthFlowObject) for more details.


### paths

The `paths` key. Refer to [pathsObject](https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#pathsObject).

This is the most important key of the entire configuration. This will define your paths and operations. Where possible, try to only use $refs and keep this configuration as clean and neat as possible.

```php
  ...

  'paths' => [
      // path item
      '/users' => [
          // operation (GET)
          'get' => [
              'tags'        => ['User'],
              'summary'     => 'Get Users',
              'description' => 'Get a paginated result set of User objects.',
              'operationId' => 'user.index',
              // try stick to $refs
              'responses'   => [
                  '200' => ['$ref' => '#/components/responses/UsersList'],
                  '400' => ['$ref' => '#/components/responses/400'],
                  '401' => ['$ref' => '#/components/responses/401'],
                  '403' => ['$ref' => '#/components/responses/403'],
              ],
          ],

          // operation (POST)
          'post' => [
              'tags'        => ['User'],
              'summary'     => 'Create User',
              'description' => 'Create a new user.',
              'operationId' => 'user.create',
              'requestBody' => ['$ref' => '#/components/requestBodies/User'],
              'responses' => [
                  '200' => ['$ref' => '#/components/responses/User'],
                  '400' => ['$ref' => '#/components/responses/400'],
                  '401' => ['$ref' => '#/components/responses/401'],
                  '403' => ['$ref' => '#/components/responses/403'],
                  '404' => ['$ref' => '#/components/responses/404'],
                  '418' => ['$ref' => '#/components/responses/418'],
                  '422' => ['$ref' => '#/components/responses/422'],
              ],
          ],
      ],
  ],

  ...
```

## TODO

* Add `examples` objects to specification. See [exampleObject](https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#exampleObject).
* Add `links` objects to specification.  See [linkObject](https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#linkObject).
* Add `callbacks` objects to specification. See [callbackObject](https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#callbackObject).

## License

Released under the Apache-2.0 License, see [LICENSE](LICENSE.md).
