<?php
/**
 * @access protected
 * @author Judzhin Miles <info[woof-woof]msbios.com>
 */

namespace MSBios\Apigility;

use MSBios\Apigility\InputFilter\WhitelistInputFilter;
use Zend\Db\Metadata\MetadataInterface;
use Zend\Db\Metadata\Source\Factory;
use Zend\Db\ResultSet\ResultSetInterface;
use Zend\Db\Sql\Predicate\Operator;
use Zend\Db\Sql\Predicate\Predicate;
use Zend\Db\Sql\PreparableSqlInterface;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use ZF\Apigility\DbConnectedResource as DefaultDbConnectedResource;
use ZF\ApiProblem\Exception\DomainException;

/**
 * Class Resource
 * @package MSBios\Apigility
 */
class Resource extends DefaultDbConnectedResource
{
    /** @const */
    const IDENTIFIER_NAME = 'id';

    /** @const */
    const PARAM_FILTER_KEY = 'filter';

    /** @const */
    const PARAM_PAGE_KEY = 'page';

    /** @const */
    const PARAM_QUERY_KEY = 'query';

    /** @const */
    const PROPERTY_OPERATOR_KEY = 'operator';

    /** @const */
    const PROPERTY_NAME_KEY = 'property';

    /** @const */
    const PROPERTY_VALUE_KEY = 'value';

    /** @const */
    const PROPERTY_DIRECTION_KEY = 'direction';

    /** @var null */
    protected $viewName = null;

    /** @var string */
    protected $queryFieldName = 'name';

    /**
     * @param mixed $data
     * @return array|mixed
     */
    protected function retrieveData($data)
    {
        /** @var array $data */
        $data = parent::retrieveData($data);

        /** @var string $key */
        foreach (['UserName', 'username'] as $key) {
            if (array_key_exists($key, $data)) {
                /** @var array $identity */
                $identity = $this->getIdentity()->getAuthenticationIdentity();
                $data[$key] = $identity['user_id'];
                break;
            }
        }

        return $data;
    }

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
     * @param Select $select
     * @param array $params
     * @param callable|null $callback
     */
    protected function populateParams(Select $select, array $params, callable $callback = null)
    {
        /** @var mixed $id */
        if ($id = $params[WhitelistInputFilter::INPUT_IDENTIFIER]) {
            /** @var Predicate $predicate */
            $predicate = new Predicate;
            $predicate->equalTo(WhitelistInputFilter::INPUT_IDENTIFIER, $id);
            $select->where($predicate);
            return;
        }

        /** @var array $filters */
        if ($filters = $params[WhitelistInputFilter::INPUT_FILTER]) {

            /** @var array $filter */
            foreach ($filters as $filter) {

                /** @var Predicate $predicate */
                $predicate = new Predicate;

                /** @var string $operator */
                $operator = isset($filter[self::PROPERTY_OPERATOR_KEY])
                    ? $filter[self::PROPERTY_OPERATOR_KEY] : 'like';

                if (! isset($filter[self::PROPERTY_VALUE_KEY])) {
                    continue;
                }

                /** @var string $value */
                $value = $filter[self::PROPERTY_VALUE_KEY];

                switch ($operator) {
                    case 'eq':
                    case Operator::OP_EQ:
                        $predicate->equalTo($filter[self::PROPERTY_NAME_KEY], $value);
                        break;

                    case 'in':
                        $predicate->in($filter[self::PROPERTY_NAME_KEY], $value);
                        break;

                    case 'lt':
                    case Operator::OP_LT:
                        $predicate->lessThan($filter[self::PROPERTY_NAME_KEY], $value);
                        break;

                    case 'gt':
                    case Operator::OP_GT:
                        $predicate->greaterThan($filter[self::PROPERTY_NAME_KEY], $value);
                        break;

                    case 'like':
                    default:
                        /** @var string $like */
                        $like = "{$value}%";
                        if (is_array($filter[self::PROPERTY_NAME_KEY])) {

                            /** @var string $identifier */
                            foreach ($filter[self::PROPERTY_NAME_KEY] as $identifier) {
                                /** @var Predicate $tmp */
                                $tmp = new Predicate;
                                $tmp->like($identifier, $like);
                                $predicate->addPredicate($tmp, Predicate::COMBINED_BY_OR);
                            }
                        } else {
                            $predicate->like($filter[self::PROPERTY_NAME_KEY], $like);
                        }

                        break;
                }

                $select->where($predicate);
            }
        }

        /** @var string $query */
        if ($query = $params[WhitelistInputFilter::INPUT_QUERY]) {
            // /** @var MetadataInterface $metadata */
            // $metadata = Factory::createSourceFromAdapter($this->table->getAdapter());
            //
            // /** @var array $columns */
            // $columns = $metadata->getColumns($this->table->getTable());

            /** @var Predicate $predicate */
            $predicate = new Predicate;
            $predicate->like($this->queryFieldName, "{$query}%");
            $select->where($predicate);
        }

        /** @var array $sort */
        if ($sort = $params['sort']) {
            foreach ($sort as $filter) {
                $select->order(
                    [$filter[self::PROPERTY_NAME_KEY] => $filter[self::PROPERTY_DIRECTION_KEY]]
                );
            }
        }

        if ($callback) {
            $callback($select, $params);
        }
    }

    /**
     * @param int|string $id
     * @return mixed
     */
    public function fetch($id)
    {
        /** @var PreparableSqlInterface $select */
        $select = new Select($this->viewName ?: $this->table->getTable());
        $select->where([$this->identifierName => $id]);

        /** @var ResultSetInterface $resultSet */
        $resultSet = $this->table->selectWith($select);
        if (0 === $resultSet->count()) {
            throw new DomainException('Item not found', 404);
        }

        return $resultSet->current();
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function fetchAll($params = [])
    {
        /** @var  $inputFilter */
        $inputFilter = new WhitelistInputFilter;

        /** @var array $params */
        $params = $inputFilter->setData($params)->getValues();

        /** @var Select $select */
        $select = new Select($this->viewName ?: $this->table->getTable());
        $this->populateParams($select, $params);

        return new $this->collectionClass(
            new DbSelect($select, $this->table->getAdapter())
        );
    }
}
