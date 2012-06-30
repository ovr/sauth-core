<?php

namespace Sauth\Provider;

use Sauth\Exception\InvalidArgumentException;

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

    public function authenticate()
    {

        // first step is to verify provider configuration
        if (!$this->validateConfiguration()) {
            throw new InvalidArgumentException("Wrong provider configuration, please verify it.");
        }

        if ($this->isRedirectStep()) {
            // here we are redirecting user in his browser
            $this->doRedirectStep();

        } else if($this->isRequestStep()) {
            $result = $this->doRequestStep();

        } else {
            // if we are here that's mean something went wrong
            $result = $this->doErrorStep();
        }

        return $this->prepareResult($result);
    }

    public function doErrorStep()
    {
        return array(
            "success" => false,
            "message" => "Something went wrong",
        );
    }

    public abstract function isRedirectStep();
    public abstract function doRedirectStep();

    public abstract function isRequestStep();
    public abstract function doRequestStep();

    public abstract function validateConfiguration();

    /**
     * Prepare result for the output.
     * It always return success and message keys.
     * On success it also returns credentials and all required user data.
     *
     * @param array $result
     * @return array
     */
    public final function prepareResult(array $result)
    {

        $return = array(
            'success' => $result['success'],
            'message' => $result['message'],
        );

        if ($result['success']) {

            $return['required'] = array(
                'uid' => $result['uid'],
                'username' => $result['username'],
                'email' => $result['email'],
                'fullName' => $result['fullName'],
            );

            $return['credentials'] = $result['credentials'];
            $return['user'] = $result['user'];
            $return['other'] = $result['other'];
        }

        return $return;
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
