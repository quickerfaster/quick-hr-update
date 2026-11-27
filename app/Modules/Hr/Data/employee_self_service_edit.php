<?php

/**
 * Employee Self-Service Edit — Drawer Form
 *
 * Stripped-down config for the "Update My Info" action card drawer.
 * Shows only the fields an employee should be able to edit themselves:
 * name, email, phone, and hire date (read-only).
 *
 * Config key: hr.employee_self_service_edit
 */
return [
    'model' => 'App\Modules\Hr\Models\Employee',
    'fieldDefinitions' => [
        'first_name' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'string',
            'label' => 'First Name',
            'validation' => 'required|string|max:255',
            'editable' => true,
        ],
        'last_name' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'string',
            'label' => 'Last Name',
            'validation' => 'required|string|max:255',
            'editable' => true,
        ],
        'email' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'string',
            'label' => 'Work Email',
            'validation' => 'nullable|email|max:255',
            'editable' => true,
        ],
        'phone' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'string',
            'label' => 'Phone',
            'validation' => 'nullable|string|max:255',
            'editable' => true,
        ],
        'hire_date' => [
            'display' => 'inline',
            'fillable' => false,
            'field_type' => 'datepicker',
            'label' => 'Hire Date',
            'validation' => 'nullable|date',
            'editable' => false,
            'help_text' => 'Set by HR — contact your administrator to update',
        ],
    ],
    'hiddenFields' => [
        'onTable' => [],
        'onNewForm' => [],
        'onEditForm' => [],
        'onQuery' => [],
    ],
    'simpleActions' => [],
    'isTransaction' => false,
    'crudType' => 'drawers',
    'includeControllers' => false,
    'addRoutes' => false,
    'dispatchEvents' => true,
    'controls' => [
        'addButton' => false,
        'files' => false,
        'search' => false,
        'showHideColumns' => false,
        'editable' => true,
        'softDelete' => false,
        'restore' => false,
        'forceDelete' => false,
        'trashView' => false,
        'bulkActions' => [],
    ],
    'fieldGroups' => [
        'personal_details' => [
            'title' => 'Personal Details',
            'groupType' => 'hr',
            'icon' => 'fas fa-user-edit',
            'fields' => [
                '0' => 'first_name',
                '1' => 'last_name',
                '2' => 'email',
                '3' => 'phone',
                '4' => 'hire_date',
            ],
        ],
    ],
];
