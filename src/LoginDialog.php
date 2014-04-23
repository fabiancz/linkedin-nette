<?php

namespace Fabian\Linkedin;

/**
 * @method onResponse(\Fabian\Linkedin\LinkedinLoginDialog $dialog)
 */
class LoginDialog extends \Nette\Application\UI\PresenterComponent
{
    /**
     * @var \Fabian\Linkedin\Linkedin
     */
    protected $linkedin;
    
    /**
    * @var array of function(\Fabian\Linkedin\LoginDialog $dialog)
    */
    public $onResponse = array();
    
    public function __construct(\Fabian\Linkedin\Linkedin $linkedin,
        \Nette\ComponentModel\IContainer $parent = NULL, $name = NULL)
    {
        parent::__construct($parent, $name);
        
        $this->linkedin = $linkedin;
    }

    public function handleOpen()
    {
        if (!$this->presenter->user->isLoggedIn()) {
            $this->open();
        }
        $this->onResponse($this);
        $this->presenter->redirect('this');
    }
    
    private function open()
    {
        $url = $this->linkedin->getRedirectUrl($this->link('//response!'));
        $this->presenter->redirectUrl($url);
    }
    
    public function handleResponse()
    {
        $params = $this->presenter->params;
        if (!isset($params['code'])) {
            throw new Exception('no code!');
        }
        // check state token for CSRF attack
        if ($params['state'] != $this->linkedin->getState()) {
            throw new Exception('CSRF attack!');
        }
        $accessToken = $this->linkedin->getAccessToken(
            $params['code'], $this->link('//response!')
        );
        
        $this->onResponse($this);
        $this->presenter->redirect('this');
    }
}