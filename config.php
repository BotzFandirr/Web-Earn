<?php
return [
    'app' => [
        'name' => 'CuanTask',
        'url' => 'http://localhost:8000',
    ],
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'database' => 'cuantask',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
    ],
    'smtp' => [
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'username' => 'youremail@gmail.com',
        'password' => 'your_app_password',
        'from_email' => 'youremail@gmail.com',
        'from_name' => 'CuanTask',
    ],
];
