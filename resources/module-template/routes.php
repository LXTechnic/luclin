<?php

Route::namespace(':MyModule\API\Controllers')
    ->prefix('api/:mymodule/v1')
    ->group(\luc\mod(':mymodule')->path('routes', 'api.php'));
