<?php
/**
 * @access protected
 * @author Judzhin Miles <info[woof-woof]msbios.com>
 */
namespace MSBios\Apigility\Factory;

use Interop\Container\ContainerInterface;

use MSBios\Apigility\Mvc\CorsRequestListener;
use ZfrCors\Service\CorsService;

/**
 * Class CorsRequestListenerFactory
 * @package MSBios\Apigility\Factory
 */
class CorsRequestListenerFactory
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param null $options
     * @return CorsRequestListener
     */
    public function __invoke(ContainerInterface $container, $requestedName, $options = null)
    {
        return new CorsRequestListener(
            $container->get(CorsService::class)
        );
    }
}