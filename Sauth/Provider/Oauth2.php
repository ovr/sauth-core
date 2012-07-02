<?php

namespace Sauth\Provider;

use Sauth\Provider\AbstractProvider;
use Sauth\Util\Util;

class Oauth2 extends AbstractProvider
{

    /**
     * Checks provider configuration
     * @return bool
     */
    public function validateConfiguration()
    {
        $configuration = $this->getConfiguration();

        return !empty($configuration['authorizationUri'])
            && !empty($configuration['accessTokenUri'])
            && !empty($configuration['clientId'])
            && !empty($configuration['responseType'])
            && !empty($configuration['redirectUri']);
    }

    /**
     * Checks current step
     * @return bool
     */
    public function isAuthorizeStep()
    {
        return empty($_GET['code']) && empty($_GET['error']);
    }

    /**
     * Do Authorization Request
     * @url http://tools.ietf.org/html/draft-ietf-oauth-v2-28#section-4.1.1
     * @exit
     */
    public function doAuthorizeStep()
    {
        $url = $this->getAuthorizationUri();
        $url .= '?' . http_build_query($this->getAuthorizationParameters(), null, '&');

        header('Location: ' . $url);
        exit(1);
    }

    /**
     * Checks current step
     * @return bool
     */
    public function isAccessStep()
    {
        return !empty($_GET['code']);
    }

    /**
     * Do Access Token Request
     * @url http://tools.ietf.org/html/draft-ietf-oauth-v2-28#section-4.1.3
     * @return array Ready for prepareResult
     */
    public function doAccessStep()
    {
        $code = $_GET['code'];

        $url = $this->getAccessTokenUri();
        $parameters = $this->getAccessTokenParameters($code);

        $result = false;
        $response = Util::postRequest($url, $parameters);

        if ($response && empty($response['error'])) {
            $result = $this->requestUserData($response);
        }

        return $result;
    }

    /**
     * Requests user data and prepares response to the doResultStep function
     * @abstract
     * @param $response
     * @return mixed
     */
    public function requestUserData($response) {
        return array('success' => true, 'message' => 'Success', 'credentials' => array('accessToken' => $response['access_token']));
    }

    /**
     * onError handler
     * @return array Ready for prepareResult
     */
    public function doErrorStep()
    {
        if (!empty($_GET['error'])) {
            $message = $_GET['error'];

            if (!empty($_GET['error_description'])) {
                $message .= ": " . $_GET['error_description'];
            }

            return array(
                'success' => false,
                'message' => $message,
            );
        }

        return parent::doErrorStep();
    }

    /**
     * Returns authorization parameters
     * @return array
     */
    public function getAuthorizationParameters()
    {
        $result = array(
            'client_id' => $this->getConfiguration('clientId'),
            'response_type' => $this->getConfiguration('responseType'),
        );

        if ($this->getConfiguration('redirectUri')) {
            $result['redirect_uri'] = $this->getConfiguration('redirectUri');
        }

        if ($this->getConfiguration('scope')) {
            $result['scope'] = implode($this->getConfiguration('scope'), ',');
        }

        if ($this->getConfiguration('state')) {
            $result['state'] = $this->getConfiguration('state');
        }
        return $result;
    }

    /**
     * Returns authorization url
     * @return mixed
     */
    public function getAuthorizationUri()
    {
        return $this->getConfiguration('authorizationUri');
    }

    /**
     * Returns access token parameters
     * @param $code
     * @return array
     */
    public function getAccessTokenParameters($code)
    {
        $result = array(
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->getConfiguration('redirectUri'),
            // I'm not sure about two parameters bellow, but most popular oauth2 servers require them
            'client_id' => $this->getConfiguration('clientId'),
            'client_secret' => $this->getConfiguration('clientSecret'),
        );
        return $result;
    }

    /**
     * Returns access token url
     * @return mixed
     */
    public function getAccessTokenUri()
    {
        return $this->getConfiguration('accessTokenUri');
    }
}
