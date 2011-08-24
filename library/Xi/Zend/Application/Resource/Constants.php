<?php
namespace Xi\Zend\Application\Resource;

use Zend_Application_Resource_ResourceAbstract as ResourceAbstract;

/**
 * Constant setting application resource
 *
 * @category   Xi
 * @package    Application
 * @subpackage Resource
 * @author     pekkis
 * @license    http://www.opensource.org/licenses/BSD-3-Clause New BSD License
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
