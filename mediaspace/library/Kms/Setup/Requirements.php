<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

/*
 * This class checks requirements for KMS3 according to requirements.ini
// * ini directives are interperted as part of function name which is executed through the __construct
 * 
 * Created on Dec 1, 2011
 * @author Gonen Radai
 */

class Kms_Setup_Requirements
{
    const REQUIREMENT_PASSED_TEXT = 'Passed';
    const REQUIREMENT_FAILED_TEXT = 'Failed';
    const REQUIREMENT_OPTIONAL_TEXT = 'Recommended';
    const MOD_REWRITE_EXPECTED_TEXT = 'OK';

    private $_config;
    private $_status = true;
    private $_list = array();
    private $_replacementTokens = array(
        '{basepath}' => 'replaceBasePath',
        '{majorversion}' => 'replaceMajorVersion',
    );

    /**
     * constructor of this class already calls all functions and performs requirements tests.
     * simply create a new object and get the results from it.
     */
    function __construct($runtime = false)
    {
        
        $requirements = new Zend_Config_Ini(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'configs/requirements.ini');
        foreach ($requirements as $configName => $config)
        {
            // construct function name fo checking the requirement
            $functionName = 'checkRequirement' . ucfirst($configName);
            
            foreach($config as $key => $configItem)
            {
                if(!$runtime || (isset($configItem->runtime) && $configItem->runtime == 1))
                {
                    // if function exists - call it to check the requirement
                    if (method_exists($this, $functionName))
                    {
                        
                        $this->$functionName($configItem);
                    }
                }
                
            }
            
        }
    }

    /**
     * replace tokens in a description text in case we want to provide automatic values
     * 
     * @param string $desc
     * @return string
     */
    private function replacementTokensInDescription($desc)
    {
        foreach ($this->_replacementTokens as $key => $value)
        {
            if (strpos($desc, $key) !== false)
            {
                $desc = str_replace($key, $this->$value(), $desc);
            }
        }
        return $desc;
    }
    
    /**
     * add an item to the _list array
     * to show later in the checklist
     * 
     * @param string $key unique array key 
     * @param string $desc description of list item
     * @param string $status textual representation of requirement status
     * @return void 
     */
    private function addListItem($key, $desc, $status)
    {
        $key = (string) $key;
    	if(isset($this->_list[$key]))
        {
            
            //throw new Exception("key exists in list - either use update or change key");
        }
        $this->_list[$key] = array(
            'description' => $this->replacementTokensInDescription($desc),
            'status' => $status,
        );
    }
    
    /**
     * update status of item in the list
     * 
     * @param string $key unique array key 
     * @param string $status textual representation of requirement status
     */
    private function updateListItemStatus($key, $status)
    {
    	if(!isset($this->_list[$key]))
        {
            Kms_Log::log("requirements: key does not exist in list - cannot update", Kms_Log::WARN);
            throw new Exception("key does not exist in list - cannot update");
        }
        $this->_list[$key]['status'] = $status;
    }

    /**
     * check php version requirement 
     */
    private function checkRequirementPhpversion($config)
    {
        $key = $this->getRandomKey();
        
        if (!defined('PHP_VERSION_ID')) 
        {
            $version = explode('.', PHP_VERSION);

            define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
        }

        // PHP_VERSION_ID is defined as a number, where the higher the number 
        // is, the newer a PHP version is used. It's defined as used in the above 
        // expression:
        //
        // $version_id = $major_version * 10000 + $minor_version * 100 + $release_version;
        //
        // Now with PHP_VERSION_ID we can check for features this PHP version 
        // may have, this doesn't require to use version_compare() everytime 
        // you check if the current PHP version may not support a feature.
        //
        // For example, we may here define the PHP_VERSION_* constants thats 
        // not available in versions prior to 5.2.7

        if (PHP_VERSION_ID < 50207) 
        {
            define('PHP_MAJOR_VERSION',   $version[0]);
            define('PHP_MINOR_VERSION',   $version[1]);
            define('PHP_RELEASE_VERSION', $version[2]);
        }        
        
        $phpVersion = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION .'.' . PHP_RELEASE_VERSION;
        
        if(isset($config->required))
        {
            $required = true;
            $version = $config->required;
        }
        elseif(isset($config->optional))
        {
            $required = false;
            $version = $config->optional;
        }

        if(isset($version))
        {
            $res = version_compare($phpVersion, $version, $config->operator);

            if(!$res)
            {
                if($required)
                {
                    $this->addListItem($key, $config->description, self::REQUIREMENT_FAILED_TEXT);
                    $this->_status = false;
                }
                else
                {
                    $this->addListItem($key, $config->description, self::REQUIREMENT_OPTIONAL_TEXT);
                }
            }
            elseif(isset($config->required))
            {
                $this->addListItem($key, $config->description,self::REQUIREMENT_PASSED_TEXT);
            }

        }
            
    }

