<?php
/**
 * @access protected
 * @author Judzhin Miles <judzhin[woof-woof]gns-it.com>
 */

namespace MSBios\Apigility\InputFilter;

use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\Json\Decoder;
use Zend\Json\Exception\RuntimeException;
use Zend\Json\Json;

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
     */
    public function __construct()
    {
        $this->init();
    }

    public function init()
    {
        parent::init();

        $this->add(new Input(self::INPUT_IDENTIFIER));
        $this->add(new Input(self::INPUT_QUERY));

        /** @var Input $objFilter */
        $objFilter = new Input(self::INPUT_FILTER);

        /** @var callable $fnDecoder */
        $fnDecoder = function ($string) {

            try {
                return Json::decode($string, Json::TYPE_ARRAY);
            } catch (RuntimeException $exc) {
                return [];
            };

            ///** @var array $result */
            //$result = Decoder::decode($string, Json::TYPE_ARRAY);
            //
            ///** @var array $result */
            //$result = json_decode($string, true);
            //
            //if ((json_last_error() != JSON_ERROR_NONE)) {
            //    /** @var array $result */
            //    return [];
            //}
            //
            //return $result;
        };

        $objFilter->getFilterChain()
            ->attach($fnDecoder);
        $this->add($objFilter);

        /** @var Input $objSort */
        $objSort = new Input(self::INPUT_SORT);
        $objSort->getFilterChain()
            ->attach($fnDecoder);
        $this->add($objSort);


        $this->add(new Input(self::INPUT_LIMIT));
        $this->add(new Input(self::INPUT_OFFSET));
    }
}
