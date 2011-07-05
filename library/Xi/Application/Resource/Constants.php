<?php

namespace Xi\Application\Resource;

use Zend_Application_Resource_ResourceAbstract as ResourceAbstract;

/**
 * Constant setting resource
 * 
 * @author pekkis
 * @package Xi\Application
 *
 */
class Constants extends ResourceAbstract
{
	
	
	public function init()
	{

		$options = $this->getOptions();
		
		foreach($options as $constant => $value) {
			defined($constant) || define($constant, $value);
		}
		
	}
	
	
	
}
