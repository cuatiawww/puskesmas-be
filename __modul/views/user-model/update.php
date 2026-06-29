<?php
// Render create.php dengan model untuk edit mode
echo $this->render('create', [
    'model' => $model,
    'levelOptions' => $levelOptions ?? [],
    'provinsiList' => $provinsiList ?? [],
    'kabupatenList' => $kabupatenList ?? [],
]);
