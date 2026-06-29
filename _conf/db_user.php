<?php

$db2Addr = $_ENV['DB2_ADDR'] ?? getenv('DB2_ADDR') ?: ($_ENV['DB_ADDR'] ?? getenv('DB_ADDR') ?: 'localhost');
$db2Port = $_ENV['DB2_PORT'] ?? getenv('DB2_PORT') ?: ($_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: '5432');
$db2Name = $_ENV['DB2_NAME'] ?? getenv('DB2_NAME') ?: ($_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: '');
$db2User = $_ENV['DB2_USER'] ?? getenv('DB2_USER') ?: ($_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'postgres');
$db2Pass = $_ENV['DB2_PASS'] ?? getenv('DB2_PASS') ?: ($_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: '');

return [
    'class' => 'yii\db\Connection',
    
    'dsn' => 'pgsql:host=' . $db2Addr . ';port=' . $db2Port . ';dbname=' . $db2Name,
    'username' => $db2User,   
    'password' => $db2Pass,
    'charset' => 'utf8',
    'schemaMap' => [
        'pgsql' => [
            'class' => 'yii\db\pgsql\Schema',
            'defaultSchema' => 'public' 
        ]
    ], // 
    'on afterOpen' => function ($event) {
        /** @var \yii\db\Connection $db */
        $db = $event->sender;
        $db->createCommand("SET TIME ZONE 'Asia/Jakarta'")->execute();
    },
];
