<?php

/**
 * ESS (Employee Self-Service) Profile Config
 * 
 * This is a stripped-down version of employee.php designed for the
 * "My Profile" page. It exposes only employee-appropriate fields and
 * removes all administrative/sensitive data (salary, bank, tax, etc.).
 * 
 * Used by: my-profile.blade.php → EmployeeDetail component
 * Config key: hr.employee_ess
 */
return [
    'model' => 'App\Modules\Hr\Models\Employee',
    'fieldDefinitions' => [
        'employee_number' => [
            'display' => 'inline',
            'field_type' => 'string',
            'label' => 'Employee Number',
            'searchable' => true,
        ],
        'first_name' => [
            'display' => 'inline',
            'field_type' => 'string',
            'label' => 'First Name',
            'searchable' => true,
        ],
        'last_name' => [
            'display' => 'inline',
            'field_type' => 'string',
            'label' => 'Last Name',
            'searchable' => true,
        ],
        'email' => [
            'display' => 'inline',
            'field_type' => 'string',
            'label' => 'Work Email',
            'searchable' => true,
        ],
        'phone' => [
            'display' => 'inline',
            'field_type' => 'string',
            'label' => 'Phone',
            'searchable' => true,
        ],
        'hire_date' => [
            'display' => 'inline',
            'field_type' => 'datepicker',
            'label' => 'Hire Date',
        ],
    ],
    'detailComponent' => 'qf.employee-detail',
    'hiddenFields' => [
        'onTable' => [
            '0' => 'created_at',
            '1' => 'updated_at',
            '2' => 'deleted_at',
            '3' => 'company_id',
            '4' => 'user_id',
            '5' => 'tag_ids',
            '6' => 'employee_group_id',
        ],
        'onNewForm' => [],
        'onEditForm' => [],
        'onQuery' => ['0' => 'deleted_at'],
        'onDetail' => [
            '0' => 'created_at',
            '1' => 'updated_at',
            '2' => 'deleted_at',
            '3' => 'company_id',
            '4' => 'user_id',
            '5' => 'tag_ids',
            '6' => 'employee_group_id',
        ],
    ],
    'simpleActions' => [],
    'isTransaction' => false,
    'crudType' => 'pages',
    'includeControllers' => false,
    'addRoutes' => false,
    'dispatchEvents' => false,
    'controls' => [
        'editable' => false,
        'addButton' => false,
        'files' => false,
        'perPage' => [],
        'search' => false,
        'showHideColumns' => false,
        'softDelete' => false,
        'restore' => false,
        'forceDelete' => false,
        'trashView' => false,
        'bulkActions' => [],
    ],
    'fieldGroups' => [
        'identity' => [
            'title' => 'Identity',
            'groupType' => 'hr',
            'icon' => 'fas fa-id-card',
            'fields' => [
                '0' => 'employee_number',
                '1' => 'first_name',
                '2' => 'last_name',
            ],
        ],
        'employment_details' => [
            'title' => 'Employment Details',
            'groupType' => 'hr',
            'icon' => 'fas fa-briefcase',
            'fields' => [
                '0' => 'hire_date',
            ],
        ],
        'contact' => [
            'title' => 'Contact Information',
            'groupType' => 'hr',
            'icon' => 'fas fa-address-card',
            'fields' => [
                '0' => 'email',
                '1' => 'phone',
            ],
        ],
    ],
    'relations' => [
        'employeePosition' => [
            'type' => 'hasOne',
            'model' => 'App\Modules\Hr\Models\EmployeePosition',
            'foreignKey' => 'employee_id',
            'localKey' => '',
        ],
        'employeeProfile' => [
            'type' => 'hasOne',
            'model' => 'App\Modules\Hr\Models\EmployeeProfile',
            'foreignKey' => 'employee_id',
            'localKey' => '',
        ],
        'company' => [
            'type' => 'belongsTo',
            'model' => 'App\Modules\Hr\Models\Company',
            'foreignKey' => 'company_id',
            'localKey' => '',
        ],
        'employeeGroup' => [
            'type' => 'belongsTo',
            'model' => 'App\Modules\Hr\Models\EmployeeGroup',
            'foreignKey' => 'employee_group_id',
            'localKey' => '',
        ],
        'user' => [
            'type' => 'belongsTo',
            'model' => 'App\Models\User',
            'foreignKey' => 'user_id',
            'localKey' => '',
        ],
    ],
    'self_service' => [
        'enabled' => true,
        'allowed_tabs' => ['overview', 'personal', 'contact', 'employment', 'history', 'workpatterns', 'payslips', 'documents', 'attendance'],
        'hide_edit_buttons' => true,
        'hide_compensation' => true,
        'hide_bank_info' => true,
        'hide_tax_info' => true,
    ],
    'report' => [],
];