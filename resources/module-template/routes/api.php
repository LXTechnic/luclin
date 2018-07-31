<?php

if (\luc\debug()) {
    Route::get('/hello', 'PlayController@hello');
    Route::get('/user', 'PlayController@user');
}
