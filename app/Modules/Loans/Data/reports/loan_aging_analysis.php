<?php

return array (
  'module' => 'hr',
  'context' => 'loans',
  'configKey' => 'loans_loan',
  'model' => 'App\\Modules\\Loans\\Models\\Loan',
  'title' => 'Loan Aging Analysis',
  'label' => 'Loan Aging Analysis',
  'type' => 'dashboard',
  'description' => 'Outstanding loans by age and repayment status',
  'widgets' =>
  array (
    0 =>
    array (
      'width' => 6,
      'type' => 'chart',
      'title' => 'Loans by Status',
      'chartType' => 'doughnut',
      'model' => 'App\\Modules\\Loans\\Models\\Loan',
      'groupBy' => 'status',
      'aggregate' => 'count',
    ),
    1 =>
    array (
      'width' => 6,
      'type' => 'chart',
      'title' => 'Outstanding by Loan Type',
      'chartType' => 'bar',
      'model' => 'App\\Modules\\Loans\\Models\\Loan',
      'aggregate' => 'sum',
      'aggregateField' => 'balance',
      'groupBy' => 'loan_type_id',
      'filters' => array (
        array ('field' => 'status', 'operator' => '=', 'value' => 'active'),
      ),
    ),
    2 =>
    array (
      'width' => 12,
      'type' => 'table',
      'title' => 'Overdue Repayments',
      'model' => 'App\\Modules\\Loans\\Models\\LoanRepayment',
      'columns' => array ('loan_id', 'amount', 'due_date', 'status'),
      'filters' => array (
        array ('field' => 'status', 'operator' => '=', 'value' => 'overdue'),
      ),
      'sort' => array ('column' => 'due_date', 'direction' => 'asc'),
    ),
  ),
  'layout' =>
  array (
    'columns' => 12,
    'gutter' => 3,
  ),
);
