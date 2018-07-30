<?php

Route::namespace(':MyModule\API\Controllers')
    ->prefix(':mymodule-api-prefix')
    ->group(\luc\mod(':mymodule')->path('routes', 'api.php'));
