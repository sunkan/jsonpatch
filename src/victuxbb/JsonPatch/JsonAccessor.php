<?php
/**
 * Created by PhpStorm.
 * User: victuxbb
 * Date: 8/12/14
 * Time: 4:22
 */

namespace victuxbb\JsonPatch;

class JsonAccessor
{

    /**
     * constructor
     *
     * @param PaserInterface $parser JSON Pointer Parser
     */
    public function __construct(JsonPointerParserInterface $parser)
    {
        $this->parser = $parser;
    }

    /**
     * get pointed value
     *
     * @param string $pointer JSON Pointer string
     * @param array  $array   target array
     *
     * @throws Exception\SyntaxError
     * @throws Exception\NoneExistentValue
     */
    public function get($pointerArray, $array)
    {
        $current = $array;
        foreach ($pointerArray as $key) {
            if (!is_array($current) || !array_key_exists($key, $current)) {
                throw new Exception\NoneExistentValue('references none existent value.');
            }

            $current = $current[$key];
        }

        return $current;
    }

    /**
     * set pointed value
     *
     * @param string $pointer JSON Pointer string
     * @param array  &$array   target array
     * @param mixed  $value   value to set
     *
     * @throws Exception\SyntaxError
     * @throws Exception\NoneExistentValue
     */

    public function set($pointerArray, &$array, $value)
    {
        $depth = count($pointerArray);
        while (list($key, $val) = each($pointerArray)) {
            if (is_array($array[$val]) && $depth !== ($key + 1)) {
                $nextArray = &$array[$val];
                $this->set($pointerArray, $nextArray, $value);
                break;
            } else {
                $array[$val] = $value;
            }
        }
    }

    /**
     * set pointed value in the location of an array
     *
     * @param string $pointer JSON Pointer string
     * @param array  &$array   target array
     * @param mixed  $value   value to set
     *
     * @throws Exception\SyntaxError
     * @throws Exception\NoneExistentValue
     */
    public function insert($pointerArray, &$array, $value)
    {
        $depth = count($pointerArray);
        while (list($key, $val) = each($pointerArray)) {
            if (isset($array[$val]) && is_array($array[$val]) && $depth !== ($key + 1)) {
                $nextArray = &$array[$val];
                $this->insert($pointerArray, $nextArray, $value);
                break;
            } else {
                if ($this->parser->destinationIsLocationInArray()) {
                    if (is_array($value) || is_object($value)) {
                        $value = [$value];
                    }
                    array_splice($array, $val, 0, $value);
                } else {
                    $array[$val] = $value;
                }
            }
        }
    }

    public function remove($pointerArray, &$array)
    {
        $depth = count($pointerArray);
        while (list($key, $val) = each($pointerArray)) {
            if (isset($array[$val]) && is_array($array[$val]) && $depth !== ($key + 1)) {
                $nextArray = &$array[$val];
                $this->remove($pointerArray, $nextArray);
                break;
            } else {
                if ($this->parser->destinationIsLocationInArray()) {
                    array_splice($array, $val, 1);
                } else {
                    unset($array[$val]);
                }
            }
        }
    }
}
