<?php

$dbName = $_ENV['DB_NAME'] ?? 'db_puskesmas';
if ($dbName === 'puskesmas-be') {
    $dbName = 'db_puskesmas';
}

return [
    'class' => 'yii\db\Connection',
    
    'dsn' => 'pgsql:host='.$_ENV['DB_ADDR'].';port='.$_ENV['DB_PORT'].';dbname='.$dbName.'', 
    'username' => $_ENV['DB_USER'],   
    'password' => $_ENV['DB_PASS'],
    'charset' => 'utf8',
    'schemaMap' => [
        'pgsql' => [
            'class' => 'yii\db\pgsql\Schema',
            'defaultSchema' => 'public' //specify your schema here, public is the default schema
        ]
    ], //
    'on afterOpen' => function ($event) {
        /** @var \yii\db\Connection $db */
        $db = $event->sender;
        $db->createCommand("SET TIME ZONE 'Asia/Jakarta'")->execute();
    }, 
];
