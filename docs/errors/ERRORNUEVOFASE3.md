# Illuminate\Broadcasting\BroadcastException - Internal Server Error

Pusher error: cURL error 7: Failed to connect to 127.0.0.1 port 6080 after 0 ms: Could not connect to server (see https://curl.se/libcurl/c/libcurl-errors.html) for http://127.0.0.1:6080/apps/935767/events?auth_key=rlml9nwlxkswhus5t6ca&auth_timestamp=1789156790&auth_version=1.0&body_md5=49dc70c423113cf7defdc94bed5ab681&auth_signature=bbe2e0ff048f0230145da7f1d6408254799b28b5105cb7e4ae99141a5f35080f.

PHP 8.4.24
Laravel 12.69.2
localhost:8000

## Stack Trace

0 - vendor/laravel/framework/src/Illuminate/Broadcasting/Broadcasters/PusherBroadcaster.php:171
1 - vendor/laravel/framework/src/Illuminate/Broadcasting/BroadcastEvent.php:100
2 - vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php:36
3 - vendor/laravel/framework/src/Illuminate/Container/Util.php:43
4 - vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php:96
5 - vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php:35
6 - vendor/laravel/framework/src/Illuminate/Container/Container.php:799
7 - vendor/laravel/framework/src/Illuminate/Bus/Dispatcher.php:129
8 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:180
9 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:137
10 - vendor/laravel/framework/src/Illuminate/Bus/Dispatcher.php:133
11 - vendor/laravel/framework/src/Illuminate/Broadcasting/BroadcastManager.php:185
12 - vendor/laravel/framework/src/Illuminate/Broadcasting/BroadcastManager.php:189
13 - vendor/laravel/framework/src/Illuminate/Events/Dispatcher.php:391
14 - vendor/laravel/framework/src/Illuminate/Events/Dispatcher.php:313
15 - vendor/laravel/framework/src/Illuminate/Events/Dispatcher.php:299
16 - vendor/laravel/framework/src/Illuminate/Foundation/helpers.php:505
17 - app/Modules/Presentaciones/Services/GestionarPresentacionService.php:32
18 - app/Modules/Presentaciones/Controllers/PresentacionController.php:89
19 - vendor/laravel/framework/src/Illuminate/Routing/ControllerDispatcher.php:46
20 - vendor/laravel/framework/src/Illuminate/Routing/Route.php:265
21 - vendor/laravel/framework/src/Illuminate/Routing/Route.php:211
22 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:822
23 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:180
24 - app/Http/Middleware/EnsureUserHasRol.php:23
25 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
26 - vendor/laravel/framework/src/Illuminate/Auth/Middleware/EnsureEmailIsVerified.php:41
27 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
28 - vendor/laravel/framework/src/Illuminate/Routing/Middleware/SubstituteBindings.php:50
29 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
30 - vendor/laravel/framework/src/Illuminate/Auth/Middleware/Authenticate.php:63
31 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
32 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/VerifyCsrfToken.php:87
33 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
34 - vendor/laravel/framework/src/Illuminate/View/Middleware/ShareErrorsFromSession.php:48
35 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
36 - vendor/laravel/framework/src/Illuminate/Session/Middleware/StartSession.php:120
37 - vendor/laravel/framework/src/Illuminate/Session/Middleware/StartSession.php:63
38 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
39 - vendor/laravel/framework/src/Illuminate/Cookie/Middleware/AddQueuedCookiesToResponse.php:36
40 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
41 - vendor/laravel/framework/src/Illuminate/Cookie/Middleware/EncryptCookies.php:74
42 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
43 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:137
44 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:821
45 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:800
46 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:764
47 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:753
48 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php:200
49 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:180
50 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/TransformsRequest.php:21
51 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/ConvertEmptyStringsToNull.php:31
52 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
53 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/TransformsRequest.php:21
54 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/TrimStrings.php:51
55 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
56 - vendor/laravel/framework/src/Illuminate/Http/Middleware/ValidatePostSize.php:27
57 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
58 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/PreventRequestsDuringMaintenance.php:109
59 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
60 - vendor/laravel/framework/src/Illuminate/Http/Middleware/HandleCors.php:61
61 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
62 - vendor/laravel/framework/src/Illuminate/Http/Middleware/TrustProxies.php:58
63 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
64 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/InvokeDeferredCallbacks.php:22
65 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
66 - vendor/laravel/framework/src/Illuminate/Http/Middleware/ValidatePathEncoding.php:26
67 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
68 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:137
69 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php:175
70 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php:144
71 - vendor/laravel/framework/src/Illuminate/Foundation/Application.php:1220
72 - public/index.php:20
73 - vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php:23

## Request

POST /admin/presentaciones/12/transicion

## Headers

* **host**: localhost:8000
* **connection**: keep-alive
* **content-length**: 67
* **cache-control**: max-age=0
* **sec-ch-ua**: "Not=A?Brand";v="99", "Brave";v="151", "Chromium";v="151"
* **sec-ch-ua-mobile**: ?0
* **sec-ch-ua-platform**: "Linux"
* **upgrade-insecure-requests**: 1
* **content-type**: application/x-www-form-urlencoded
* **user-agent**: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36
* **origin**: http://localhost:8000
* **accept**: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8
* **sec-gpc**: 1
* **sec-fetch-site**: same-origin
* **sec-fetch-mode**: navigate
* **sec-fetch-user**: ?1
* **sec-fetch-dest**: document
* **referer**: http://localhost:8000/admin/presentaciones
* **accept-encoding**: gzip, deflate, br, zstd
* **accept-language**: es-419,es;q=0.9
* **cookie**: XSRF-TOKEN=eyJpdiI6IktnRGo3Y0liSGdYZFVFUVI4M2p3S1E9PSIsInZhbHVlIjoiNTVOQnYzaUdDT3ltUDlUbm5HV21URDZMVWVURmJwaVVHOEtqNmE3YXZldFVZRHlhSzJwRHRsVVQxaEJKYXdmeGZEQSswUXN0TEhLdlQvOTNUYW10dHJjTXZlNU5DZWpXNlU1MVBYVmdoVGZxODZIUHk2NWhYV010cm94V2o1UzkiLCJtYWMiOiJlYzYyZjRmZDFiMjQ0ZWEwY2Q1M2YwNWVhZDhhOGI5OWE2NDk3NzczNmM2MTIxMDUyMTgzMzIzYWVlOGQxNzhmIiwidGFnIjoiIn0%3D; acb-canta-bolivia-session=eyJpdiI6IkNhMmdORlhXekJFU0RBNU0wTy9lZmc9PSIsInZhbHVlIjoiNHpyRlRXcEhRUkpNUk9raTdacXNvc3RjRFNNQWRwVTVGbG80NDZabkh4OWpsd3o5UURBeC81czB1NEdMcVVqRWlWdjM4ZUpicHV0dERMWW5Jay9aRy9GWkF1NDc4N083a0UwM2luM2xNNEo1U2hXZDU2WXlyWUh2WXFNR3RsNlIiLCJtYWMiOiI3ZWYxMjRjNDU4OWZhMjBiN2E5OTgyM2NiY2Q5NmRmMjk4YWNlOGU2MDViZDI2MjI4ZDMyYTE3NDQyNjdiNDE5IiwidGFnIjoiIn0%3D

## Route Context

controller: App\Modules\Presentaciones\Controllers\PresentacionController@transicion
route name: admin.presentaciones.transicion
middleware: web, auth, verified, rol:ADMIN

## Route Parameters

{
    "presentacion": {
        "id": 12,
        "id_inscripcion": 12,
        "nombre_presentacion": "Alejandra Villarreal Vélez",
        "estado": "EN_ESCENARIO",
        "tiempo_maximo": 10,
        "hora_inicio": "19:59",
        "hora_fin": null,
        "cronometro_iniciado_at": null,
        "cronometro_finalizado_at": null,
        "nota_final": null,
        "created_at": "2026-09-11T19:07:09.000000Z",
        "updated_at": "2026-09-11T19:59:50.000000Z"
    }
}

## Database Queries

* mysql - select * from `sessions` where `id` = 'umGJg0pCZT8p7gyeY7Tr85wXBgf8CQQrzD470mJa' limit 1 (1.65 ms)
* mysql - select * from `users` where `id` = 1 limit 1 (0.53 ms)
* mysql - select * from `presentaciones` where `id` = '12' limit 1 (1.06 ms)
* mysql - update `presentaciones` set `estado` = 'EN_ESCENARIO', `hora_inicio` = '19:59', `presentaciones`.`updated_at` = '2026-09-11 19:59:50' where `id` = 12 (4.58 ms)
* mysql - select * from `inscripciones` where `inscripciones`.`id` = 12 limit 1 (0.64 ms)
* mysql - select * from `categorias` where `categorias`.`id` = 1 limit 1 (0.52 ms)
* mysql - select * from `fases` where `fases`.`id` = 4 limit 1 (0.87 ms)
