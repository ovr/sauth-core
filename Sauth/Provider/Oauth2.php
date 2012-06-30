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
        //TODO: check the required parameters
        return true;
    }

    /**
     * Checks current step
     * @return bool
     */
    public function isRedirectStep()
    {
        return empty($_GET['code']) && empty($_GET['error']);
    }

    /**
     * Do Authorization Request
     * @url http://tools.ietf.org/html/draft-ietf-oauth-v2-28#section-4.1.1
     */
    public function doRedirectStep()
    {
        $url = $this->getAuthorizationUrl();
        $url .= http_build_query($this->getAuthorizationParameters(), null, '&');

        header('Location: ' . $url);
        exit(1);
    }

    /**
     * Checks current step
     * @return bool
     */
    public function isRequestStep()
    {
        return !empty($_GET['code']);
    }

    /**
     * Do Access Token Request
     * @url http://tools.ietf.org/html/draft-ietf-oauth-v2-28#section-4.1.3
     * @return array Ready for prepareResult
     */
    public function doRequestStep()
    {
        $code = $_GET['code'];
        $url = $this->getAccessTokenUrl();
        $parameters = $this->getAccessTokenParameters($code);

        $result = array();
        $response = Util::postRequest($url, $parameters);

        if ($response) {
            $result = array();
        } else {
            $result = parent::doErrorStep();
        }
        return $result;
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
            $result['scope'] = mplode($this->getConfiguration('scope'), ',');;
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
    public function getAuthorizationUrl()
    {
        return $this->getConfiguration('authorizationUrl');
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
        );
        return $result;
    }

    /**
     * Returns access token url
     * @return mixed
     */
    public function getAccessTokenUrl()
    {
        return $this->getConfiguration('accessTokenUrl');
    }
}
