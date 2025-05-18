<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
  ->in(__DIR__)
  ->exclude(['vendor', 'templates_c']);

return (new PhpCsFixer\Config())
  ->setRiskyAllowed(TRUE)
  ->setRules([
    //'@PSR12' => true,
    'blank_line_after_opening_tag' => TRUE,
    'blank_line_between_import_groups' => TRUE,
    'blank_lines_before_namespace' => TRUE,
    'braces' => [
      'position_after_functions_and_oop_constructs' => 'same',
      'position_after_control_structures' => 'same',
      'position_after_anonymous_constructs' => 'same',
    ],
    'indentation_type' => TRUE,
    'declare_strict_types' => TRUE,
    'array_syntax' => ['syntax' => 'short'],
    'ordered_imports' => ['sort_algorithm' => 'alpha'],
    'no_unused_imports' => TRUE,
    'phpdoc_add_missing_param_annotation' => TRUE,
    'phpdoc_no_useless_inheritdoc' => TRUE,
    #'phpdoc_no_empty' => false,
    'phpdoc_align' => ['align' => 'left'],

    'function_declaration' => [
      'closure_function_spacing' => 'one',
    ],

    'method_argument_space' => [
      'on_multiline' => 'ensure_fully_multiline',
    ],

    'visibility_required' => ['elements' => ['method', 'property']],
    'return_type_declaration' => ['space_before' => 'one'],
    'type_declaration_spaces' => TRUE,

    'constant_case' => [
      'case' => 'upper',
    ],
    'constant_case' => ['case' => 'upper'],
    #'variable_name' => true,
    #'property_name' => true,
  ])
  ->setFinder($finder);
