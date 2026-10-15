# Illuminate\Database\QueryException - Internal Server Error

SQLSTATE[42S02]: Base table or view not found: 1146 Table 'festival_acb.jurado_presentacion' doesn't exist (Connection: mysql, Host: 127.0.0.1, Port: 3307, Database: festival_acb, SQL: select `presentaciones`.*, `jurado_presentacion`.`id_usuario` as `pivot_id_usuario`, `jurado_presentacion`.`id_presentacion` as `pivot_id_presentacion`, `jurado_presentacion`.`es_secreto` as `pivot_es_secreto`, `jurado_presentacion`.`es_controlador` as `pivot_es_controlador`, `jurado_presentacion`.`estado` as `pivot_estado` from `presentaciones` inner join `jurado_presentacion` on `presentaciones`.`id` = `jurado_presentacion`.`id_presentacion` where `jurado_presentacion`.`id_usuario` = 6 order by `updated_at` desc)

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
9 - resources/views/jurado/inicio.blade.php:15
10 - vendor/laravel/framework/src/Illuminate/Filesystem/Filesystem.php:123
11 - vendor/laravel/framework/src/Illuminate/Filesystem/Filesystem.php:124
12 - vendor/laravel/framework/src/Illuminate/View/Engines/PhpEngine.php:57
13 - vendor/laravel/framework/src/Illuminate/View/Engines/CompilerEngine.php:76
14 - vendor/laravel/framework/src/Illuminate/View/View.php:208
15 - vendor/laravel/framework/src/Illuminate/View/View.php:191
16 - vendor/laravel/framework/src/Illuminate/View/View.php:160
17 - vendor/laravel/framework/src/Illuminate/Http/Response.php:78
18 - vendor/laravel/framework/src/Illuminate/Http/Response.php:34
19 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:939
20 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:906
21 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:821
22 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:180
23 - app/Http/Middleware/EnsureUserHasRol.php:23
24 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
25 - vendor/laravel/framework/src/Illuminate/Auth/Middleware/EnsureEmailIsVerified.php:41
26 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
27 - vendor/laravel/framework/src/Illuminate/Routing/Middleware/SubstituteBindings.php:50
28 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
29 - vendor/laravel/framework/src/Illuminate/Auth/Middleware/Authenticate.php:63
30 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
31 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/VerifyCsrfToken.php:87
32 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
33 - vendor/laravel/framework/src/Illuminate/View/Middleware/ShareErrorsFromSession.php:48
34 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
35 - vendor/laravel/framework/src/Illuminate/Session/Middleware/StartSession.php:120
36 - vendor/laravel/framework/src/Illuminate/Session/Middleware/StartSession.php:63
37 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
38 - vendor/laravel/framework/src/Illuminate/Cookie/Middleware/AddQueuedCookiesToResponse.php:36
39 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
40 - vendor/laravel/framework/src/Illuminate/Cookie/Middleware/EncryptCookies.php:74
41 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
42 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:137
43 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:821
44 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:800
45 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:764
46 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:753
47 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php:200
48 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:180
49 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/TransformsRequest.php:21
50 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/ConvertEmptyStringsToNull.php:31
51 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
52 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/TransformsRequest.php:21
53 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/TrimStrings.php:51
54 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
55 - vendor/laravel/framework/src/Illuminate/Http/Middleware/ValidatePostSize.php:27
56 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
57 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/PreventRequestsDuringMaintenance.php:109
58 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
59 - vendor/laravel/framework/src/Illuminate/Http/Middleware/HandleCors.php:61
60 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
61 - vendor/laravel/framework/src/Illuminate/Http/Middleware/TrustProxies.php:58
62 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
63 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/InvokeDeferredCallbacks.php:22
64 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
65 - vendor/laravel/framework/src/Illuminate/Http/Middleware/ValidatePathEncoding.php:26
66 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
67 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:137
68 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php:175
69 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php:144
70 - vendor/laravel/framework/src/Illuminate/Foundation/Application.php:1220
71 - public/index.php:20
72 - vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php:23

## Request

GET /jurado/inicio

## Headers

* **host**: localhost:8000
* **connection**: keep-alive
* **cache-control**: max-age=0
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
* **referer**: http://localhost:8000/jurado/inicio
* **accept-encoding**: gzip, deflate, br, zstd
* **accept-language**: es-419,es;q=0.6
* **cookie**: csrftoken=WifRwVHlrRF4m6XpgHZLs3qdunoPINRQ; sessionid=b3m2x6s2jm2wvyt7y2orqw7viq648od7; phpMyAdmin=7bd3b9aefe79193e37e2fee89e998e0d; pma_lang=es; XSRF-TOKEN=eyJpdiI6ImdueDVVamhjNnJTRDBRTmFxYU93cHc9PSIsInZhbHVlIjoiU1M2S2hJc25yU09OR3J3aUlyaGV0RGprOHdOSGtQMHdCWFZUeXpzVlNLemw4cWdDbTVaOGNxdTNCTVdYQ250MmxhNTFnYnFDdmNwOFA4R0Z5RzFRRVY1VS9OUXVqc0NteVBiZFQyelo5dkN3cmZPYTllc1kwc2haRUFQdE1keGciLCJtYWMiOiIyMTU3Njk3ZDU2MzQ2NzliNzk5MGY4ZGYxZTBiODExYTI0ZGM0ZDZmMWExOTJiZjM5MjQ2NzMyZTljZDVlYjExIiwidGFnIjoiIn0%3D; acb-canta-bolivia-session=eyJpdiI6Im03NXdIMnpYQ20zQW9WdDdtbklKRXc9PSIsInZhbHVlIjoidnBOdmFMTVZuRkE2Z1NRbE9aK3pFSU0ybU9mbjBGaWJyc05MbjNPY0FZSUdXQVRCR0tXN0wyd29EUTV4V25hUmRXdGlPSzhSTnNZREZ1UUViT2dsTzRSdHVEVmZZc2FTYUJ0S2F4RFcwMHp0UjYveDREYTRCVWdBRURhRlBkRnAiLCJtYWMiOiI2ZDczYmQ1OWY4Mjg0MjJhMzRhMGY2NjcyNmM2ZmYzMTIxYmMxMjllMDYzNWI4ODVkYTExYzQ2ZTBhNTFiM2I5IiwidGFnIjoiIn0%3D

## Route Context

controller: App\Modules\Usuarios\Controllers\InicioController@jurado
route name: jurado.inicio
middleware: web, auth, verified, rol:JURADO

## Route Parameters

No route parameter data available.

## Database Queries

* mysql - select * from `sessions` where `id` = '4aJtFILYkYtG46zVGWP7lwtt56Sw9LlO3jfu6pav' limit 1 (5.05 ms)
* mysql - select * from `users` where `id` = 6 limit 1 (4.25 ms)
