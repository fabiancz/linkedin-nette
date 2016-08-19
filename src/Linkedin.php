<?php

namespace Fabian\Linkedin;

class Linkedin extends \Nette\Object
{
    /**
     * @var string
     */
    public $accessToken;
    
    /**
     * @var \Nette\Http\Session
     */
    private $session;

    /**
     * @var \Fabian\Linkedin\Configuration
     */
    private $config;
    
    public function __construct(\Nette\Http\Session $session,
        \Fabian\Linkedin\Configuration $config)
    {
        $this->session = $session->getSection('linkedin');
        if (isset($this->session->access_token)) {
            $this->accessToken = $this->session->access_token;
        }
        
        $this->config = $config;
    }
    
    /**
     * Get LinkedIn CSRF token
     * @return string
     */
    public function getState()
    {
        if (isset($this->session->csrfToken)) {
            return $this->session->csrfToken;
        }
        return FALSE;
    }

    public function getRedirectUrl($backLink)
    {
        $this->session->csrfToken = \Nette\Utils\Random::generate();
        
        return $this->config->url['authorization'].'?'
            .  http_build_query(array(
                'response_type' => 'code',
                'client_id' => $this->config->appId,
                'scope' => join(' ', $this->config->permissions),
                'state' => $this->session->csrfToken,
                'redirect_uri' => $backLink
            ));
    }

    public function getAccessToken($code, $redirectUri)
    {
        if (isset($this->accessToken)) {
            return $this->accessToken;
        }

        $params = array(
            'grant_type' => 'authorization_code',
            'client_id' => $this->config->appId,
            'client_secret' => $this->config->appSecret,
            'code' => $code,
            'redirect_uri' => $redirectUri,
        );
        
        $ch = curl_init($this->config->url['accessToken']);

        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));

        $response = curl_exec($ch);
        curl_close($ch);

        $token = json_decode($response);
        if (isset($token->error)) {
            throw new Exception($token->error_description);
        } elseif (!$token) {
            throw new Exception('token error');
        }
        
        $this->session->access_token = $this->accessToken = $token->access_token;
        
        return $token->access_token;
    }
    
    public function createDialog()
    {
        return new LoginDialog($this);
    }
    
    /**
     * Call LinkedIn API
     * 
     * @param string $endpoint
     * @return array
     * @throws Exception
     */
    public function call($endpoint)
    {
        if (!$this->accessToken) {
            throw new Exception('no access token');
        }
        
        $url = $this->config->url['api'].$endpoint.'?'
            .http_build_query(array(
                'oauth2_access_token' => $this->accessToken,
                'format' => 'json'
            ));
        
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);
        curl_close($ch);
        
        $json = json_decode($response);
        
        if (isset($json->errorCode)) {
            unset($this->session->access_token);
            throw new Exception($json->message, $json->errorCode);
        }
        
        return $json;
    }

    public function clearSession()
    {
        foreach ($this->session as $name => $item) {
            unset($this->session->$name);
        }
    }
}