<?php

namespace Sauth\Provider;

abstract class AbstractProvider
{

    /**
     * Configuration array
     * @var array
     */
    protected $_configuration = array();


    /**
     * Provider object constructor
     * @param array $configuration
     */
    public function __construct(array $configuration = array())
    {
        $this->setConfiguration($configuration);
    }

    /**
     * Sets configuration $value by $key
     * @param string $key
     * @param mixed $value
     */
    public function addConfiguration($key, $value)
    {
        $this->_configuration[$key] = $value;
    }

    /**
     * Sets configuration array. Replaces previous one
     * Configuration will be validated before request part
     * @param array $configuration
     */
    public function setConfiguration($configuration)
    {
        $this->_configuration = $configuration;
    }

    /**
     * Returns whole configuration array or just a specified element
     * @param mixed $key
     * @return mixed
     */
    public function getConfiguration($key = null)
    {
        if ($key) {
            return isset($this->_configuration[$key]) ? $this->_configuration[$key] : false;
        } else {
            return $this->_configuration;
        }
    }
}
