<?php
/**
 * @access protected
 * @author Judzhin Miles <info[woof-woof]msbios.com>
 */

namespace MSBios\Apigility;

use MSBios\Apigility\InputFilter\WhitelistInputFilter;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Paginator\Adapter\DbTableGateway as TableGatewayPaginator;
use ZF\Apigility\DbConnectedResource as DefaultDbConnectedResource;

/**
 * Class ConnectedResource
 * @package MSBios\Apigility
 */
class ConnectedResource extends DefaultDbConnectedResource
{
    // /**
    //  * @param mixed $data
    //  * @return array|mixed
    //  */
    // protected function retrieveData($data)
    // {
    //     /** @var array $data */
    //     $data = parent::retrieveData($data);
    //
    //     /** @var string $key */
    //     foreach (['UserName', 'username'] as $key) {
    //         if (array_key_exists($key, $data)) {
    //             /** @var array $identity */
    //             $identity = $this->getIdentity()->getAuthenticationIdentity();
    //             $data[$key] = $identity['user_id'];
    //             break;
    //         }
    //     }
    //
    //     return $data;
    // }

    /**
     * @param array|object $data
     * @return array|object
     */
    public function create($data)
    {
        /** @var array $data */
        $data = $this->retrieveData($data);
        $this->table->insert($data);

        /** @var int $id */
        $id = (isset($data[$this->identifierName]))
            ? $data[$this->identifierName] : $this->table->getLastInsertValue();

        return $this->fetch($id);
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function fetchAll($params = [])
    {
        /** @var array $params */
        $params = (new WhitelistInputFilter($params))->getValues();

        /** @var AdapterInterface $adapter */
        $adapter = new TableGatewayPaginator(
            $this->table,
            $params['filter'],
            $params['sort']
        );

        return new $this->collectionClass($adapter);
    }
}
