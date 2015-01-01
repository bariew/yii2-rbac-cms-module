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
        'i18n' => [ // this example only if you don't have i18n defined in any other way.
            'translations' => [
                '*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                ],
            ],
        ],
    ],
```

2. Include 'rbac' module in modules config section:
```
    'modules' => [
    ...
        'rbac'   => [
            'class' => 'bariew\rbacModule\Module'
        ],
    ],
```

3. Apply migrations from module migrations folder. E.g. you may copy those migrations to your application migrations folder and run
    common yii console migration command.

4. Go to rbac/auth-item/index URL and create some roles and permissions, using menu tree with right mouse button.

5. Use AuthItem::checkAccess() for beforeAction events and ViewAccess::afterRender() for afterRender event.
You may also use Yii::$app->authManager in common way.
