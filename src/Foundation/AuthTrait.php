<?php

namespace Luclin\Foundation;

trait AuthTrait
{
    abstract public function model();

    protected $_authExtra = [];

    public function getAuthExtra($key = null) {
        return $key ? ($this->_authExtra[$key] ?? null) : $this->_authExtra;
    }

    public function setAuthExtra(array $extra): self {
        $this->_authExtra = $extra;
        return $this;
    }

    public function setAuthIdentifier($id): self {
        $this->{$this->getAuthIdentifierName()} = $id;
        return $this;
    }

    public function id() {
        return $this->getAuthIdentifier();
    }

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return 'id';
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->{$this->getAuthIdentifierName()};
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    /**
     * Get the token value for the "remember me" session.
     *
     * @return string|null
     */
    public function getRememberToken()
    {
        return isset($this->remember_token) ? (string)$this->remember_token : null;
    }

    /**
     * Set the token value for the "remember me" session.
     *
     * @param  string  $value
     * @return void
     */
    public function setRememberToken($value)
    {
        $this->remember_token = $value;
    }

    /**
     * Get the column name for the "remember me" token.
     *
     * @return string
     */
    public function getRememberTokenName()
    {
        return 'remember_token';
    }
}
