<?php
/**
 * @access protected
 * @author Judzhin Miles <info[woof-woof]msbios.com>
 */

namespace MSBios\Apigility\Filter;

use MSBios\Apigility\Exception\InvalidArgumentException;
use Zend\Db\Sql\Predicate\Operator;
use Zend\Db\Sql\Predicate\Predicate;
use Zend\Db\Sql\Predicate\PredicateInterface;
use Zend\Db\Sql\Predicate\PredicateSet;
use Zend\Filter\FilterInterface;
use Zend\Stdlib\ArrayUtils;

/**
 * Class CriteriaFilter
 * @package MSBios\Apigility\Filter
 */
class CriteriaFilter implements FilterInterface
{
    /**
     * @param mixed $value
     * @return array|mixed|PredicateInterface|PredicateSet
     */
    public function filter($value)
    {

        if (empty($value)) {
            return $value;
        }

        if ($value instanceof \Traversable) {
            $value = ArrayUtils::iteratorToArray($value);
        }

        /** @var PredicateInterface $predicateSet */
        $predicateSet = new PredicateSet;

        /** @var array $filter */
        foreach ($value as $filter) {

            /** @var PredicateInterface $predicate */
            $predicate = self::factory($filter);

            if ($predicate instanceof PredicateInterface) {
                $predicateSet->addPredicate($predicate);
            }
        }

        return $predicateSet->count() ? $predicateSet : [];
    }

    /**
     * @param array $criteria
     * @return Predicate
     */
    private static function factory(array $criteria)
    {
        /** @var PredicateInterface $predicate */
        $predicate = new Predicate;

        switch ($criteria['operator']) {
            case Operator::OP_EQ:
            case 'eq':
                if (! is_null($criteria['value'])) {
                    return $predicate->equalTo($criteria['property'], $criteria['value']);
                }
                return $predicate->isNull($criteria['property']);
                break;

            case Operator::OP_NE:
                if (! is_null($criteria['value'])) {
                    return $predicate->notEqualTo($criteria['property'], $criteria['value']);
                }
                return $predicate->isNotNull($criteria['property']);
                break;

            case Operator::OP_LT:
            case 'lt':
                return $predicate->lessThan($criteria['property'], $criteria['value']);
                break;

            case Operator::OP_GT:
            case 'gt':
                return $predicate->greaterThan($criteria['property'], $criteria['value']);
                break;

            case 'in':
                if (is_null($criteria['value'])) {
                    return $predicate->isNull($criteria['property']);
                }

                if (! is_array($criteria['value'])) {
                    /** @var array $filters */
                    $criteria['value'] = [$criteria['value']];
                }

                return $predicate->in($criteria['property'], $criteria['value']);
                break;

            case 'like':
                $criteria['value'] = addslashes($criteria['value']);

                if (! is_array($criteria['property'])) {
                    return $predicate->literal("UPPER({$criteria['property']}) LIKE UPPER('{$criteria['value']}%')");
                }

                /** @var PredicateInterface $predicateSet */
                $predicateSet = new PredicateSet;

                /** @var string $property */
                foreach ($criteria['property'] as $property) {
                    $predicateSet->addPredicate(
                        (new Predicate)->literal("UPPER({$property}) LIKE UPPER('{$criteria['value']}%')"),
                        PredicateSet::COMBINED_BY_OR
                    );
                }

                return $predicateSet;

                break;
            default:
                throw new InvalidArgumentException('Criteria not found!');
                break;
        }
    }
}
