<?php

/**
 *
 * A PHP class that acts as wrapper for the Ontraport API
 *
 * Read Ontraport API documentation at https://github.com/Ontraport/ontra_api_examples
 *
 * Copyright 2016 Luke Stevenson <lucanos.com>
 * Licensed under the MIT License
 * @author Luke Stevenson
 * @copyright 2016 Luke Stevenson
 * @license http://opensource.org/licenses/MIT
 * @link http://github.com/lucanos/ontraport-api-php-class
 * @version 0.0.1
 *
 **/

class Ontraport {

  /**
   * API App ID
   * @var string
   **/
  private $ontraport_appid = null;

  /**
   * API Key
   * @var string
   **/
  private $ontraport_key = null;

  /**
   * A variable to hold debugging information
   * @var array
   **/
  public $debug = array();



  /**
   * Class constructor
   *
   * @param string $appid The App ID
   * @param string $key The App Key
   * @return null
   **/
  public function __construct($appid = null, $key = null){
    $this->setAPICredentials($appid, $key);
  }

  /**
   * Set the API Credentials
   *
   * @param string $appid The App ID
   * @param string $key The API Key
   * @return boolean
   **/
  public setAPICredentials($appid = null, $key = null){
    // Sanitise and Check APP ID
    $appid = trim($appid);
    if(!is_null($appid) && !empty($appid)){
      $this->ontraport_appid = $appid;
    }else{
      $this->ontraport_appid = null;
    }
    // Sanitise and Check API Key
    $key = trim($key);
    if(!is_null($key) && !empty($key)){
      $this->ontraport_key = $key;
    }else{
      $this->ontraport_key = null;
    }
    // Return TRUE if AppID and API Key are Set
    return (!is_null($this->ontraport_appid) && !is_null($this->ontraport_key))
  }

