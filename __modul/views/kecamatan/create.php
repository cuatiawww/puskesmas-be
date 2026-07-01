<?php

$this->title = 'Tambah Kecamatan';
$this->params['active_menu'] = $activeMenu;

echo $this->render('_form', [
    'model' => $model,
    'pageTitle' => $this->title,
    'activeMenu' => $activeMenu,
    'parentOptions' => $parentOptions,
]);
