<?php

namespace Fabian\Linkedin;

class Configuration extends \Nette\Object
{
    /**
     * @var string
     */
    public $appId;
    
    /**
     * @var string
     */
    public $appSecret;
    
    /**
     * @var array
     */
    public $permissions;
    
    /**
     * @var array
     */
    public $url = array(
        'authorization' => 'https://www.linkedin.com/uas/oauth2/authorization',
        'accessToken' => 'https://www.linkedin.com/uas/oauth2/accessToken/',
        'api' => 'https://api.linkedin.com/v1/'
    );

    public function __construct($appId, $appSecret, array $permissions = array())
    {
        $this->appId = $appId;
        $this->appSecret = $appSecret;
        $this->permissions = $permissions;
    }
}