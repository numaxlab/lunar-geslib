<?php

return [

    'label' => 'Autor/a',

    'plural_label' => 'Autores/as',

    'table' => [
        'name' => [
            'label' => 'Nombre',
        ],
        'products_count' => [
            'label' => 'N.º de productos',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Nombre',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Esta autora no puede ser eliminada porque tiene productos asociados.',
            ],
        ],
    ],
    'pages' => [
        'edit' => [
            'title' => 'Información básica',
        ],
        'products' => [
            'label' => 'Productos',
            'actions' => [
                'attach' => [
                    'label' => 'Asociar un producto',
                    'form' => [
                        'record_id' => [
                            'label' => 'Producto',
                        ],
                        'author_type' => [
                            'label' => 'Tipo de autoría',
                            'options' => [
                                'author' => 'Autora',
                                'translator' => 'Traductora',
                                'illustrator' => 'Ilustradora',
                                'cover_illustrator' => 'Ilustradora de portada',
                                'back_cover_illustrator' => 'Ilustradora de contraportada',
                            ],
                        ],
                    ],
                    'notification' => [
                        'success' => 'Producto asociado a la autora',
                    ],
                ],
                'detach' => [
                    'notification' => [
                        'success' => 'Producto desasociado.',
                    ],
                ],
            ],
        ],
    ],
];