  /**
   * Send a Call to the API
   *
   * @param string @method_api The API method to be called
   * @param string @method_http The HTTP method to be used
   * @param array $data The Data to be sent to the API
   * @return array The response as a associative array
   **/
  private function sendCall($method_api = null, $method_http = 'GET', $data = array()){
    // Sanitise
    $method_api = trim($method_api, ' /');
    $method_http = strtoupper($method_http);
    // Check Parameters
    if(is_null($method_api)){
      return false;
    }
    // Split Method
    list($api_object, $api_action) = explode('/', $method_api, 2);
    // Validate Methods and Data
    switch($method_api){
      case 'object':
        // Check Method
        if($method_http != 'GET'){
          return false;
        }
        // Set Defaults
        $data_default = array(
          'objectID' => null,
          'id' => null
        );
        $data_filtered = array_intersect_key($data, $data_default);
        // Check Data
        if(!is_int($data_filtered['objectID']) || $data_filtered['objectID']<0){
          // Invalid Object Type ID
          return false;
        }
        if(!is_int($data_filtered['id']) || $data_filtered['id']<0){
          // Invalid Object ID
          return false;
        }
        break;
      case 'objects':
        // Check Method
        if(!in_array($method_http, array('GET', 'POST', 'PUT'))){
          return false;
        }
        // Set Defaults
        $data_default = array(
          'objectID' => null
        );
        $data = array_merge($data, $data_default);
        // Check Data
        if(!is_int($data['objectID']) || $data['objectID']<0){
          // Invalid Object Type ID
          return false;
        }
        $data_filtered = array(
          'objectID' => $data['objectID']
        );
        switch($method_http){
          case 'GET':
            # ids
            if(isset($data['ids'])){
              if(is_array($data['ids']){
                // ID array must be comma-delimited string
                $data['ids'] = implode(',', $data['ids']);
              }
              if(!preg_match('/^\d+(?:\,\d+)*/', $data['ids'])){
                // Invalid Object IDs
                return false;
              }
              $data_filtered['ids'] = $data['ids'];
            }
            # performAll
            if(isset($data['performAll'])){
              $data_filtered['performAll'] = (bool) $data['performAll'];
            }
            # start
            if(isset($data['start'])){
              if(!is_int($data['start'])){
                // Should be Integer
                return false;
              }
              $data_filtered['start'] = $data['start'];
            }
            # range
            if(isset($data['range'])){
              if(!is_int($data['range'])){
                // Should be Integer
                return false;
              }
              if($data['range']<1){
                // Must be at least 1
                return false;
              }
              if($data['range']>50){
                // Must be less than or equal to 50
                return false;
              }
              $data_filtered['range'] = $data['range'];
            }
            # sort
            if(isset($data['sort'])){
              // No Rules to Validate this
              $data_filtered['sort'] = $data['sort'];
            }
            # sortDir
            if(isset($data['sortDir'])){
              $data['sortDir'] = strtolower($data['sortDir']);
              if(!preg_match('/^(?:a|de)sc$/', $data['sortDir'])){
                // Invalid Sort Type
                return false;
              }
            }
            # condition
            if(isset($data['condition'])){
              // No Rules to Validate this
              $data_filtered['condition'] = $data['condition'];
            }
            # search
            if(isset($data['search'])){
              // No Rules to Validate this
              $data_filtered['search'] = $data['search'];
            }
            # searchNotes
            if(isset($data['searchNotes'])){
              $data_filtered['searchNotes'] = (bool) $data['searchNotes'];
            }
            # date_range
            if(isset($data['date_range'])){
              if(!is_array($data['date_range'])){
                $data['date_range'] = explode(',', $data['date_range']);
              }
              if(count($data['date_range']) != 2){
                // Insufficient Values
                return false;
              }
              if(is_string($data['date_range'][0]) && strttotime($data['date_range'][0])===false){
                // Invalid Range Start Date
                return false;
              }
              if(is_string($data['date_range'][1]) && strttotime($data['date_range'][1])===false){
                // Invalid Range End Date
                return false;
              }
            }
            # group_ids
            if(isset($data['group_ids'])){
              if(is_array($data['group_ids']){
                // Group ID array must be comma-delimited string
                $data['group_ids'] = implode(',', $data['group_ids']);
              }
              if(!preg_match('/^\d+(?:\,\d+)*/', $data['group_ids'])){
                // Invalid Group IDs
                return false;
              }
              $data_filtered['group_ids'] = $data['group_ids'];
            }
            # externs
            if(isset($data['externs'])){
              // No Rules to Validate this
              $data_filtered['externs'] = $data['externs'];
            }
            # listFields
            if(isset($data['listFields'])){
              if(is_array($data['listFields']){
                // Group ID array must be comma-delimited string
                $data['listFields'] = implode(',', $data['listFields']);
              }
              $data_filtered['listFields'] = $data['listFields'];
            }
            break;
          case 'PUT':
            # id
            if(isset($data['id'])){
              if(!is_int($data['id']) || $data['id']<0){
                // Invalid Object ID
                return false;
              }
              $data_filtered['id'] = $data['id'];
            }
            # Fallthrough
          case 'POST':
            # firstname
            if(isset($data['firstname'])){
              // No Rules to Validate this
              $data_filtered['firstname'] = $data['firstname'];
            }
            # lastname
            if(isset($data['lastname'])){
              // No Rules to Validate this
              $data_filtered['lastname'] = $data['lastname'];
            }
            # email
            if(isset($data['email'])){
              if(function_exists('filter_var')){
                if(!filter_var($data['email'], FILTER_VALIDATE_EMAIL)){
                  // Invalid Email Address
                  return false;
                }
              }else{
                if(!preg_match('/^\S+\@\S+(?:\.\S+)+$/', $data['email'])){
                  // Invalid Email Address
                  return false;
                }
              }
              $data_filtered['email'] = $data['email'];
            }
            break;
        }
        break;
      case 'objects/getInfo':
        // Check Method
        if($method_http != 'GET'){
          return false;
        }
        // Set Defaults
        $data_default = array(
          'objectID' => null
        );
        $data = array_merge($data, $data_default);
        // Check Data
        if(!is_int($data['objectID']) || $data['objectID']<0){
          // Invalid Object Type ID
          return false;
        }
        $data_filtered = array(
          'objectID' => $data['objectID']
        );
        # condition
        if(isset($data['condition'])){
          // No Rules to Validate this
          $data_filtered['condition'] = $data['condition'];
        }
        # search
        if(isset($data['search'])){
          // No Rules to Validate this
          $data_filtered['search'] = $data['search'];
        }
        # searchNotes
        if(isset($data['searchNotes'])){
          $data_filtered['searchNotes'] = (bool) $data['searchNotes'];
        }
        # date_range
        if(isset($data['date_range'])){
          if(!is_array($data['date_range'])){
            $data['date_range'] = explode(',', $data['date_range']);
          }
          if(count($data['date_range']) != 2){
            // Insufficient Values
            return false;
          }
          if(is_string($data['date_range'][0]) && strttotime($data['date_range'][0])===false){
            // Invalid Range Start Date
            return false;
          }
          if(is_string($data['date_range'][1]) && strttotime($data['date_range'][1])===false){
            // Invalid Range End Date
            return false;
          }
        }
        # group_ids
        if(isset($data['group_ids'])){
          if(is_array($data['group_ids']){
            // Group ID array must be comma-delimited string
            $data['group_ids'] = implode(',', $data['group_ids']);
          }
          if(!preg_match('/^\d+(?:\,\d+)*/', $data['group_ids'])){
            // Invalid Group IDs
            return false;
          }
          $data_filtered['group_ids'] = $data['group_ids'];
        }
        break;
      case 'saveorupdate':
        // Check Method
        if($method_http != 'POST'){
          return false;
        }
        // Set Defaults
        $data_default = array(
          'objectID' => null
        );
        $data = array_merge($data, $data_default);
        // Check Data
        if(!is_int($data['objectID']) || $data['objectID']<0){
          // Invalid Object Type ID
          return false;
        }
        $data_filtered = array(
          'objectID' => $data['objectID']
        );
        # firstname
        if(isset($data['firstname'])){
          // No Rules to Validate this
          $data_filtered['firstname'] = $data['firstname'];
        }
        # lastname
        if(isset($data['lastname'])){
          // No Rules to Validate this
          $data_filtered['lastname'] = $data['lastname'];
        }
        # email
        if(isset($data['email'])){
          if(function_exists('filter_var')){
            if(!filter_var($data['email'], FILTER_VALIDATE_EMAIL)){
              // Invalid Email Address
              return false;
            }
          }else{
            if(!preg_match('/^\S+\@\S+(?:\.\S+)+$/', $data['email'])){
              // Invalid Email Address
              return false;
            }
          }
          $data_filtered['email'] = $data['email'];
        }
        break;
      case 'objects/meta':
        // Check Method
        if($method_http != 'GET'){
          return false;
        }
        // Set Defaults
        $data_default = array(
          'format' => 'byName'
        );
        $data_filtered = array_intersect_key($data, $data_default);
        // Validate Other Fields
        # objectID
        if(isset($data['objectID'])){
          if(!is_int($data['objectID']) || $data['objectID']<0){
            // Invalid Object ID
            return false;
          }
          $data_filtered['objectID'] = $data['objectID'];
        }
        break;
      case 'objects/tag':
        // Check Method
        if(!in_array($method_http, array('PUT', 'DELETE'))){
          return false;
        }
        // Set Defaults
        $list_key = ($method_http == 'PUT' ? 'add' : 'remove').'_list';
        $data_default = array(
          'objectID' => null
        );
        $data_default[$list_key] = array();
        $data_filtered = array_intersect_key($data, $data_default);
        // Check Data
        if(!is_int($data_filtered['objectID']) || $data_filtered['objectID']<0){
          // Invalid Object Type ID
          return false;
        }
        if(!is_array($data_filtered[$list_key]) || !count($data_filtered[$list_key])){
          // Invalid Tag List
          return false;
        }
        # ids
        if(isset($data['ids'])){
          if(is_array($data['ids']){
            // ID array must be comma-delimited string
            $data['ids'] = implode(',', $data['ids']);
          }
          if(!preg_match('/^\d+(?:\,\d+)*/', $data['ids'])){
            // Invalid Object IDs
            return false;
          }
          $data_filtered['ids'] = $data['ids'];
        }
        # performAll
        if(isset($data['performAll'])){
          $data_filtered['performAll'] = (bool) $data['performAll'];
        }
        # start
        if(isset($data['start'])){
          if(!is_int($data['start'])){
            // Should be Integer
            return false;
          }
          $data_filtered['start'] = $data['start'];
        }
        # range
        if(isset($data['range'])){
          if(!is_int($data['range'])){
            // Should be Integer
            return false;
          }
          if($data['range']<1){
            // Must be at least 1
            return false;
          }
          if($data['range']>50){
            // Must be less than or equal to 50
            return false;
          }
          $data_filtered['range'] = $data['range'];
        }
        # sort
        if(isset($data['sort'])){
          // No Rules to Validate this
          $data_filtered['sort'] = $data['sort'];
        }
        # sortDir
        if(isset($data['sortDir'])){
          $data['sortDir'] = strtolower($data['sortDir']);
          if(!preg_match('/^(?:a|de)sc$/', $data['sortDir'])){
            // Invalid Sort Type
            return false;
          }
        }
        # condition
        if(isset($data['condition'])){
          // No Rules to Validate this
          $data_filtered['condition'] = $data['condition'];
        }
        # search
        if(isset($data['search'])){
          // No Rules to Validate this
          $data_filtered['search'] = $data['search'];
        }
        # searchNotes
        if(isset($data['searchNotes'])){
          $data_filtered['searchNotes'] = (bool) $data['searchNotes'];
        }
        # date_range
        if(isset($data['date_range'])){
          if(!is_array($data['date_range'])){
            $data['date_range'] = explode(',', $data['date_range']);
          }
          if(count($data['date_range']) != 2){
            // Insufficient Values
            return false;
          }
          if(is_string($data['date_range'][0]) && strttotime($data['date_range'][0])===false){
            // Invalid Range Start Date
            return false;
          }
          if(is_string($data['date_range'][1]) && strttotime($data['date_range'][1])===false){
            // Invalid Range End Date
            return false;
          }
        }
        # group_ids
        if(isset($data['group_ids'])){
          if(is_array($data['group_ids']){
            // Group ID array must be comma-delimited string
            $data['group_ids'] = implode(',', $data['group_ids']);
          }
          if(!preg_match('/^\d+(?:\,\d+)*/', $data['group_ids'])){
            // Invalid Group IDs
            return false;
          }
          $data_filtered['group_ids'] = $data['group_ids'];
        }
        # externs
        if(isset($data['externs'])){
          // No Rules to Validate this
          $data_filtered['externs'] = $data['externs'];
        }
        # listFields
        if(isset($data['listFields'])){
          if(is_array($data['listFields']){
            // Group ID array must be comma-delimited string
            $data['listFields'] = implode(',', $data['listFields']);
          }
          $data_filtered['listFields'] = $data['listFields'];
        }
        break;
    }
  }

}

?>