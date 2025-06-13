<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'modules' => [

        'gridview' => [
            'class' => '\kartik\grid\Module',
        ],
      
          'dynagrid' => [
        'class' => '\kartik\dynagrid\Module',
    ],
    'admin' => [
        'class' => 'mdm\admin\Module',
        'layout' => 'left-menu', 
    ]
    ],
    'components' => [

        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'dlcdIjlWxvy1qBxjvD0PFAf6Ynp1TOxB',
            'enableCsrfValidation' => false,

        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\Usuario',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            // send all mails to a file by default.
            'useFileTransport' => true,
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
    
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
           
        ],

    ],
    
    'params' => $params,
                    'as access' => [
                'class' => 'mdm\admin\components\AccessControl',
                'allowActions' => [
                        'site/login',
                        'site/logout',
                    'admin/*',
                    'gii/*',
                    'site/*',
                    'gpslocations/*',
                    'notificaciones/*',
                    'usuario/*',
                    'gpsreport/*'
    
            
                ]
            ],    

];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
