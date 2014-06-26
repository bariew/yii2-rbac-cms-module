<?php
/**
 * ViewAccess class file.
 * @copyright (c) 2014, Galament
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace bariew\rbacModule\components;

use \Yii;
use yii\base\Object;
use yii\base\ViewEvent;
use bariew\rbacModule\helpers\UrlHelper;
use bariew\rbacModule\models\AuthItem;

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
     * Runs access methods for view event.
     * @param ViewEvent $event view event.
     */
    public static function afterRender(ViewEvent $event)
    {
        self::denyLinks($event);
    }

    /**
     * Checks whether links are available and removes/disables them.
     * @param ViewEvent $event view event.
     */
    public static function denyLinks(ViewEvent $event)
    {
        $doc = \phpQuery::newDocumentHTML($event->output);
        foreach ($doc->find('a') as $el) {
            $link = pq($el);
            if (self::checkHrefAccess($link->attr('href'))) {
                continue;
            }
            self::disableLink($link);
        }

        foreach ($doc->find('li.dropdown') as $el) {
            $li = pq($el);
            if (!$li->find('a[href]')->length) {
                $li->remove();
            }
        }
        $event->output = $doc;
    }

    /**
     * Disables or removes link from phpQuery document.
     * @param object $link phpQuery object
     * @return boolean whether link is disabled.
     */
    public static function disableLink($link)
    {
        $href = $link->attr('href');
        if (!$rule = UrlHelper::rule($href)) {
            return false;
        }
        if (in_array($rule['action'], ['index', 'update', 'delete', 'create'])) {
            $link->remove();
        } else {
            $link->addClass('disabled');
            $link->attr('href', '#');
        }
        return true;
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
