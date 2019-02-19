<?php
//Contains all global constants

return [
    //'API_URL' => 'https://www.ecube-solutions.com/gma500laravel/public',
    //'SITE_URL' => 'https://www.ecube-solutions.com',
    'API_URL' => env('APP_API_URL'),  //In prod: https://www.ecube-solutions.com/gma500laravel/public
    'SITE_URL' => env('APP_SITE_URL'), 
    'EMAIL_FROM_ADDRESS' => 'webmaster@cassemoto06.fr',
    'EMAIL_FROM_NAME' => 'Casse moto 06',
    'EMAIL_NOREPLY' => 'no-reply@cassemoto06.fr',
    'TOKEN_LIFE_SHORT' => 1,//120,          //Time in minutes of token life when no keepconnected
    'TOKEN_LIFE_LONG'  => 43200,          //Time in minutes of token life when keepconnected
    'ACCESS_DEFAULT' => 'Membre',
    'ACCESS_ADMIN' => 'Admin',
    'ACCESS_AVAILABLE' => ['Membre', 'Admin'],
    'LANGUAGES' => ['en', 'fr'],         //Supported languages
    'THUMBS' => ['full' => 900, 'large'=> 700, 'big' => 500, 'medium' => 350 , 'small' => 200, 'thumbnail'=>100, 'tinythumbnail' => 50 ]
];