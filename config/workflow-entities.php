<?php

/**
 * Workflow Entity Types Registry
 *
 * Maps workflow definition keys to human-readable labels. The Workflow
 * Definition Wizard uses this to offer a validated dropdown instead of
 * a free-text field.
 *
 * The key MUST match the value returned by the entity's
 * getWorkflowDefinitionKey() method in the Workflowable contract.
 *
 * Each consuming-app module that implements Workflowable should add
 * its entities here via mergeConfigFrom() in its service provider.
 */
return [
    'leave_request' => 'Leave Request',
    'payroll_run'   => 'Payroll Run',
];
