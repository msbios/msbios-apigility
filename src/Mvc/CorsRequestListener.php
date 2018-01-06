<?php
/**
 * @access protected
 * @author Judzhin Miles <info[woof-woof]msbios.com>
 */
namespace MSBios\Apigility\Mvc;

use Zend\Mvc\MvcEvent;
use ZfrCors\Mvc\CorsRequestListener as DefaultCorsRequestListener;

/**
 * Class CorsRequestListener
 * @package MSBios\Apigility\Mvc
 */
class CorsRequestListener extends DefaultCorsRequestListener
{
    /**
     * @param MvcEvent $event
     */
    public function onCorsRequest(MvcEvent $event)
    {
        try {
            parent::onCorsRequest($event);
        } catch (\Exception $e) {
            // I have an exception.... but I ignore it!
        }
    }
}
