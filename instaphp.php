<?php

/**
 * @package Instaphp
 * @filesource
 */

namespace Instaphp {
    
    require_once('config.php');
    require_once('request.php');
    require_once('response.php');
    require_once('instagram/base.php');
    require_once('instagram/users.php');
    require_once('instagram/media.php');
    require_once('instagram/tags.php');
    require_once('instagram/locations.php');

    use Instaphp\Instagram\Base;
    use Instaphp\Instagram\Users;
    use Instaphp\Instagram\Media;
    use Instaphp\Instagram\Tags;
    use Instaphp\Instagram\Locations;
    use Instaphp\Request;
    use Instaphp\Response;

    /**
     * A simple base class used to instantiate the various other API classes
     * @package Instaphp
     * @version 1.0
     * @author randy sesser <randy@instaphp.com>
     */
    class Instaphp
    {

        /**
         * @link Instaphp\Instagram\Users
         * @var Instaphp\Instagram\Users
         * @access public
         */
        public $Users = null;
        /**
         * @link Instaphp\Instagram\Media
         * @var Instaphp\Instagram\Media
         * @access public
         */
        public $Media = null;
        /**
         * @link Instaphp\Instagram\Tags
         * @var Instaphp\Instagram\Tags
         * @access public
         */
        public $Tags = null;
        /**
         * @link Instaphp\Instagram\Locations
         * @var Instaphp\Instagram\Locations
         */
        public $Locations = null;

        /**
         * The constructor constructs
         */
        public function __construct()
        {
            $this->Users = new Users;
            $this->Media = new Media;
            $this->Tags = new Tags;
            $this->Locations = new Locations;
        }

    }

}