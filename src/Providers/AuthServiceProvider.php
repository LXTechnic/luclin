<?php

namespace Luclin\Providers;

use Illuminate\Support\Str;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // $this->app->auth->extend('apiguard', function ($app) {
        //     return $app->make(ApiGuard::class);
        // });
    }

    public function register() {
        // $this->app->bind('tspack:token', function () {
        //     $token = new \Luclin\Support\JwtToken(
        //         config('tspack.env.jwtSecret'), function(): string {
        //             return Str::random(16);
        //         }, config('tspack.env.jwtTTL'));
        //     return $token->setUserLoader(function(array $payload): ?Models\User {
        //         // 暂时不做token数据库校验
        //         // $token = Models\Token::findByToken($this->payload['jti'], $this->payload['sub']);
        //         return Models\User::f($payload['sub']);
        //     });
        // });
    }
}
