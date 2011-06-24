<?php
/**
 * Constant setting resource
 * 
 * @author pekkis
 * @package Xi_Application
 *
 */
class Xi_Application_Resource_Constants extends Zend_Application_Resource_ResourceAbstract
{
	
	
	public function init()
	{

		$options = $this->getOptions();
		
		foreach($options as $constant => $value) {
			defined($constant) || define($constant, $value);
		}
		
	}
	
	
	
}