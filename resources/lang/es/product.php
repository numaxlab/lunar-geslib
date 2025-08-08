<?php

return [
    'pages' => [
        'authors' => [
            'label' => 'Autores/as',
            'actions' => [
                'attach' => [
                    'label' => 'Asociar un autor/a',
                    'form' => [
                        'record_id' => [
                            'label' => 'Autor/a',
                        ],
                        'position' => [
                            'label' => 'Posición',
                        ],
                    ],
                    'notification' => [
                        'success' => 'Autora asociada al producto',
                    ],
                ],
                'detach' => [
                    'notification' => [
                        'success' => 'Autora desasociada.',
                    ],
                ],
            ],
        ],
    ],
];
