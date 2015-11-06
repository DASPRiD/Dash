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
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/DASPRiD/Dash/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/DASPRiD/Dash/?branch=master)
[![Coverage Status](https://coveralls.io/repos/DASPRiD/Dash/badge.png?branch=master)](https://coveralls.io/r/DASPRiD/Dash)

Dash is a router which was initially meant to be a router for Zend Framework 3, but by now evolved into its very own
package. It still has a soft dependency on ZF's Service Manager 3, but that may eventually be cleared completely.

Route Configuration
===================

Route configuration is greatly simplified from ZF2, routes now have an (optional) 3 key indexed array as the first 3
parameters, the format for the shortcut parameters are:

`[path, defaults, methods]`

This may feel counter intuitive, but the idea is that the most frequently changed parameter appears earliest in the
order so that when defining child routes you can skip parameters you wish to inherit.

`path`, will expect a string, whereas `defaults` will expect an array and `method` will expect either an array of methods
this route should match, a single method in a string, or a string of `'*'` for all methods. An empty string `''` will
match no methods. Currently, passing no method defaults to `'*'`.

Along with the indexed shortcut parameters, named configuration can also be passed using key value pairs:

```
[
    'path' => '/foo',
    'defaults' => ['action' => 'bar', 'controller' => 'FooController'],
    'methods' => ['get', 'post']
]
```

Parameters and named values can be mixed, although the first 3 indexed items will always to be presumed to be the
parameters as ordered above.

Child Routes
============

Child routes can be simply defined in the `children` key of the configuration of the parent route:

```
'dash' => [
    'routes' => [
        'user' => ['/user', ['action' => 'index', 'controller' => 'UserController'], 'children' =>
        [
            'create' => ['/create', ['action' => 'create'], 'create', ['get', 'post']],
            'edit' => ['/edit/:id', ['action' => 'edit'], ['get', 'post'], 'constraints' => ['id' => '\d+']],
            'delete' => ['/delete/:id', ['action' => 'delete'], 'get', 'constraints' => ['id' => '\d+']],
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
'user' => ['/user', ['action' => index', 'controller' => 'UserController'], 'get', 'hostname' => 'login.example.com']
```

Similarly, if a given route should only match the https protocol:

```
'user' => ['/user', ['action' => index', 'controller' => 'UserController'], 'get', 'secure' => true]
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
'user' => ['/user', ['action' => index', 'controller' => 'UserController'], 'get']
```

```
Module B
'user' => ['path' => '/userinfo']
```

`/user` now will no longer match, but `/userinfo` will match in it's place.

Development
===========

When doing performance-dependent changes, make sure to compare the benchmarks between master and your branch. To run
them, execute the following command:

`php vendor/bin/athletic -p benchmark -b vendor/autoload.php`
