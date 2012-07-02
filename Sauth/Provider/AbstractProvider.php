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
            throw new InvalidArgumentException('Wrong provider configuration, please verify it.');
        }

        $result = false;
        if ($this->isAuthorizeStep()) {
            // here we are redirecting user
            $this->doAuthorizeStep();

        } else if($this->isAccessStep()) {
            // access token  and user data request
            $result = $this->doAccessStep();
        }

        if (!$result) {
            $result = $this->doErrorStep();
        }

        return $this->doResultStep($result);
    }

    public function doErrorStep()
    {
        return array(
            "success" => false,
            "message" => "Something went wrong",
        );
    }

    public abstract function isAuthorizeStep();
    public abstract function doAuthorizeStep();

    public abstract function isAccessStep();
    public abstract function doAccessStep();

    public abstract function validateConfiguration();

    /**
     * Formats result for the output.
     * It always return success and message keys.
     * On success it also returns credentials and all required user data.
     *
     * @param array $result
     * @return array
     */
    public final function doResultStep(array $result)
    {

        $data = array(
            'success' => $result['success'],
            'message' => $result['message'],
        );

        if ($result['success']) {

            $data['required'] = array(
                'uid' => $result['uid'],
                'userName' => $result['userName'],
            );

            $data['credentials'] = $result['credentials'];
            $data['user'] = $result['user'];
            $data['additional'] = $result['additional'];
        }

        return $data;
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
