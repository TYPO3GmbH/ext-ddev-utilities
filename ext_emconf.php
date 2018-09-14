<?php

/**
 * Extension Manager/Repository config file for ext "ddev_utilities".
 */
$EM_CONF[$_EXTKEY] = [
    'title' => 'ddev utilities',
    'description' => 'Utility Extension for intended use with ddev-based setups',
    'category' => 'templates',
    'constraints' => [
        'depends' => [
            'typo3' => '8.7.0-9.5.99',
        ],
        'conflicts' => [
        ],
    ],
    'autoload' => [
        'psr-4' => [
            'T3G\\DdevUtilities\\' => 'Classes'
        ],
    ],
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 1,
    'author' => 'TYPO3 GmbH',
    'author_email' => 'info@typo3.com',
    'author_company' => 'TYPO3 GmbH',
    'version' => '1.0.0',
];
