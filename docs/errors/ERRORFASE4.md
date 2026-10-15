# Illuminate\Database\QueryException - Internal Server Error

SQLSTATE[42S22]: Column not found: 1054 Unknown column 'inscripciones.fecha_presentacion' in 'order clause' (Connection: mysql, Host: 127.0.0.1, Port: 3307, Database: festival_acb, SQL: select `presentaciones`.*, `jurado_presentacion`.`id_usuario` as `pivot_id_usuario`, `jurado_presentacion`.`id_presentacion` as `pivot_id_presentacion`, `jurado_presentacion`.`es_secreto` as `pivot_es_secreto`, `jurado_presentacion`.`es_controlador` as `pivot_es_controlador`, `jurado_presentacion`.`estado` as `pivot_estado` from `presentaciones` inner join `jurado_presentacion` on `presentaciones`.`id` = `jurado_presentacion`.`id_presentacion` where `jurado_presentacion`.`id_usuario` = 6 and `presentaciones`.`estado` = POR_PARTICIPAR order by `inscripciones`.`fecha_presentacion` asc, `inscripciones`.`hora_presentacion` asc limit 1)

PHP 8.4.24
Laravel 12.69.2
localhost:8000

## Stack Trace

0 - vendor/laravel/framework/src/Illuminate/Database/Connection.php:838
1 - vendor/laravel/framework/src/Illuminate/Database/Connection.php:794
2 - vendor/laravel/framework/src/Illuminate/Database/Connection.php:411
3 - vendor/laravel/framework/src/Illuminate/Database/Query/Builder.php:3505
4 - vendor/laravel/framework/src/Illuminate/Database/Query/Builder.php:3490
5 - vendor/laravel/framework/src/Illuminate/Database/Query/Builder.php:4080
6 - vendor/laravel/framework/src/Illuminate/Database/Query/Builder.php:3489
7 - vendor/laravel/framework/src/Illuminate/Database/Eloquent/Builder.php:902
8 - vendor/laravel/framework/src/Illuminate/Database/Eloquent/Relations/BelongsToMany.php:932
9 - vendor/laravel/framework/src/Illuminate/Database/Eloquent/Relations/BelongsToMany.php:866
10 - app/Modules/Usuarios/Controllers/InicioController.php:48
11 - vendor/laravel/framework/src/Illuminate/Routing/ControllerDispatcher.php:46
12 - vendor/laravel/framework/src/Illuminate/Routing/Route.php:265
13 - vendor/laravel/framework/src/Illuminate/Routing/Route.php:211
14 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:822
15 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:180
16 - app/Http/Middleware/EnsureUserHasRol.php:23
17 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
18 - vendor/laravel/framework/src/Illuminate/Auth/Middleware/EnsureEmailIsVerified.php:41
19 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
20 - vendor/laravel/framework/src/Illuminate/Routing/Middleware/SubstituteBindings.php:50
21 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
22 - vendor/laravel/framework/src/Illuminate/Auth/Middleware/Authenticate.php:63
23 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
24 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/VerifyCsrfToken.php:87
25 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
26 - vendor/laravel/framework/src/Illuminate/View/Middleware/ShareErrorsFromSession.php:48
27 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
28 - vendor/laravel/framework/src/Illuminate/Session/Middleware/StartSession.php:120
29 - vendor/laravel/framework/src/Illuminate/Session/Middleware/StartSession.php:63
30 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
31 - vendor/laravel/framework/src/Illuminate/Cookie/Middleware/AddQueuedCookiesToResponse.php:36
32 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
33 - vendor/laravel/framework/src/Illuminate/Cookie/Middleware/EncryptCookies.php:74
34 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
35 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:137
36 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:821
37 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:800
38 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:764
39 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:753
40 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php:200
41 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:180
42 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/TransformsRequest.php:21
43 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/ConvertEmptyStringsToNull.php:31
44 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
45 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/TransformsRequest.php:21
46 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/TrimStrings.php:51
47 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
48 - vendor/laravel/framework/src/Illuminate/Http/Middleware/ValidatePostSize.php:27
49 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
50 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/PreventRequestsDuringMaintenance.php:109
51 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
52 - vendor/laravel/framework/src/Illuminate/Http/Middleware/HandleCors.php:61
53 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
54 - vendor/laravel/framework/src/Illuminate/Http/Middleware/TrustProxies.php:58
55 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
56 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/InvokeDeferredCallbacks.php:22
57 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
58 - vendor/laravel/framework/src/Illuminate/Http/Middleware/ValidatePathEncoding.php:26
59 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
60 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:137
61 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php:175
62 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php:144
63 - vendor/laravel/framework/src/Illuminate/Foundation/Application.php:1220
64 - public/index.php:20
65 - vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php:23

