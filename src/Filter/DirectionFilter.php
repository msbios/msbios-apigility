<?php
/**
 * @access protected
 * @author Judzhin Miles <info[woof-woof]msbios.com>
 */

namespace MSBios\Apigility\Filter;

use Zend\Filter\FilterInterface;

/**
 * Class DirectionFilter
 * @package MSBios\Apigility\Filter
 */
class DirectionFilter implements FilterInterface
{
    /**
     * @param mixed $value
     * @return mixed
     */
    public function filter($value)
    {
        if (!empty($value)) {
            /**
             * @var int $i
             * @var array $v
             */
            foreach ($value as $i => $v) {
                $value[$v['property']] = $v['direction'];
                unset($value[$i]);
            }
        }

        return $value;
    }
}
