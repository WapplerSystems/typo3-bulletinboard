<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Bulletinboard',
    'description' => 'Bulletinboard Extension',
    'category' => 'plugin',
    'author' => 'Sven Wappler',
    'author_email' => '',
    'author_company' => 'WapplerSystems',
    'state' => 'stable',
    'version' => '11.0.1',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-12.4.99',
            'form' => '12.4.0-12.4.99',
            'extbase' => '12.4.0-12.4.99',
            'frontend' => '12.4.0-12.4.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
];
