<?php
/**
 * TreeBuilder class file.
 * @copyright (c) 2014, Galament
 * @license       http://www.opensource.org/licenses/bsd-license.php
 */

namespace bariew\rbacModule\components;

use \bariew\nodeTree\ARTreeMenuWidget;
use \bariew\nodeTree\SimpleTreeBehavior;

/**
 * Generates jstree tree view from parent children nodes.
 *
 * 1. Attach behavior in yii2 common way, define $actionPath and $id attribute name.
 * 2.1 Call widget from model like self::findOne(1)->menuWidget()
 * 2.2 Call checkbox widget like self::findOne(1)->checkboxWidget('node', $selectedItemIds)
 *
 * @author Pavel Bariev <bariew@yandex.ru>
 */
class TreeBuilder extends SimpleTreeBehavior
{
    public $childrenAttribute = 'children';
    /**
     * @inheritdoc
     */
    public $types = [
        "user" => ["icon" => "glyphicon glyphicon-user"],
        "flag" => ["icon" => "glyphicon glyphicon-flag"],
    ];

    /**
     * Callback for $this->menuWidget() method.
     * @param array $data data to process.
     * @return array processed data.
     */
    public function menuCallback($data)
    {
        $contextMenu          = ARTreeMenuWidget::$commonOptions['contextmenu'];
        $contextMenu['items'] = [
            'createRole'       => [
                "label"  => "<i class='glyphicon glyphicon-user' title='Create role'></i>",
                "action" => 'function(obj){
                    var url = replaceTreeUrl($(obj.reference[0]).attr("href"), "tree-create-role");
                    window.location.href = url;
                }'
            ],
            'createPermission' => [
                "label"  => "<i class='glyphicon glyphicon-flag' title='Create permission'></i>",
                "action" => 'function(obj){
                    var url = replaceTreeUrl($(obj.reference[0]).attr("href"), "tree-create-permission");
                    window.location.href = url;
                }'
            ],
            'delete'           => $contextMenu['items']['delete']
        ];
        $data['options']      = ['types' => $this->types, 'contextmenu' => $contextMenu];

        return $data;
    }

    /**
     * Callback for $this->menuWidget() method.
     * @param array $data data to process.
     * @return array processed data.
     */
    public function checkboxCallback($data)
    {
        $this->childrenAttribute = 'roles';
        $this->selectedNodes = $data['selected'];
        unset($data['selected']);
        $data['options'] = [
            'types'    => $this->types,
            'plugins'  => ['checkbox', 'search', 'types'],
            'checkbox' => ["keep_selected_style" => false]
        ];
        $data['binds']   = [
            'changed.jstree'     => 'function(event, data){
                var url = data.node.a_attr.href.replace("access-roles/update", "assign/change")
                    + "&add=" + (data.action=="select_node" ? 1 : 0)
                    + "&user_id=" + ' . $_GET['id'] . ';
                $.get(url);
            }',
            'select_node.jstree' => 'function(){return false;} '
        ];

        return $data;
    }

    /**
     * Generates attributes for jstree item from owner model.
     * @param mixed $model model
     * @param mixed $pid view item parent id.
     * @param bool $uniqueKey unique node view id prefix.
     * @return array attributes
     */
    public function nodeAttributes($model = false, $pid = '', $uniqueKey = false)
    {
        $uniqueKey = $uniqueKey ? $uniqueKey : self::$uniqueKey++;
        $model     = ($model) ? $model : $this->owner;
        $id        = $model[$this->id];
        $nodeId    = $uniqueKey . '-id-' . $id;

        return array(
            'id'       => $nodeId,
            'model'    => $model,
            'children' => $model->{$this->childrenAttribute},
            'text'     => $model['name'],
            'type'     => $model['type'] == 1 ? 'user' : 'flag',
            'selected' => in_array($model->{$this->id}, $this->selectedNodes),
            'a_attr'   => array(
                'class'   => 'jstree-clicked',
                'data-id' => $nodeId,
                'href'    => $this->actionPath . "?{$this->id}={$id}&pid={$pid}"
            )
        );
    }
}
