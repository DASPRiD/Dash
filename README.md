```
                            ▄▄
                       █▀▀▄█░░█▄         ▄▄▄▄▀▀▀▀▀▀▄
                        ▀▄░░██░░█   █▀▀▀▀▀▓▓▓▓▓▓▒▓▄▄▓▀▄
    ▄▄▄▄▄▄  █▄         ▄▄█░░▀▄░█ ▄▄█▀▄▄▀▀█▒▒▒▄▄▀░▀▀██
  ▄█▀▀▀▀▀▀██▓█        █░▀█░░█░█▀▀▄▄█░░░▄▀▀▀▀░░░░░░░█
▄█▄▄▄░░░▒▒▒▀█▓█        ▀▄░█▀█▀▀█▀▄█▄▄▄▀░░░░▀▀█▀▀██░█
     ▀▀▄▓▓▓▒▒█▄▀▄      ▄▄█▀▀▄░░░█    █░░░░░░ ▀███░░░█▄
        ▀▄▄▓░▒▒▀█▄█▀▀▀▀▒▀█▄█▀░░█▀▀▀▀▀░█░░░░░░░░░░░▄░█
           ▀▀▄▄▓▓░░░░░▓▓▓▓▄█ ▀▄▄░▀░░░░░░░░░▄▄▄▄▄▄▄▄▄▄▀▀
               ▀▄▓▓▓▓▓▄▄▄▀  █▓▒░░░░░░░░░░░█▄▀▀▀█▄▄▄▄▄▄▄
                 ▀▀▀▀▀      ▄▀█░▓▒░░░░▄░░░█░▀░▄▄░░█▒▒▒▒▒▒▒█
                         ▄▄▄▀▀░░░░░░░░▄██▀▀▀▀▀▀█▀░░█▀▀▀▄▄▄▀
    DASH                █░░░░░▄▄▀█▄▄▄▀▀         █░░░█
                        █░░▄▄█▒▒▄▀               █░▄▀
                        ▀▀▀  ▀▀▀                  ▀
```
--------------------------------------------------------------------------------

[![Build Status](https://api.travis-ci.org/DASPRiD/Dash.png?branch=master)](http://travis-ci.org/DASPRiD/Dash)
[![Coverage Status](https://coveralls.io/repos/DASPRiD/Dash/badge.png?branch=master)](https://coveralls.io/r/DASPRiD/Dash)

This package is the router prototype for Zend Framework 3. It isn't by any means
complete yet, but evolving quickly. It was just quickly hacked together so it
can be demonstrated at ZendConEU.

Right now it is not usable within Zend Framework 2, but a simple bridge class
can be created to make it work.

Route Configuration
===================

Route configuration is greatly simplified from ZF2, routes now have an (optional) 4 key indexed array as the first 4 
parameters, the format for the shortcut parameters are:

`[path, action, controller, methods]`

This may feel counter intuitive, but the idea is that the most frequently changed parameter appears earliest in the
order so that when defining child routes you can skip parameters you wish to inherit.

`path`, `action` and `controller` will expect a string, whereas `method` will expect either an array of methods this
route should match, a single method in a string, or a string of `'*'` for all methods. An empty string `''` will 
match no methods. Currently, passing no method defaults to `'get'`.

Along with the indexed shortcut parameters, named configuration can also be passed using key value pairs:

```
[
    'path' => '/foo',
    'action' => 'bar',
    'controller' => 'FooController',
    'methods' => ['get', 'post']
]
```

Parameters and named values can be mixed, although the first 4 indexed items will always to be presumed to be the 
parameters as ordered above.

Child Routes
============

Child routes can be simply defined in the `children` key of the configuration of the parent route:

```
'dash_router' => [
    'routes' => [
        'user' => ['/user', 'user', 'index', 'children' => 
        [
            'create' => ['/create', 'Application\Controller\UserController', 'create', ['get', 'post']],
            'edit' => ['/edit/:id', 'edit', 'Application\Controller\UserController', ['get', 'post'], 'constraints' => ['id' => '\d+']],
            'delete' => ['/delete/:id', 'edit', 'Application\Controller\UserController', 'constraints' => ['id' => '\d+']],
        ]],
    ],
],
```

Route Types
===========

The router no longer has mulitple route types, instead the `Generic` route handles all aspects of HTTP routing. 
Instead of specificing the route type in the configuration, the router now knows how to handle all routes based 
solely on the configuration. 

For example, if you want a given route only to match a specific hostname, simply define the correct key value 
pair in that route's configuration:

```
'user' => ['/user',  index', 'Application\Controller\UserController', ['get'], 'hostname' => 'login.example.com']
```

Similarly, if a given route should only match the https protocol:

```
'user' => ['/user', index', 'Application\Controller\UserController', ['get'], 'secure' => true]
```

Overloading
===========

One confusion that's anticipate to be a minor problem is the confusion of how to overload a given route parameter
from within a different module. This is easily achieved by defining the relevant key\value in the configuration that 
is intended to override the route. 
*Overwriting a route parameter by the shortcut key will not take effect because it will only be added to the end of*
*the configuration array*


```
Module A
'user' => ['/user', index', 'Application\Controller\UserController', ['get']]
```

```
Module B
'user' => ['path' => '/userinfo']
```

`/user` now will no longer match, but `/userinfo` will match in it's place.

