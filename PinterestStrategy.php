<?php
/**
 * Pinterest strategy for Opauth
 * based on https://developers.pinterest.com/docs/api/overview/
 * 
 * More information on Opauth: http://opauth.org
 * 
 * @copyright    Copyright © 2012 U-Zyn Chua (http://uzyn.com)
 * @link         http://opauth.org
 * @package      Opauth.PinterestStrategy
 * @license      MIT License
 */

class PinterestStrategy extends OpauthStrategy {
    /**
     * Compulsory config keys, listed as unassociative arrays
     * eg. array('app_id', 'app_secret');
     */
    public $expects = array('client_id', 'client_secret');

    /**
     * Optional config keys with respective default values, listed as associative arrays
     * eg. array('scope' => 'email');
     */
    public $defaults = array(
        'redirect_uri' => '{complete_url_to_strategy}oauth2callback',
        'pinterest_profile_url' => 'https://www.pinterest.com/{username}'
    );

    /**
     * Auth request
     */
    public function request() {
        $url = 'https://api.pinterest.com/oauth/';
        
        $params = array(
            'client_id' => $this->strategy['client_id'],
            'redirect_uri' => $this->strategy['redirect_uri'],
            'response_type' => 'code'
        );

        if (!empty($this->strategy['scope']))
            $params['scope'] = $this->strategy['scope'];
        if (!empty($this->strategy['response_type']))
            $params['response_type'] = $this->strategy['response_type'];
        
        // redirect to generated url
        $this->clientGet($url, $params);
    }

    /**
     * Internal callback, after Pinterest's OAuth
     */
    public function oauth2callback() {
        if (array_key_exists('code', $_GET) && !empty($_GET['code'])) {
            $code = trim($_GET['code']);
            $url = 'https://api.pinterest.com/v1/oauth/token';

            $params = array(
                'client_id' =>$this->strategy['client_id'],
                'client_secret' => $this->strategy['client_secret'],
                'redirect_uri'=> $this->strategy['redirect_uri'],
                'grant_type' => 'authorization_code',
                'code' => $code
            );

            $response = $this->serverPost($url, $params, null, $headers);

            $results = json_decode($response);

            if (!empty($results) && !empty($results->access_token)) {
                $profileResponse = $this->getProfile($results->access_token);
                $profile = $profileResponse->data;
                
                $this->auth = array(
                    'provider' => 'Pinterest',
                    'uid' => $profile->id,
                    'info' => array(
                        'name' => sprintf('%s %s', $profile->first_name, $profile->last_name),
                        'nickname' => $profile->username,
                        'image' => $profile->image->{'60x60'}->url,
                        'urls' => array(
                            'pinterest' => str_replace('{username}', $profile->username, $this->strategy['pinterest_profile_url'])
                        )
                    ),
                    'credentials' => array(
                        'token' => $results->access_token,
                        //'expires' => date('c', time() + $results->expires_in)
                    ),
                    'raw' => $profile
                );

                /**
                 * NOTE:
                 * Pinterest's access_token have no explicit expiry, however, please do not assume your 
                 * access_token is valid forever.
                 *
                 * Missing optional info values
                 * - email
                 */
                
                $this->callback();
            } else {
                $error = array(
                    'provider' => 'Pinterest',
                    'code' => 'access_token_error',
                    'message' => 'Failed when attempting to obtain access token',
                    'raw' => array(
                        'response' => $userinfo,
                        'headers' => $headers
                    )
                );

                $this->errorCallback($error);
            }
        } else {
            $error = array(
                'provider' => 'Pinterest',
                'code' => $_GET['error'],
                'reason' => $_GET['error_reason'],
                'message' => $_GET['error_description'],
                'raw' => $_GET
            );
            
            $this->errorCallback($error);
        }
    }

    /**
     * Queries Pinterest API for user info
     *
     * @param   string  $access_token 
     * @return  array   Parsed JSON results
     */
    private function getProfile($access_token) {
        if (empty($this->strategy['profile_fields'])) {
            $this->strategy['profile_fields'] = array('id', 'first_name', 'last_name', 'username', 'image');
        }

        if (is_array($this->strategy['profile_fields'])) {
            $fields = implode(',', $this->strategy['profile_fields']);
        } else {
            $fields = $this->strategy['profile_fields'];
        }

        $userinfo = $this->serverGet('https://api.pinterest.com/v1/users/me/', array('access_token' => $access_token, 'fields' => $fields), null, $headers);

        if (!empty($userinfo)) {
            return json_decode($userinfo);
        } else {
            $error = array(
                'provider' => 'Pinterest',
                'code' => 'userinfo_error',
                'message' => 'Failed when attempting to query for user information',
                'raw' => array(
                    'response' => $userinfo,
                    'headers' => $headers
                )
            );

            $this->errorCallback($error);
        }
    }
}
?>
