<?php

$this->title = 'Ubah Provinsi';
$this->params['active_menu'] = $activeMenu;

echo $this->render('_form', [
    'model' => $model,
    'pageTitle' => $this->title,
    'activeMenu' => $activeMenu,
]);
