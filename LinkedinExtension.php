<?php

namespace Fabian\Linkedin;

class LinkedinExtension extends \Nette\DI\CompilerExtension
{
    /**
    * @var array
    */
    public $defaults = array(
       'appId' => NULL,
       'appSecret' => NULL,
    );

    public function loadConfiguration()
    {
        $builder = $this->getContainerBuilder();
        
        $config = $this->getConfig($this->defaults);
        \Nette\Utils\Validators::assert($config['appId'], 'string', 'Application ID');
        \Nette\Utils\Validators::assert($config['appSecret'], 'string', 'Application secret');
        \Nette\Utils\Validators::assert($config['permissions'], 'list', 'permissions scope');
        
        $configurator = $builder->addDefinition($this->prefix('config'))
            ->setClass('Fabian\Linkedin\Configuration')
            ->setArguments(array($config['appId'], $config['appSecret'], $config['permissions']))
            ->setInject(FALSE);

        $builder->addDefinition($this->prefix('client'))
            ->setClass('Fabian\Linkedin\Linkedin')
            ->setInject(FALSE);
    }

    /**
     * @param \Nette\Configurator $configurator
     */
    public static function register(\Nette\Configurator $configurator)
    {
        $configurator->onCompile[] = function ($config, \Nette\DI\Compiler $compiler) {
            $compiler->addExtension('linkedin', new LinkedinExtension());
        };
    }
}