<?php
/**
 * @access protected
 * @author Judzhin Miles <info[woof-woof]msbios.com>
 */

namespace MSBios\Apigility\Factory;

use Interop\Container\ContainerInterface;
use MSBios\Apigility\Controller\AuthenticationController;
use OAuth2\Server as OAuth2Server;
use Zend\Stdlib\DispatchableInterface;
use ZF\OAuth2\Factory\AuthControllerFactory as DefaultAuthControllerFactory;

/**
 * Class AuthenticationControllerFactory
 * @package MSBios\Apigility\Factory
 */
class AuthenticationControllerFactory extends DefaultAuthControllerFactory
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return AuthenticationController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var DispatchableInterface $instance */
        $instance = new AuthenticationController(
            $this->getOAuth2ServerFactory($container),
            $container->get('ZF\OAuth2\Provider\UserId')
        );

        $instance->setApiProblemErrorResponse(
            $this->marshalApiProblemErrorResponse($container)
        );

        return $instance;
    }

    /**
     * @inheritdoc
     * @param ContainerInterface $container
     * @return \Closure|mixed
     */
    private function getOAuth2ServerFactory(ContainerInterface $container)
    {
        $oauth2ServerFactory = $container->get('ZF\OAuth2\Service\OAuth2Server');

        if (! $oauth2ServerFactory instanceof OAuth2Server) {
            return $oauth2ServerFactory;
        }

        return function () use ($oauth2ServerFactory) {
            return $oauth2ServerFactory;
        };
    }

    /**
     * @inheritdoc
     * @param ContainerInterface $container
     * @return bool
     */
    private function marshalApiProblemErrorResponse(ContainerInterface $container)
    {
        if (! $container->has('config')) {
            return false;
        }

        $config = $container->get('config');

        return (isset($config['zf-oauth2']['api_problem_error_response'])
            && $config['zf-oauth2']['api_problem_error_response'] === true);
    }
}
