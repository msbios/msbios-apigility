<?php
/**
 * @access protected
 * @author Judzhin Miles <judzhin[woof-woof]gns-it.com>
 */

namespace MSBios\Apigility\InputFilter;

use MSBios\Apigility\Filter\CriteriaFilter;
use MSBios\Apigility\Filter\DirectionFilter;
use MSBios\Filter\Json\Decoder;
use Zend\Filter\StringTrim;
use Zend\Filter\StripTags;
use Zend\InputFilter\InputFilter;

/**
 * Class WhitelistInputFilter
 * @package MSBios\Apigility\InputFilter
 */
class WhitelistInputFilter extends InputFilter
{
    /** @const INPUT_IDENTIFIER */
    const INPUT_IDENTIFIER = 'id';

    /** @const INPUT_QUERY */
    const INPUT_QUERY = 'query';

    /** @const INPUT_FILTER */
    const INPUT_FILTER = 'filter';

    /** @const INPUT_SORT */
    const INPUT_SORT = 'sort';

    /** @const INPUT_SORT */
    const INPUT_LIMIT = 'limit';

    /** @const INPUT_OFFSET */
    const INPUT_OFFSET = 'offset';

    /**
     * WhitelistInputFilter constructor.
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

        /** @var array $trim */
        $trim = [
            'name' => StringTrim::class,
        ];

        /** @var array $decoder */
        $decoder = [
            'name' => Decoder::class,
        ];

        $this->add([
            'name' => self::INPUT_IDENTIFIER,
            'filters' => [
                $trim
            ]
        ])->add([
            'name' => self::INPUT_QUERY,
            'filters' => [
                $trim, [
                    'name' => StripTags::class,
                ],
            ]
        ])->add([
            'name' => self::INPUT_FILTER,
            'filters' => [
                $decoder, [
                    'name' => CriteriaFilter::class,
                ]
            ]
        ])->add([
            'name' => self::INPUT_SORT,
            'filters' => [
                $decoder, [
                    'name' => DirectionFilter::class,
                ]
            ]
        ])->add([
            'name' => self::INPUT_LIMIT,
            'filters' => [
                $trim
            ]
        ])->add([
            'name' => self::INPUT_OFFSET,
            'filters' => [
                $trim
            ]
        ]);
    }
}
