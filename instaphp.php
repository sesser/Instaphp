<?php

/**
 * @package Instaphp
 * @filesource
 */

namespace Instaphp {
    
    require_once('config.php');
    require_once('request.php');
    require_once('response.php');
    require_once('instagram/instagrambase.php');
    require_once('instagram/users.php');
    require_once('instagram/media.php');
    require_once('instagram/tags.php');
    require_once('instagram/locations.php');
    
    /**
     * A simple base class used to instantiate the various other API classes
     * @package Instaphp
     * @version 1.0
     * @author randy sesser <randy@instaphp.com>
     */
    class Instaphp
    {

        /**
         * @var Users
         * @access public
         */
        public $Users = null;
        /**
         * @var Media
         * @access public
         */
        public $Media = null;
        /**
         * @var Tags
         * @access public
         */
        public $Tags = null;
        /**
         * @var Locations
         */
        public $Locations = null;

        private static $instance = null;
        /**
         * The constructor constructs, but only for itself
         */
        final private function __construct()
        {
            $this->Users = new Instagram\Users;
            $this->Media = new Instagram\Media;
            $this->Tags = new Instagram\Tags;
            $this->Locations = new Instagram\Locations;
        }
        
        /**
         * I AM SINGLETON
         * We don't need to go instantiating all these objects more than once here
         * @return Instaphp 
         */
        public static function Instance()
        {
            if (self::$instance == null) {
                self::$instance = new self();
            }
            return self::$instance;
        }
    }

}