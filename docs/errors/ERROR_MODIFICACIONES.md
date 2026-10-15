# Illuminate\Database\Eloquent\RelationNotFoundException - Internal Server Error

Call to undefined relationship [categoriaParametro] on model [App\Modules\Calificaciones\Models\DetalleCalificacion].

PHP 8.4.24
Laravel 12.69.2
127.0.0.1:8000

## Stack Trace

0 - vendor/laravel/framework/src/Illuminate/Database/Eloquent/RelationNotFoundException.php:35
1 - vendor/laravel/framework/src/Illuminate/Database/Eloquent/Builder.php:969
2 - vendor/laravel/framework/src/Illuminate/Database/Eloquent/Relations/Relation.php:119
3 - vendor/laravel/framework/src/Illuminate/Database/Eloquent/Builder.php:965
4 - vendor/laravel/framework/src/Illuminate/Database/Eloquent/Builder.php:939
5 - vendor/laravel/framework/src/Illuminate/Database/Eloquent/Builder.php:919
6 - vendor/laravel/framework/src/Illuminate/Database/Eloquent/Builder.php:885
7 - vendor/laravel/framework/src/Illuminate/Database/Eloquent/Relations/Relation.php:212
8 - vendor/laravel/framework/src/Illuminate/Database/Eloquent/Relations/Relation.php:175
9 - vendor/laravel/framework/src/Illuminate/Database/Eloquent/Builder.php:950
10 - vendor/laravel/framework/src/Illuminate/Database/Eloquent/Builder.php:919
11 - vendor/laravel/framework/src/Illuminate/Database/Eloquent/Builder.php:885
12 - vendor/laravel/framework/src/Illuminate/Database/Concerns/BuildsQueries.php:366
13 - vendor/laravel/framework/src/Illuminate/Support/Traits/ForwardsCalls.php:23
14 - vendor/laravel/framework/src/Illuminate/Support/Traits/ForwardsCalls.php:52
15 - vendor/laravel/framework/src/Illuminate/Database/Eloquent/Relations/Relation.php:538
16 - app/Modules/Presentaciones/Controllers/PresentacionController.php:93
17 - vendor/laravel/framework/src/Illuminate/Routing/ControllerDispatcher.php:46
18 - vendor/laravel/framework/src/Illuminate/Routing/Route.php:265
19 - vendor/laravel/framework/src/Illuminate/Routing/Route.php:211
20 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:822
21 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:180
22 - app/Http/Middleware/EnsureUserHasRol.php:23
23 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
24 - vendor/laravel/framework/src/Illuminate/Auth/Middleware/EnsureEmailIsVerified.php:41
25 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
26 - vendor/laravel/framework/src/Illuminate/Routing/Middleware/SubstituteBindings.php:50
27 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
28 - vendor/laravel/framework/src/Illuminate/Auth/Middleware/Authenticate.php:63
29 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
30 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/VerifyCsrfToken.php:87
31 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
32 - vendor/laravel/framework/src/Illuminate/View/Middleware/ShareErrorsFromSession.php:48
33 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
34 - vendor/laravel/framework/src/Illuminate/Session/Middleware/StartSession.php:120
35 - vendor/laravel/framework/src/Illuminate/Session/Middleware/StartSession.php:63
36 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
37 - vendor/laravel/framework/src/Illuminate/Cookie/Middleware/AddQueuedCookiesToResponse.php:36
38 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
39 - vendor/laravel/framework/src/Illuminate/Cookie/Middleware/EncryptCookies.php:74
40 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
41 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:137
42 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:821
43 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:800
44 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:764
45 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:753
46 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php:200
47 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:180
48 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/TransformsRequest.php:21
49 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/ConvertEmptyStringsToNull.php:31
50 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
51 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/TransformsRequest.php:21
52 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/TrimStrings.php:51
53 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
54 - vendor/laravel/framework/src/Illuminate/Http/Middleware/ValidatePostSize.php:27
55 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
56 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/PreventRequestsDuringMaintenance.php:109
57 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
58 - vendor/laravel/framework/src/Illuminate/Http/Middleware/HandleCors.php:61
59 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
60 - vendor/laravel/framework/src/Illuminate/Http/Middleware/TrustProxies.php:58
61 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
62 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/InvokeDeferredCallbacks.php:22
63 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
64 - vendor/laravel/framework/src/Illuminate/Http/Middleware/ValidatePathEncoding.php:26
65 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
66 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:137
67 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php:175
68 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php:144
69 - vendor/laravel/framework/src/Illuminate/Foundation/Application.php:1220
70 - public/index.php:20
71 - vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php:23

## Request

GET /jurado/presentaciones/actual

## Headers

