<?php

namespace Jikan\Request\User;

use Jikan\Request\RequestInterface;

/**
 * Class UserProfileRequest
 *
 * @package Jikan\Request
 */
class UserProfileRequest implements RequestInterface
{

    /**
     * @var string
     */
    private $username;

    /**
     * UserProfileRequest constructor.
     *
     * @param string $username
     */
    public function __construct($username)
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return sprintf('https://myanimelist.net/profile/%s/', $this->username);
    }
}