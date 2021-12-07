<?php

return [

    'host' => 'localhost',
    'port' => 5672,
    'user' => 'guest',
    'password' => 'guest',
    'vhost' => 'guest',

    /*
    |--------------------------------------------------------------------------
    | Directories
    |--------------------------------------------------------------------------
    |
    | The default directory structure
    |
    */

    'consumer_directory' => app_path('Consumers/'),

    /*
    |--------------------------------------------------------------------------
    | Namespaces
    |--------------------------------------------------------------------------
    |
    | The namespace of consumer, interface
    |
    */

    'consumer_namespace' => 'App\Consumers',

    /*
    |--------------------------------------------------------------------------
    | Main Interface File
    |--------------------------------------------------------------------------
    |
    | The main interface class
    |
    */

    'main_interface_class' => \MohammadMehrabani\ConsumerGenerator\ConsumerInterface::class,

];
