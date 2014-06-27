<?php
echo \yii\helpers\Html::activeCheckboxList($role, 'users', $users,
    ['onchange'=>'$.get("/rbac/auth-assignment/change", {
        "id" : "'.$role->name.'",
        "user_id" : event.target.value,
        "add" : $(event.target).is(":checked") ? 1 : 0
    })']);