    /**
     * check existance of different php methods
     */
    private function checkRequirementPhpmethods($config)
    {
        $key = $this->getRandomKey();
        
        $this->addListItem($key, $config->description,self::REQUIREMENT_PASSED_TEXT);
        if (isset($config->required))
        {
            if (!function_exists($config->required))
            {
                $this->updateListItemStatus($key, self::REQUIREMENT_FAILED_TEXT);
                $this->_status = false;
            }
        } 
        elseif (isset($config->optional))
        {
            if (!function_exists($config->optional))
            {
                $this->updateListItemStatus($key, self::REQUIREMENT_OPTIONAL_TEXT);
            }
        }
    }

    /**
     * check existance of different php libraries
     */
    private function checkRequirementPhplibs($config)
    {
        $key = $this->getRandomKey();
            
        $this->addListItem($key, $config->description,self::REQUIREMENT_PASSED_TEXT);
        if (isset($config->required))
        {
            if (!extension_loaded($config->required))
            {
                $this->updateListItemStatus($key, self::REQUIREMENT_FAILED_TEXT);
                $this->_status = false;
            }
        } 
        elseif (isset($config->optional))
        {
            if (!function_exists($config->optional))
            {
                $this->updateListItemStatus($key, self::REQUIREMENT_OPTIONAL_TEXT);
            }
        }
    }

    /**
     * check if configs folder is writable
     */
    private function checkRequirementPathwritable($config)
    {
        $key = $this->getRandomKey();
        $this->addListItem($key, $config->description,self::REQUIREMENT_PASSED_TEXT);

        if (isset($config->required))
        {
            if (!is_writable(APPLICATION_PATH . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . $config->required))
            {
                $this->updateListItemStatus($key, self::REQUIREMENT_FAILED_TEXT);
                $this->_status = false;
            }
        }
        elseif(isset($config->optional))
        {
            if (!is_writable(APPLICATION_PATH . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . $config->optional))
            {
                $this->updateListItemStatus($key, self::REQUIREMENT_OPTIONAL_TEXT);
            }
        }
            
    }
    
    /**
     * check if mod-rewrite is enabled for the site
     */
    private function checkRequirementModrewrite($config)
    {
        $key = $this->getRandomKey();
        $this->addListItem($key, $config->description,self::REQUIREMENT_PASSED_TEXT);
        $status = true;
        $apacheModules = apache_get_modules();
        if(!in_array('mod_rewrite', $apacheModules))
        {
            $this->updateListItemStatus($key, self::REQUIREMENT_FAILED_TEXT);
            $this->_status = false;
        }
        
    }
    
    // @todo: add check for htaccess (check the APPLICATION_ENV apache env var)
    private function checkRequirementHtaccess($config)
    {
        $key = $this->getRandomKey();
        // add note about htaccess
        $this->addListItem($key, $config->description,self::REQUIREMENT_PASSED_TEXT);
        if(!getenv('HTACCESS_ENABLED'))
        {
            $this->updateListItemStatus($key, self::REQUIREMENT_FAILED_TEXT);
            $this->_status = false;
        }
    }

    /**
     * returns the installation path of KMS on the disk (applications/../)
     * 
     * @return string
     */
    private function replaceBasePath()
    {
        return realpath(APPLICATION_PATH . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR);
    }

    private function replaceMajorVersion()
    {
        $helper = new Kms_View_Helper_MajorVersion();
        return $helper->MajorVersion();
    }

    /**
     * returns the list of requirements and their status
     * 
     * @return array
     */
    public function getList()
    {
        return $this->_list;
    }

    /**
     * returns the accumulated status of requirements
     * 
     * @return bool
     */
    public function getStatus()
    {
        return $this->_status;
    }
    
    public function getFailed()
    {
        $ret = array();
        if(is_array($this->_list) && count($this->_list))
        {
            foreach($this->_list as $key => $item)
            {
                if($item['status'] == 'Failed')
                {
                    $ret[$key] = $item['description'];
                }
            }
        }
        return $ret;
    }
    
    
    private function getRandomKey()
    {
        return mt_rand(10000000, 99999999);
    }

}