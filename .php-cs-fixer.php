<?php

$finder = PhpCsFixer\Finder::create()
    ->path('src')
    ->path('tests')
    ->notPath('config/_compiled')
    ->in(__DIR__)
;

$config = new PhpCsFixer\Config();
return $config->setRules([
        '@Symfony' => true,
        'concat_space' => ['spacing' => 'one'],
    ])
    ->setFinder($finder);
