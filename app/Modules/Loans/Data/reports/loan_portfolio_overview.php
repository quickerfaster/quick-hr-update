<?php

return array (
  'module' => 'hr',
  'context' => 'loans',
  'configKey' => 'loans_loan',
  'model' => 'App\\Modules\\Loans\\Models\\Loan',
  'title' => 'Loan Portfolio Overview',
  'label' => 'Loan Portfolio Overview',
  'type' => 'dashboard',
  'description' => 'Key loan portfolio metrics and outstanding balances',
  'widgets' =>
  array (
    0 =>
    array (
      'width' => 3,
      'type' => 'stat',
      'title' => 'Active Loans',
      'model' => 'App\\Modules\\Loans\\Models\\Loan',
      'aggregate' => 'count',
      'filters' => array (
        array ('field' => 'status', 'operator' => '=', 'value' => 'active'),
      ),
    ),
    1 =>
    array (
      'width' => 3,
      'type' => 'stat',
      'title' => 'Pending Approval',
      'model' => 'App\\Modules\\Loans\\Models\\Loan',
      'aggregate' => 'count',
      'filters' => array (
        array ('field' => 'status', 'operator' => '=', 'value' => 'pending_approval'),
      ),
    ),
    2 =>
    array (
      'width' => 3,
      'type' => 'stat',
      'title' => 'Total Outstanding',
      'model' => 'App\\Modules\\Loans\\Models\\Loan',
      'aggregate' => 'sum',
      'aggregateField' => 'balance',
      'filters' => array (
        array ('field' => 'status', 'operator' => '=', 'value' => 'active'),
      ),
    ),
    3 =>
    array (
      'width' => 3,
      'type' => 'stat',
      'title' => 'Overdue Repayments',
      'model' => 'App\\Modules\\Loans\\Models\\LoanRepayment',
      'aggregate' => 'count',
      'filters' => array (
        array ('field' => 'status', 'operator' => '=', 'value' => 'overdue'),
      ),
    ),
  ),
  'layout' =>
  array (
    'columns' => 12,
    'gutter' => 3,
  ),
);
