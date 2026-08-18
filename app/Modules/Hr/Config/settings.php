<?php

return [
    'contexts' => [
        'people' => [
            'groups' => [
                'auto_generation' => [
                    'label' => 'Auto-Generation',
                    'icon' => 'fas fa-magic',
                    'settings' => [
                        [
                            'key' => 'employee_number_pattern',
                            'type' => 'text',
                            'label' => 'Employee Number Pattern',
                            'default' => 'EMP-{year}-{sequence:5}',
                            'help' => 'Use the insert buttons below or type placeholders manually.',
                            'pattern_helpers' => true,
                            'pattern_preview' => true,
                            'pattern_model' => 'employee',
                            'pattern_field' => 'employee_number',
                        ],
                    ],
                ],
            ],
        ],
    ],
];
