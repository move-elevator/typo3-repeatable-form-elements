<?php

$EM_CONF['repeatable_form_elements'] = [
    'title' => 'Repeatable form elements',
    'description' => 'Adds a new form element which allows the editor to create new container elements with any type fields in them. In the frontend, a user can create any number of new containers. This is an extension for TYPO3 CMS.',
    'category' => 'fe',
    'state' => 'stable',
    'author' => 'Konrad Michalik',
    'author_email' => 'km@move-elevator.de',
    'version' => '6.0.0-alpha',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.0-14.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
