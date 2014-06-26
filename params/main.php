<?php  
return [
    'events'    =>     [
        // Checks action access for backend controllers.
        'yii\web\Controller' => [
            'afterAction' => [
                ['bariew\rbacModule\models\AuthItem', 'checkActionAccess'],
            ],
        ],
        // Runs access rules for views (removes denied links).
        'yii\web\View' => [
            'afterRender' => [
                ['bariew\rbacModule\components\ViewAccess', 'afterRender'],
            ],
        ],
    ],
    'menu'  => [
        'label'    => 'Access',
        'url' => ['/rbac/auth-item/index'],
    ]
];