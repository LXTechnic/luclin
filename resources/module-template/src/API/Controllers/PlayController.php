<?php

namespace :MyModule\API\Controllers;

use Luclin\Contracts\Auth;
use Luclin\Foundation\Controller;

use :MyModule\API\{
    Requests\Request,
    Types
};
use :MyModule\Models;
use :MyModule\Domains;


class PlayController extends Controller
{
    public function hello(Request $request) {
        $response = $this->response();
        $response->hello = 'world';
        return $response->send();
    }

    public function user(Request $request, Auth $auth) {
        $response = $this->response();
        $response->userId = $auth->id();
        return $response->send();
    }
}
