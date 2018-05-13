<?php
/**
 * @access protected
 * @author Judzhin Miles <judzhin[woof-woof]gns-it.com>
 */

namespace MSBios\Apigility\InputFilter;

use MSBios\Apigility\Filter\CriteriaFilter;
use MSBios\Apigility\Filter\DirectionFilter;
use MSBios\Filter\Json\Decoder;
use Zend\InputFilter\InputFilter;

/**
 * Class QueryFilter
 * @package MSBios\Apigility\InputFilter
 */
class QueryFilter extends InputFilter
{
    /**
     * QueryFilter constructor.
     * @param array $data
     */
    public function __construct($data = [])
    {
        $this->init();
        $this->setData($data);
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        /** @var array $decode */
        $decode = [
            'name' => Decoder::class,
        ];

        $this->add([
            'name' => 'filter',
            'filters' => [
                $decode, [
                    'name' => CriteriaFilter::class,
                ]
            ]
        ])->add([
            'name' => 'sort',
            'filters' => [
                $decode, [
                    'name' => DirectionFilter::class,
                ]
            ]
        ]);
    }
}
