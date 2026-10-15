# Illuminate\Database\QueryException - Internal Server Error

SQLSTATE[42S22]: Column not found: 1054 Unknown column 'es_activa' in 'where clause' (Connection: mysql, Host: 127.0.0.1, Port: 3307, Database: festival_acb, SQL: select * from `categorias` where `es_activa` = 1 limit 1)

PHP 8.4.24
Laravel 12.69.2
127.0.0.1:8000

## Stack Trace

0 - vendor/laravel/framework/src/Illuminate/Database/Connection.php:838
1 - vendor/laravel/framework/src/Illuminate/Database/Connection.php:794
2 - vendor/laravel/framework/src/Illuminate/Database/Connection.php:411
3 - vendor/laravel/framework/src/Illuminate/Database/Query/Builder.php:3505
4 - vendor/laravel/framework/src/Illuminate/Database/Query/Builder.php:3490
5 - vendor/laravel/framework/src/Illuminate/Database/Query/Builder.php:4080
6 - vendor/laravel/framework/src/Illuminate/Database/Query/Builder.php:3489
7 - vendor/laravel/framework/src/Illuminate/Database/Eloquent/Builder.php:902
8 - vendor/laravel/framework/src/Illuminate/Database/Eloquent/Builder.php:884
9 - vendor/laravel/framework/src/Illuminate/Database/Concerns/BuildsQueries.php:366
10 - app/Modules/Categorias/Models/Categoria.php:145
11 - app/Modules/Usuarios/Controllers/InicioController.php:75
12 - vendor/laravel/framework/src/Illuminate/Routing/ControllerDispatcher.php:46
13 - vendor/laravel/framework/src/Illuminate/Routing/Route.php:265
14 - vendor/laravel/framework/src/Illuminate/Routing/Route.php:211
15 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:822
16 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:180
17 - vendor/laravel/framework/src/Illuminate/Routing/Middleware/SubstituteBindings.php:50
18 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
19 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/VerifyCsrfToken.php:87
20 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
21 - vendor/laravel/framework/src/Illuminate/View/Middleware/ShareErrorsFromSession.php:48
22 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
23 - vendor/laravel/framework/src/Illuminate/Session/Middleware/StartSession.php:120
24 - vendor/laravel/framework/src/Illuminate/Session/Middleware/StartSession.php:63
25 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
26 - vendor/laravel/framework/src/Illuminate/Cookie/Middleware/AddQueuedCookiesToResponse.php:36
27 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
28 - vendor/laravel/framework/src/Illuminate/Cookie/Middleware/EncryptCookies.php:74
29 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
30 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:137
31 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:821
32 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:800
33 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:764
34 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:753
35 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php:200
36 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:180
37 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/TransformsRequest.php:21
38 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/ConvertEmptyStringsToNull.php:31
39 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
40 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/TransformsRequest.php:21
41 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/TrimStrings.php:51
42 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
43 - vendor/laravel/framework/src/Illuminate/Http/Middleware/ValidatePostSize.php:27
44 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
45 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/PreventRequestsDuringMaintenance.php:109
46 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
47 - vendor/laravel/framework/src/Illuminate/Http/Middleware/HandleCors.php:61
48 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
49 - vendor/laravel/framework/src/Illuminate/Http/Middleware/TrustProxies.php:58
50 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
51 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/InvokeDeferredCallbacks.php:22
52 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
53 - vendor/laravel/framework/src/Illuminate/Http/Middleware/ValidatePathEncoding.php:26
54 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
55 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:137
56 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php:175
57 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php:144
58 - vendor/laravel/framework/src/Illuminate/Foundation/Application.php:1220
59 - public/index.php:20
60 - vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php:23

## Request

GET /

## Headers

* **host**: 127.0.0.1:8000
* **connection**: keep-alive
* **sec-ch-ua**: "Not=A?Brand";v="99", "Brave";v="151", "Chromium";v="151"
* **sec-ch-ua-mobile**: ?0
* **sec-ch-ua-platform**: "Linux"
* **upgrade-insecure-requests**: 1
* **user-agent**: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36
* **accept**: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8
* **sec-gpc**: 1
* **sec-fetch-site**: none
* **sec-fetch-mode**: navigate
* **sec-fetch-user**: ?1
* **sec-fetch-dest**: document
* **accept-encoding**: gzip, deflate, br, zstd
* **accept-language**: es-419,es;q=0.9
* **cookie**: csrftoken=oQT3ejhhlMFP0jRl9T6AfyaqwrY5XszV; phpMyAdmin=128fa867a18e6cbd7bef876f83ff20f0; pma_lang=es; XSRF-TOKEN=eyJpdiI6Imkya014c0trcnIrYXpyREwwbUF2RUE9PSIsInZhbHVlIjoiZklhRnQ0NEQyZGxRQ1ZQWlEraThuK0w0cHMxK0tJWUpxZ2lRdWN2NHp5UzdSQkpQNml2eEVqYVZUU2Jpd040VGJ0OWovTlpXdW5hcUFRekRINnp1NUxKRzdoMDhuRVVvQXk0OWliOGxwTTVEYitPNjNhUDM2OFB1dTA1a2g0TkEiLCJtYWMiOiI0YjdjYmVmMGFmNzhhZmNmNDhlNDZlZmIyNDQzOWI5NDNhM2EzN2RkMTA5ZWEwODJkOTAzNDBhY2EwMGNiMTZlIiwidGFnIjoiIn0%3D; aqui-canta-bolivia-session=eyJpdiI6IlJhMnFPL05PaGl2OU5GMGIzUDRqakE9PSIsInZhbHVlIjoiT3FUTE16cUR4ek1iS3BDNXM4NG5GbXNjWWI4TUVEQ0RVN0MrTDF1ZWMyM3AxcHZQeGw3QlJlSk9JcVk1RjRocWJ0WFpnL2NidlBic3ZOb0FDSXRWSVR2VU5aL2krKzBMckxHU3VYeXJ0eTF4ckRHQW1hZmVKbTFUQWRCcGlkQTUiLCJtYWMiOiJhMjdkODAwYzRkNzMwODRiMDIwMjEwNmE2NDVmNGFiYWRjMDVkOGE2MTM4OWRiOGMxMzAzOGZkYzYzMTdhMmUzIiwidGFnIjoiIn0%3D

## Route Context

controller: App\Modules\Usuarios\Controllers\InicioController@publico
route name: inicio
middleware: web

## Route Parameters

No route parameter data available.

## Database Queries

* mysql - select * from `sessions` where `id` = 'gP9hG3POXBHX8qmcMkocbzvUahhYYhNTgJtbUKzk' limit 1 (2.01 ms)
* mysql - select exists (select 1 from information_schema.tables where table_schema = schema() and table_name = 'categorias' and table_type in ('BASE TABLE', 'SYSTEM VERSIONED')) as `exists` (1.97 ms)
* mysql - select * from `categorias` order by `id` asc (0.97 ms)
