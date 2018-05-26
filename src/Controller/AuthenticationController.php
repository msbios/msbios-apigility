<?php
/**
 * @access protected
 * @author Judzhin Miles <info[woof-woof]msbios.com>
 */
namespace MSBios\Apigility\Controller;

use Zend\Http\PhpEnvironment\Response;
use Zend\Json\Encoder;
use Zend\Json\Json;
use ZF\OAuth2\Controller\AuthController as DefaultAuthController;

/**
 * Class AuthenticationController
 * @package MSBios\Apigility\Controller
 */
class AuthenticationController extends DefaultAuthController
{
    /**
     * @return Response
     */
    public function tokenAction()
    {
        /** @var Response $httpResponse */
        $httpResponse = parent::tokenAction();

        if ($httpResponse instanceof Response
            && Response::STATUS_CODE_200 == $httpResponse->getStatusCode()) {
            /** @var array $data */
            $data = Json::decode($httpResponse->getContent(), Json::TYPE_ARRAY);
            $data['success'] = true;
            $httpResponse->setContent(Encoder::encode($data));
        }

        return $httpResponse;
    }
}
