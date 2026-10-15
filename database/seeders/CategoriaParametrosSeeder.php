<?php

namespace Database\Seeders;

use App\Modules\Categorias\Models\Categoria;
use App\Modules\Categorias\Models\CategoriaParametro;
use App\Modules\Parametros\Models\Parametro;
use Illuminate\Database\Seeder;
use RuntimeException;

class CategoriaParametrosSeeder extends Seeder
{
    /**
     * Datos oficiales de la convocatoria 2026.
     *
     * Cada categoría define: max_participantes (personas en escenario),
     * tiempo_presentacion (minutos) y sus parámetros con el máximo de puntos.
     * La suma de valor_maximo SIEMPRE debe ser 100 por categoría.
     */
    public function run(): void
    {
        $categorias = [
            [
                'categoria' => 'Ñusta',
                'max_participantes' => 1,
                'tiempo_presentacion' => 10,
                'parametros' => [
                    ['parámetro' => 'Interpretación vocal', 'valor_maximo' => 30],
                    ['parámetro' => 'Interpretación instrumental', 'valor_maximo' => 40],
                    ['parámetro' => 'Carisma, dominio de escenario y conocimientos culturales', 'valor_maximo' => 15],
                    ['parámetro' => 'Indumentaria representativa del lugar de origen', 'valor_maximo' => 15],
                ],
            ],
            [
                'categoria' => 'Solista vocal (con o sin acompañamiento)',
                'max_participantes' => 6,
                'tiempo_presentacion' => 5,
                'parametros' => [
                    ['parámetro' => 'Interpretación', 'valor_maximo' => 40],
                    ['parámetro' => 'Técnica vocal', 'valor_maximo' => 30],
                    ['parámetro' => 'Expresión y dominio de escenario', 'valor_maximo' => 20],
                    ['parámetro' => 'Indumentaria', 'valor_maximo' => 10],
                ],
            ],
            [
                'categoria' => 'Solista instrumental – Armónico',
                'max_participantes' => 1,
                'tiempo_presentacion' => 5,
                'parametros' => [
                    ['parámetro' => 'Dominio instrumental', 'valor_maximo' => 50],
                    ['parámetro' => 'Interpretación y expresión', 'valor_maximo' => 30],
                    ['parámetro' => 'Indumentaria', 'valor_maximo' => 20],
                ],
            ],
            [
                'categoria' => 'Solista instrumental – Melódico (con o sin acompañamiento)',
                'max_participantes' => 2,
                'tiempo_presentacion' => 5,
                'parametros' => [
                    ['parámetro' => 'Dominio instrumental', 'valor_maximo' => 50],
                    ['parámetro' => 'Interpretación y expresión', 'valor_maximo' => 30],
                    ['parámetro' => 'Indumentaria', 'valor_maximo' => 20],
                ],
            ],
            [
                'categoria' => 'Dúo vocal instrumental',
                'max_participantes' => 2,
                'tiempo_presentacion' => 5,
                'parametros' => [
                    ['parámetro' => 'Interpretación vocal y armonía', 'valor_maximo' => 30],
                    ['parámetro' => 'Interpretación instrumental', 'valor_maximo' => 30],
                    ['parámetro' => 'Expresión y dominio de escenario', 'valor_maximo' => 25],
                    ['parámetro' => 'Indumentaria', 'valor_maximo' => 15],
                ],
            ],
            [
                'categoria' => 'Trío vocal instrumental',
                'max_participantes' => 3,
                'tiempo_presentacion' => 5,
                'parametros' => [
                    ['parámetro' => 'Interpretación vocal y armonía', 'valor_maximo' => 30],
                    ['parámetro' => 'Interpretación instrumental', 'valor_maximo' => 30],
                    ['parámetro' => 'Expresión y dominio de escenario', 'valor_maximo' => 25],
                    ['parámetro' => 'Indumentaria', 'valor_maximo' => 15],
                ],
            ],
            [
                'categoria' => 'Grupo vocal instrumental',
                'max_participantes' => 8,
                'tiempo_presentacion' => 5,
                'parametros' => [
                    ['parámetro' => 'Interpretación vocal y armonía', 'valor_maximo' => 35],
                    ['parámetro' => 'Interpretación y arreglos instrumentales', 'valor_maximo' => 35],
                    ['parámetro' => 'Expresión y dominio de escenario', 'valor_maximo' => 15],
                    ['parámetro' => 'Indumentaria', 'valor_maximo' => 15],
                ],
            ],
            [
                'categoria' => 'Composición',
                'max_participantes' => 6,
                'tiempo_presentacion' => 5,
                'parametros' => [
                    ['parámetro' => 'Estructura musical', 'valor_maximo' => 40],
                    ['parámetro' => 'Letra', 'valor_maximo' => 40],
                    ['parámetro' => 'Originalidad', 'valor_maximo' => 20],
                ],
            ],
            [
                'categoria' => 'Estudiantina',
                'max_participantes' => 15,
                'tiempo_presentacion' => 7,
                'parametros' => [
                    ['parámetro' => 'Interpretación y ejecución instrumental', 'valor_maximo' => 40],
                    ['parámetro' => 'Arreglos musicales propios (Armonía)', 'valor_maximo' => 40],
                    ['parámetro' => 'Indumentaria', 'valor_maximo' => 20],
                ],
            ],
            [
                'categoria' => 'Autóctono',
                'max_participantes' => 20,
                'tiempo_presentacion' => 7,
                'parametros' => [
                    ['parámetro' => 'Dominio instrumental', 'valor_maximo' => 30],
                    ['parámetro' => 'Armonía instrumental', 'valor_maximo' => 30],
                    ['parámetro' => 'Dominio escénico y originalidad', 'valor_maximo' => 20],
                    ['parámetro' => 'Indumentaria', 'valor_maximo' => 20],
                ],
            ],
            [
                'categoria' => 'Solista vocal infantil (con o sin acompañamiento)',
                'max_participantes' => 6,
                'tiempo_presentacion' => 5,
                'parametros' => [
                    ['parámetro' => 'Interpretación', 'valor_maximo' => 50],
                    ['parámetro' => 'Expresión y dominio de escenario', 'valor_maximo' => 20],
                    ['parámetro' => 'Técnica vocal', 'valor_maximo' => 15],
                    ['parámetro' => 'Indumentaria', 'valor_maximo' => 15],
                ],
            ],
            [
                'categoria' => 'Solista instrumental infantil (con o sin acompañamiento)',
                'max_participantes' => 2,
                'tiempo_presentacion' => 5,
                'parametros' => [
                    ['parámetro' => 'Interpretación instrumental', 'valor_maximo' => 60],
                    ['parámetro' => 'Expresión y dominio de escenario', 'valor_maximo' => 30],
                    ['parámetro' => 'Indumentaria', 'valor_maximo' => 10],
                ],
            ],
            [
                'categoria' => 'Especial',
                'max_participantes' => 10,
                'tiempo_presentacion' => 5,
                'parametros' => [
                    ['parámetro' => 'Técnica vocal', 'valor_maximo' => 35],
                    ['parámetro' => 'Técnica instrumental', 'valor_maximo' => 35],
                    ['parámetro' => 'Dominio escénico y originalidad', 'valor_maximo' => 15],
                    ['parámetro' => 'Indumentaria', 'valor_maximo' => 15],
                ],
            ],
        ];

        foreach ($categorias as $datos) {
            $categoria = Categoria::updateOrCreate(
                ['categoria' => $datos['categoria']],
                [
                    'max_participantes' => $datos['max_participantes'],
                    'tiempo_presentacion' => $datos['tiempo_presentacion'],
                ]
            );

            $suma = 0;

            foreach ($datos['parametros'] as $orden => $param) {
                $parametro = Parametro::updateOrCreate(
                    ['parametro' => $param['parámetro']],
                    []
                );

                CategoriaParametro::updateOrCreate(
                    [
                        'id_categoria' => $categoria->id,
                        'id_parametro' => $parametro->id,
                    ],
                    [
                        'valor_minimo' => 1,
                        'valor_maximo' => $param['valor_maximo'],
                        'orden' => $orden + 1,
                    ]
                );

                $suma += $param['valor_maximo'];
            }

            if ($suma !== 100) {
                throw new RuntimeException(
                    "La categoría '{$datos['categoria']}' suma {$suma} puntos, debe sumar 100."
                );
            }
        }
    }
}