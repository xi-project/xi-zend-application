<?php
namespace Xi\Zend\Application\DI;

/**
 * Provides named dependencies for components and allows overrides.
 * 
 * Suggested usage is to accept a DependencyProvider in the constructors
 * of service classes and use the default provider when none was given.
 * This allows for easy (and type-aware) usage of services by initializing
 * them with `new` while permitting overrides where needed, most importantly
 * in tests.
 * 
 * A dependency provider may have a parent to which lookups default.
 */
class DependencyProvider
{
    /**
     * @var DependencyProvider
     */
    protected $parent;
    
    /**
     * @var array
     */
    protected $functions;
    
    
    public function __construct(DependencyProvider $parent = null)
    {
        $this->parent = $parent;
        $this->functions = array();
    }
    
    /**
     * Returns a named dependency object, or throws if not found.
     */
    public final function get($name)
    {
        $obj = $this->tryGet($name);
        if ($obj) {
            return $obj;
        } else {
            throw new \RuntimeException("Dependency `$name` not found.");
        }
    }
    
    /**
     * Returns a named dependency object, or null if not found.
     */
    public function tryGet($name)
    {
        if (isset($this->functions[$name])) {
            $f = $this->functions[$name];
            return $f();
        } elseif ($this->parent) {
            return $this->parent->tryGet($name);
        } else {
            return null;
        }
    }
    
    public function put($name, $object)
    {
        $this->functions[$name] = function() use ($object) {
            return $object;
        };
    }
    
    /**
     * Causes $func to be called every time $name is asked for.
     */
    public function putFunc($name, $func)
    {
        $this->functions[$name] = $func;
    }
    
    
    protected static $instance;
    
    /**
     * @return DependencyProvider
     */
    public static function getDefault()
    {
        return self::$instance;
    }
    
    public static function setDefault(DependencyProvider $provider)
    {
        self::$instance = $provider;
    }
}