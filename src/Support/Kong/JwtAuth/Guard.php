<?php

namespace Luclin\Support\Kong\JwtAuth;

use Luclin\Foundation\Auth\Guest;

use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard as GuardContract;
use Illuminate\Auth\{
    AuthenticationException
};

class Guard implements GuardContract
{
    private $auth = null;

    private $authMaker;

    private $request;

    private $consumer = null;

    private $defaultId;
    private $defaultName;
    private $allowAnonymous;

    public function __construct(callable $authMaker, Request $request,
        $defaultId = null, string $defaultName = null, bool $allowAnonymous = false)
    {
        $this->authMaker    = $authMaker;
        $this->request      = $request;

        $this->defaultId        = $defaultId;
        $this->defaultName      = $defaultName;
        $this->allowAnonymous   = $allowAnonymous;
    }

    public function consumer(): array {
        if (!$this->consumer) {
            $anonymous = $this->request->header('x-anonymous-consumer');
            $anonymous = ($anonymous && $anonymous == 'true') ? true : false;

            $this->consumer = [
                'anonymous' => $anonymous,
                'name'      => $this->request
                    ->header('x-consumer-username', $this->defaultName),
                'id'        => $this->request
                    ->header('x-consumer-custom-id', $this->defaultId),
            ];
        }
        return $this->consumer;
    }

    public function authenticate()
    {
        if (($user = $this->user())
            && !($user instanceof Guest))
        {
            return $user;
        }

        throw new AuthenticationException;
    }

    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function check(): bool {
        return $this->consumer()['id']
            && (!$this->consumer()['anonymous'] || $this->allowAnonymous);
    }

    /**
     * Determine if the current user is a guest.
     *
     * @return bool
     */
    public function guest(): bool {
        return !$this->check();
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user() {
        if ($this->auth) {
            return $this->auth;
        }

        $authMaker  = $this->authMaker;
        $this->auth = $authMaker($this->consumer())
            ?: (new Guest())->fill($this->consumer());
        return $this->auth;
    }

    /**
     * Get the ID for the currently authenticated user.
     *
     * @return int|null
     */
    public function id() {
        return $this->user() ? $this->user()->getAuthIdentifier() : null;
    }

    /**
     * Validate a user's credentials.
     *
     * @param  array  $credentials
     * @return bool
     */
    public function validate(array $credentials = []) {
        return $this->check();
    }

    /**
     * Set the current user.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $auth
     * @return self
     */
    public function setUser(Authenticatable $auth): self {
        $this->auth = $auth;
        return $this;
    }

}