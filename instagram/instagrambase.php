<?php

/**
 * @package Instaphp
 * @filesource
 */

namespace Instaphp\Instagram {

    use Instaphp\Config;
    use Instaphp\Request;
    use Instaphp\Response;

    /**
     * Instaphp Base Instagram class. 
     * @package Instaphp
     * @subpackage Instagram
     * @version 1.0
     * @author randy <randy@instaphp.com>
     */
    class InstagramBase
    {

        /**
         * The Instaphp Config object
         * @var Instaphp\Config
         * @access protected
         */
        protected $config = null;
        /**
         * The base API path appended to the endpoint
         * @var string
         * @access protected
         */
        protected $api_path;
        /**
         * Parameters passed to the API
         * @var Array
         * @access protected
         */
        protected $api_params;
        /**
         * The access token used to authenticate API calls
         * @var string
         * @access protected
         */
        protected $access_token;
        /**
         * Our request object
         * @var Instaphp\Request
         * @access protected
         */
        protected $request;

        /**
         * Constructor. 
         * If you inherit from this class, you must call the parent constructor
         * <code>
         * public function __construct() {
         *  parent::__construct();
         * }
         * </code>
         * @access public
         */
        public function __construct()
        {
            $this->config = Config::Instance();
            $this->api_params = array(
                'client_id' => $this->config->Instagram->ClientId
            );
            $this->request = new Request();
        }

        /**
         * A convenience method for making Get requests via the request
         * @access protected
         * @param string $url A url in which to make a request
         * @param Array $params An associative array of key/value pairs to pass to the API
         * @return Response
         */
        protected function Get($url = null, $params = array())
        {
            if (empty($url))
                trigger_error('A URL is required in ' . __METHOD__, E_USER_ERROR);
            $this->AddParams($params);
            return $this->request->Get($url, $this->api_params)->response;
        }

        /**
         * A convenience method for making POST request via the request object
         * @access protected
         * @param string $url A url in which to make a POST request
         * @param Array $params An associative array of key/value pairs to POST to the API
         * @return Response
         */
        protected function Post($url = null, $params = array())
        {
            if (empty($url))
                trigger_error('A URL is required in ' . __METHOD__, E_USER_ERROR);
            $this->AddParams($params);
            return $this->request->Post($url, $this->api_params)->response;
        }

        /**
         * A convenience method for making DELETE requests via the request object
         * @access protected
         * @param string $url A url in which to make a DELETE request
         * @param Array $params An associative array of key/value pairs to pass to the API
         * @return Response
         */
        protected function Delete($url = null, $params = array())
        {
            if (empty($url))
                trigger_error('A URL is required in ' . __METHOD__, E_USER_ERROR);
            $this->AddParams($params);
            return $this->request->Delete($url, $this->api_params)->response;
        }

        /**
         * A convenience method that builds a base URL to the Instagram API based on
         * values in the config.xml file and the $api_path property. It also adds
         * the access token to the global parameters if it is set elsewhere.
         * @access protected
         * @param string $path The path to append to the base URL to create the endpoint
         * @param string $action The 'action' to append to the endpoint (not always used, but available)
         * @return string The Instagram API endpoint
         */
        protected function BuildUrl($path = null, $action = null)
        {
            $uri = $this->config->Instagram->Endpoint . '/' . $this->config->Instagram->Version;


            if (!empty($path) && substr($path, 0, 1) !== '/')
                $path = '/' . $path;

            $path = $this->api_path . $path;

            $uri .= $path;

            if (null !== $action) {
                if (substr($action, 0, 1) !== '/')
                    $action = '/' . $action;

                if (substr($action, strlen($action) - 1, 1) !== '/')
                    $action .= '/';

                $uri .= $action;
            }

            if (!empty($this->access_token))
                $this->AddParam('access_token', $this->access_token);

            return $uri;
        }

        /**
         * A convenience method for bulk adding querystring parameters 
         * to the API requests. Note that existing params will be over-written.
         * @access public
         * @param Array $params Associative array of key/value pairs to add
         * @return void
         * {@source}
         */
        public function AddParams($params = array())
        {
            if (!empty($params)) {
                foreach ($params as $name => $value)
                    $this->api_params[$name] = $value;
            }
        }

        /**
         * A convenience method for adding a single parameter to the querystring
         * passed to the API. Note this will overwrite existing parameters with 
         * the same name.
         * @access public
         * @param string $name The parameter name
         * @param string $value The value to pass
         * @return void
         * {@source}
         */
        public function AddParam($name, $value)
        {
            $this->api_params[$name] = $value;
        }

        /**
         * A convenience method for removing parameters from the querystring.
         * @access public
         * @param string $name The name of the parameter to remove
         * @return void
         * {@source}
         */
        public function RemoveParam($name)
        {
            if (isset($this->api_params[$name]))
                unset($this->api_params[$name]);
        }

    }

}