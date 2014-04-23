Instalation
===========

The best way is via [Composer](http://getcomposer.org/):

```
composer require fabian/linkedin-nette:dev-master
```

Usage
=====

- [register LinkedIn app](https://www.linkedin.com/secure/developer)
- add credentials to your config.neon:
```
linkedin:
    appId: "YOUR_API_KEY"
    appSecret: "YOUR_API_SECRET"
    permissions: [r_fullprofile, r_emailaddress]
```
and extension:
```
extensions:
    linkedin: \Fabian\Linkedin\LinkedinExtension
```
- in your BasePresenter injext LinkedIn extension:
```
/**
 * @var \Fabian\Linkedin\Linkedin
 */
private $linkedin;

public function __construct(\Fabian\Linkedin\Linkedin $linkedin)
{
    parent::__construct();
    $this->linkedin = $linkedin;
}
```
- and create component handling login operations (f.e. in your BasePresenter, too):
```
protected function createComponentLinkedinLogin()
{
    $dialog = $this->linkedin->createDialog();
    /** @var \Fabian\Linkedin\LoginDialog $dialog */
    
    $dialog->onResponse[] = function(\Fabian\Linkedin\LoginDialog $dialog) {
        $me = $this->linkedin->call(
            'people/~:(id,first-name,last-name,email-address)'
        );
        
        // if user is not found in your database, register new based on LinkedIn profile details
        if (!$existing = $this->usersModel->findByLinkedinId($me->id)) {
            $existing = $this->usersModel->registerFromLinkedin((array) $me);
        }

        $this->user->login(new \Nette\Security\Identity($existing->users_id, $existing->role, $existing));
    };
    
    return $dialog;
}
```
- place LinkedIn login in your template:
```
<a n:href="linkedinLogin-open!">Login by LinkedIn</a>
```

Inspired by [Kdyby\Facebook](https://github.com/kdyby/facebook)