## Request

GET /jurado/inicio

## Headers

* **host**: localhost:8000
* **connection**: keep-alive
* **sec-ch-ua**: "Not=A?Brand";v="99", "Brave";v="151", "Chromium";v="151"
* **sec-ch-ua-mobile**: ?0
* **sec-ch-ua-platform**: "Linux"
* **upgrade-insecure-requests**: 1
* **user-agent**: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36
* **accept**: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8
* **sec-gpc**: 1
* **sec-fetch-site**: same-origin
* **sec-fetch-mode**: navigate
* **sec-fetch-user**: ?1
* **sec-fetch-dest**: document
* **referer**: http://localhost:8000/
* **accept-encoding**: gzip, deflate, br, zstd
* **accept-language**: es-419,es;q=0.6
* **cookie**: csrftoken=WifRwVHlrRF4m6XpgHZLs3qdunoPINRQ; sessionid=b3m2x6s2jm2wvyt7y2orqw7viq648od7; pma_lang=es; XSRF-TOKEN=eyJpdiI6ImM4S2NGaDBlaGhETFMrMUhVTlhzUFE9PSIsInZhbHVlIjoiMGxkMU1Qa3E1cnNmbW5KS1ZPWEl4c3Jnclp3bUhBZmpOaTBTSkpQVldFRkZhTDU5UmFHalZiTmdqZ2Jva2NrVlJwRkNXNVU0bjlKV0x6RzFsQ2tVcXZoUnRTdTJyMXc1ZGloeGdGNVo0U0txMFBucHRIL2dOOE0yOWMxWGxXK00iLCJtYWMiOiI5Y2M1N2I2NDk3ZTRlYjllZDg5Yjg1MTdhYjgwYjU0NDE3NmI5YzRlMzRlMTUyNWJkYWRlYzJjZTBlZDUzMDAwIiwidGFnIjoiIn0%3D; acb-canta-bolivia-session=eyJpdiI6Inl3UzVqd2dvbTFYSGZDZ2V4bXo2aEE9PSIsInZhbHVlIjoiNFczQ0ljTHJJZm82YjNyZzdtZGRVVmtHaHBRdzdaSG9WcHlZQ3d2aW52N0xTR3JEdmdjRmx4OTE3WFdYY2kraUpVb2F6TUpkTmZhQ1NyYUd1dUVjQ0RZT3g5M1JlQXR2cnNrVi82TlhpZndxZFl1Rm9kM0pDekszSjFXK1FFM0UiLCJtYWMiOiI2N2RmNjg5NGM0MGYzMzViYTZmMmIyNmQ5NDJkNTQzNzNkZWYyM2JkMzVhNTc5ODdlMTkyNDZiNzU4NTY2ODU3IiwidGFnIjoiIn0%3D

## Route Context

controller: App\Modules\Usuarios\Controllers\InicioController@jurado
route name: jurado.inicio
middleware: web, auth, verified, rol:JURADO

## Route Parameters

No route parameter data available.

## Database Queries

* mysql - select * from `sessions` where `id` = '4aJtFILYkYtG46zVGWP7lwtt56Sw9LlO3jfu6pav' limit 1 (1.6 ms)
* mysql - select * from `users` where `id` = 6 limit 1 (0.49 ms)
* mysql - select * from `presentaciones` where `estado` in ('EN_ESCENARIO', 'PARTICIPANDO') order by `updated_at` desc limit 1 (0.61 ms)
