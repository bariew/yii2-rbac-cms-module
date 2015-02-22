<?php
/**
 * ViewAccess class file.
 * @copyright (c) 2014, Bariew
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace bariew\rbacModule\components;

use \Yii;
use yii\base\Object;
use yii\base\ViewEvent;
use bariew\rbacModule\helpers\UrlHelper;
use bariew\rbacModule\models\AuthItem;
use yii\console\Application;

/**
 * Manages View access: removes restricted elements.
 *
 * Usage: attach this class via events (see https://packagist.org/packages/bariew/yii2-event-component)
 *   'yii\web\View' => [
 *       'afterRender' => [
 *           ['bariew\rbacModule\components\ViewAccess', 'afterRender'],
 *       ],
 *   ],
 *
 * @author Pavel Bariev <bariew@yandex.ru>
 */
class ViewAccess extends Object
{
    /**
     * Checks whether links are available and removes/disables them.
     * @param ViewEvent $innerEvent view event.
     */
    public static function denyLinks($innerEvent)
    {
        $doc = \phpQuery::newDocumentHTML($innerEvent->output);
        foreach ($doc->find('a') as $el) {
            $link = pq($el);
            if (self::checkHrefAccess($link->attr('href'))) {
                continue;
            }
            $link->remove();
        }

        foreach ($doc->find('ul.dropdown-menu') as $el) {
            $ul = pq($el);
            if (!$ul->find('a[href!="#"]')->length) { 
                $ul->parent('li.dropdown')->addClass('hide');
            }
        }
        $innerEvent->output = $doc;
    }

    /**
     * Checks link access with rbac AuthItem.
     * @param string $href url.
     * @return boolean whether link is accessable.
     */
    public static function checkHrefAccess($href)
    {
        if (!$rule = UrlHelper::rule($href)) {
            return true;
        }
        return AuthItem::checkAccess($rule);
    }
}
