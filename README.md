Yii2 rbac module.
===================

Description
-----------

Manages access to site controller actions.
Stores all permissions in database.


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist bariew/yii2-rbac-cms-module "*"
```

or add

```
"bariew/yii2-rbac-cms-module": "*"
```

to the require section of your `composer.json` file.


Usage
-----

1. Define authManager component in main config components section, you'll need some i18n settings too (example):
```
    'components' => [
    ...
        'authManager'   => [
            'class' => '\yii\rbac\DbManager'
        ],
        'i18n' => [
            'translations' => [
                '*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                ],
            ],
        ],
    ],
```

2. Go to rbac/auth-item/index URL and create some roles and permissions, using menu tree with right mouse button.

3. Use AuthItem::checkAccess() for beforeAction events and ViewAccess::afterRender for afterRender event.
You may also use Yii::$app->authManager in common way.

* In the module migration folder you may see some migration files - they are required to run for module work ()

