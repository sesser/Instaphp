<?php
/**
 * @package Instaphp
 * @filesource
 */
namespace Instaphp {

    /**
     * The Response object.
     * This is the object passed back to the caller of this framework. It mimcs
     * Instagram's JSON objects returned from most (if not all) of its current
     * endpoints. Not all properties will be populated, so isset() should be used
     * when dealing with a response object's properties
     * @package Instaphp
     * @version 1.0
     * @author randy sesser <randy@instaphp.com>
     */
    class Response
    {

        /**
         * The meta "object" (contains a status code. 200 when successful)
         * @var object
         * @access public
         */
        public $meta = null;
        /**
         * The data "object" contains everything. Too much to list here.
         * See {@link https://api.instagram.com/developer/ Instagram Developer API}
         * @var object
         * @access public
         */
        public $data = null;
        /**
         * The pagination "object" is not your typical pagination
         * @var object
         * @access public
         */
        public $pagination = null;
        /**
         * If an error occurred, this will be populated. Check here first.
         * <code>
         * if (isset(\$response->error)) {
         *  echo \$response->error->message;
         * }
         * </code>
         * @var object
         * @access public
         */
        public $error = null;
        /**
         * When authenticating, this is populated with the access token and basic
         * user info returned from the API
         * @var object
         * @access public
         */
        public $auth = null;
        /**
         * This is the raw JSON response returned from the API. Usefull if 
         * you just want a "passthrough" situation or perhaps you want to embed
         * the JSON string in the page and parse it with JavaScript.
         * <code>
         * var response = JSON.parse('<?php echo \$response->json ?>');
         * </code>
         * @var string
         */
        public $json = '';

        public function __construct()
        {
            
        }

        /**
         * A convenience method to parse the response text and build a Response object
         * @access public 
         * @static
         * @param type $responseText The response from the API call
         * @return Response A new Response object
         */
        public static function FromResponseText($responseText)
        {
            if (empty($responseText))
                return null;

            $res = new Response;
            $obj = json_decode($responseText);

            if (empty($obj)) {
                $res->error->message = 'Unknown error occurred.';
                $res->error->type = 'Unknown';
            }


            if (isset($obj->{'message'})) {
                $res->error->message = $obj->{'message'};
                $res->error->type = $obj->{'type'};
            }

            if (isset($obj->{'access_token'})) {
                $res->auth->access_token = $obj->{'access_token'};
                $res->auth->user = $obj->{'user'};
            }

            if (isset($obj->{'meta'}))
                $res->meta = $obj->{'meta'};

            if (isset($obj->{'data'}))
                $res->data = $obj->{'data'};

            if (isset($obj->{'pagination'}))
                $res->pagination = $obj->{'pagination'};

            $res->json = $responseText;

            return $res;
        }

    }

}