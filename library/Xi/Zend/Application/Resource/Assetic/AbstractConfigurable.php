<?php
namespace Xi\Zend\Application\Resource\Assetic;

class AbstractConfigurable
{
    /**
     * @var array default options
     */
    protected $options = array(
    );
    
    /**
     * @param array $options
     */
    public function __construct($options)
    {
        $this->options = array_merge($this->options, $options);
    }
    
    /**
     * @param string $name
     * @param mixed $default optional, defaults to null
     * @return mixed
     */
    public function getOption($name, $default = null)
    {
        return isset($this->options[$name]) ? $this->options[$name] : $default;
    }
    
    /**
     * @param string $name option
     * @return string
     */
    protected function getRelativeApplicationPathOption($name)
    {
        return realpath(APPLICATION_PATH . DIRECTORY_SEPARATOR . $this->getOption($name));
    }
}