* **host**: 127.0.0.1:8000
* **connection**: keep-alive
* **cache-control**: max-age=0
* **upgrade-insecure-requests**: 1
* **user-agent**: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36
* **accept**: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8
* **sec-gpc**: 1
* **sec-fetch-site**: same-origin
* **sec-fetch-mode**: navigate
* **sec-fetch-user**: ?1
* **sec-fetch-dest**: document
* **sec-ch-ua**: "Not=A?Brand";v="99", "Brave";v="151", "Chromium";v="151"
* **sec-ch-ua-mobile**: ?0
* **sec-ch-ua-platform**: "Linux"
* **referer**: http://127.0.0.1:8000/jurado/presentaciones/actual
* **accept-encoding**: gzip, deflate, br, zstd
* **accept-language**: es-419,es;q=0.9
* **cookie**: csrftoken=oQT3ejhhlMFP0jRl9T6AfyaqwrY5XszV; XSRF-TOKEN=eyJpdiI6ImVndWlZZE9KdTg5eHV4VFoyai9VdVE9PSIsInZhbHVlIjoicEpyNHJoOHE0d0tHZHZNK0dodVJ5c0Vpc1g3dVFLMEF5YjVDOTRUaTBEY2VJU2doMnBSOVhvQllPZHB4ckI5QzZyS292R2h0SnppeXk3QXh4TkpYYy8yM0VkNUxrUDlyREY1Wnp6amIxYUo0S2cyRGFqSjZ4T054NTM4YWl5UGMiLCJtYWMiOiJhZTFjYmEyM2U1Mjg0YTgyY2E0NzAzZDljYWY3NjdkNGIxN2ZlYjUwMTU5YWI1MzhlMDEyYmNiN2VlMDUzYjBiIiwidGFnIjoiIn0%3D; acb-canta-bolivia-session=eyJpdiI6Ik0wSEg2MWw3aTlKbVdXMHZYeUJEUUE9PSIsInZhbHVlIjoiR093cDBkbVkrdm1CelY3TDIza2R6bGZZNGtoVEpJYzloNXJTYXpxWDFPRDdsdE5JdllDNkw5RkVnbTNhckJPSXA3WWZwL2NvT0x0eGN1SzRUemIxWXRndm05cWhjRlpjd0ZBSVl3b3YrMytNUjlNalExNlVXQ1pyNlAwbEg5S3QiLCJtYWMiOiJlNGNmNTgxZWYzNDg1Mzg0MTg4NGM1ZDZkN2EyOGI2ZTE1OTU4YTc5ODhiZjZmNDczZWEwM2E5OWJlN2JhNjVmIiwidGFnIjoiIn0%3D

## Route Context

controller: App\Modules\Presentaciones\Controllers\PresentacionController@escenario
route name: jurado.presentaciones.actual
middleware: web, auth, verified, rol:JURADO

## Route Parameters

No route parameter data available.

## Database Queries

* mysql - select * from `sessions` where `id` = 'N3PUlmpjy7fOMIU9siK6G5LuqTaELGveR1y0bAuR' limit 1 (4.2 ms)
* mysql - delete from `sessions` where `last_activity` <= 1789347686 (0.7 ms)
* mysql - select * from `users` where `id` = 6 limit 1 (1.09 ms)
* mysql - select * from `presentaciones` where `estado` in ('EN_ESCENARIO', 'PARTICIPANDO') order by `updated_at` desc limit 1 (3.06 ms)
* mysql - select * from `presentaciones` where `estado` = 'FINALIZADO' order by `updated_at` desc limit 1 (3.22 ms)
* mysql - select * from `inscripciones` where `inscripciones`.`id` in (12) (2.48 ms)
* mysql - select * from `categorias` where `categorias`.`id` in (1) (2.52 ms)
* mysql - select `participantes`.*, `inscripcion_participante`.`id_inscripcion` as `pivot_id_inscripcion`, `inscripcion_participante`.`id_participante` as `pivot_id_participante` from `participantes` inner join `inscripcion_participante` on `participantes`.`id` = `inscripcion_participante`.`id_participante` where `inscripcion_participante`.`id_inscripcion` in (12) (5.9 ms)
* mysql - select * from `jurado_presentacion` where `jurado_presentacion`.`id_presentacion` in (12) (1.18 ms)
* mysql - select * from `users` where `users`.`id` in (5, 6, 7) (1 ms)
* mysql - select `presentaciones`.* from `presentaciones` inner join `inscripciones` on `presentaciones`.`id_inscripcion` = `inscripciones`.`id` where `presentaciones`.`estado` = 'POR_PARTICIPAR' order by `inscripciones`.`fecha_presentacion` asc, `inscripciones`.`hora_presentacion` asc, `presentaciones`.`id` asc limit 1 (1.81 ms)
* mysql - select * from `inscripciones` where `inscripciones`.`id` in (13) (1 ms)
* mysql - select * from `categorias` where `categorias`.`id` in (1) (3.23 ms)
* mysql - select `participantes`.*, `inscripcion_participante`.`id_inscripcion` as `pivot_id_inscripcion`, `inscripcion_participante`.`id_participante` as `pivot_id_participante` from `participantes` inner join `inscripcion_participante` on `participantes`.`id` = `inscripcion_participante`.`id_participante` where `inscripcion_participante`.`id_inscripcion` in (13) (2.04 ms)
* mysql - select * from `categoria_parametro` where `categoria_parametro`.`id_categoria` = 1 and `categoria_parametro`.`id_categoria` is not null order by `orden` asc (2.57 ms)
* mysql - select * from `parametros` where `parametros`.`id` in (1, 2, 3, 4) (1.29 ms)
* mysql - select * from `jurado_presentacion` where `jurado_presentacion`.`id_presentacion` = 12 and `jurado_presentacion`.`id_presentacion` is not null and `id_usuario` = 6 limit 1 (0.88 ms)
* mysql - select * from `calificaciones` where `calificaciones`.`id_jurado_presentacion` = 2 and `calificaciones`.`id_jurado_presentacion` is not null limit 1 (3.68 ms)
* mysql - select * from `detalle_calificaciones` where `detalle_calificaciones`.`id_calificacion` in (1) (2.06 ms)
