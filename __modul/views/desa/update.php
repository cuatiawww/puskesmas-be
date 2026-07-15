<?php

$this->title = 'Ubah Desa/Kelurahan';
$this->params['active_menu'] = $activeMenu;

echo $this->render('_form', [
    'model' => $model,
    'pageTitle' => $this->title,
    'activeMenu' => $activeMenu,
    'parentOptions' => $parentOptions,
]);
