<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in([
        __DIR__.'/app',
        __DIR__.'/config',
        __DIR__.'/database',
        __DIR__.'/routes',
        __DIR__.'/tests',
    ])
    ->name('*.php')
    ->notName('*.blade.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return (new Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12' => true,
        '@PSR12:risky' => true,
        
        // Additional rules for better code quality
        'array_syntax' => ['syntax' => 'short'],
        'concat_space' => ['spacing' => 'one'],
        'declare_strict_types' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'no_unused_imports' => true,
        'single_quote' => true,
        'trailing_comma_in_multiline' => [
            'elements' => ['arrays', 'arguments', 'parameters'],
        ],
        'blank_line_before_statement' => [
            'statements' => ['return', 'throw', 'try'],
        ],
        'phpdoc_order' => true,
        'phpdoc_separation' => true,
        'phpdoc_align' => ['align' => 'left'],
        'method_argument_space' => [
            'on_multiline' => 'ensure_fully_multiline',
        ],
        'binary_operator_spaces' => [
            'default' => 'single_space',
        ],
        'class_attributes_separation' => [
            'elements' => [
                'method' => 'one',
                'property' => 'one',
            ],
        ],
        'no_superfluous_phpdoc_tags' => [
            'allow_mixed' => true,
            'allow_unused_params' => true,
        ],
    ])
    ->setFinder($finder);