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
  protected $version = '0.0.1';


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
   * API URL
   * @var string
   **/
  private $ontraport_url = 'https://api.ontraport.com/1/';


  /**
   * API Call - API Method
   * @var string
   **/
  private $apiCall_methodAPI = null;

  /**
   * API Call - HTTP Method
   * @var string
   **/
  private $apiCall_methodHTTP = null;

  /**
   * API Call - Data
   * @var string
   **/
  private $apiCall_data = null;


  /**
   * A variable to hold debugging information
   * @var array
   **/
  public $debug = array();


  /**
   * A variable to hold debugging information
   * @var array
   **/
  public $klogger = null;



  /**
   * Class constructor
   *
   * @param string $appid The App ID
   * @param string $key The App Key
   * @return null
   **/
  public function __construct($appid = null, $key = null){
    // Initialise Logger
    require_once('../inc/KLogger.php');
    $this->klogger = KLogger::instance( __DIR__.'/../log' , KLogger::DEBUG );
    // Set API Credentials
    $this->setAPICredentials($appid, $key);
  }

  /**
   * Set the API Credentials
   *
   * @param string $appid The App ID
   * @param string $key The API Key
   * @return boolean
   **/
  public function setAPICredentials($appid = null, $key = null){
    $this->klogger->logDebug("setAPICredentials(\$appid = '{$appid}', \$key = '{$key}')");
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
    return (!is_null($this->ontraport_appid) && !is_null($this->ontraport_key));
  }

  /**
   * Prepare a Call to the API
   *
   * @param string @method_api The API method to be called
   * @param string @method_http The HTTP method to be used
   * @param array $data The Data to be sent to the API
   * @return boolean
   **/
  public function prepCall($method_api = null, $method_http = 'GET', $data = array()){
    $this->klogger->logDebug("prepCall(\$method_api = '{$method_api}', \$method_http = '{$method_http}', \$data = ".json_encode($data).")");
    // Sanitise
    $method_api = trim($method_api, ' /');
    $method_http = strtoupper($method_http);
    // Check Parameters
    if(is_null($method_api)){
      $this->klogger->logError('$method_api is null');
      return false;
    }
    // Split Method
    list($api_object, $api_action) = explode('/', $method_api, 2);
    $this->klogger->logDebug("\$api_object = '{$api_object}', \$api_action = '{$api_action}'");
    // Validate Methods and Data
    switch($api_object){

      case 'object':
        $this->klogger->logDebug("\$api_object = '{$api_object}'");
        // Check Method
        if($method_http != 'GET'){
          $this->klogger->logError("\$method_http ('{$method_http}') is invalid for \$method_api ('{$method_api}')");
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
          $this->klogger->logError("\$data_filtered['objectID'] ('{$data_filtered[objectID]}') is Invalid");
          // Invalid Object Type ID
          return false;
        }
        if(!is_int($data_filtered['id']) || $data_filtered['id']<0){
          $this->klogger->logError("\$data_filtered['id'] ('{$data_filtered[id]}') is Invalid");
          // Invalid Object ID
          return false;
        }
        $this->klogger->logDebug("\$data_filtered", $data_filtered);
        break;

      case 'objects':
        $this->klogger->logDebug("\$api_object = '{$api_object}'");
        switch($api_action){
          case false:
          case '':
          case null:
            $this->klogger->logDebug('$api_action', $api_action);
            // Check Method
            if(!in_array($method_http, array('GET', 'POST', 'PUT'))){
              $this->klogger->logError("\$method_http ('{$method_http}') is invalid for \$method_api ('{$method_api}')");
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
                $this->klogger->logDebug('$method_http', $method_http);
                # ids
                if(isset($data['ids'])){
                  if(is_array($data['ids'])){
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
                  if(is_array($data['group_ids'])){
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
                  if(is_array($data['listFields'])){
                    // Group ID array must be comma-delimited string
                    $data['listFields'] = implode(',', $data['listFields']);
                  }
                  $data_filtered['listFields'] = $data['listFields'];
                }
                break;
              case 'PUT':
                $this->klogger->logDebug('$method_http', $method_http);
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
                $this->klogger->logDebug('$method_http', $method_http);
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
          case 'getInfo':
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
              if(is_array($data['group_ids'])){
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
          case 'meta':
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
          case 'tag':
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
              if(is_array($data['ids'])){
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
              if(is_array($data['group_ids'])){
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
              if(is_array($data['listFields'])){
                // Group ID array must be comma-delimited string
                $data['listFields'] = implode(',', $data['listFields']);
              }
              $data_filtered['listFields'] = $data['listFields'];
            }
            break;
          default:
            // Unknown Action
            return false;
        }
        break;

      case 'form':
        // Check Method
        if($method_http != 'GET'){
          return false;
        }
        break;
        break;

      case 'message':
        // Check Method
        if(!in_array($method_http, array('GET', 'POST', 'PUT'))){
          return false;
        }
        break;

      case 'task':
        // Check Method
        if($method_http != 'POST'){
          return false;
        }
        switch($api_action){
          case 'cancel':
            break;
          case 'complete':
            break;
          default:
            // Unknown Action
            return false;
        }
        break;

      case 'transaction':
        switch($api_action){
          case 'processManual':
            break;
          case 'refund':
            break;
          case 'convertToDecline':
            break;
          case 'convertToCollections':
            break;
          case 'void':
            break;
          case 'voidPurchase':
            break;
          case 'returnCommission':
            break;
          case 'markPaid':
            break;
          case 'rerun':
            break;
          case 'writeOff':
            break;
          case 'order':
            break;
          case 'resendInvoice':
            break;
          default:
            // Unknown Action
            return false;
        }
        break;

      case 'landingPage':
        // Check Action
        if($api_action != 'getHostedURL'){
          // Unknown Action
          return false;
        }
        // Check Method
        if($method_http != 'GET'){
          return false;
        }
        break;

      default:
        // Unknown Object
        return false;

    }

    $this->apiCall_methodAPI  = $method_api;
    $this->apiCall_methodHTTP = $method_http;
    $this->apiCall_data       = $data_filtered;

  }

  /**
   * Send a Call to the API
   *
   * @return Associative Array
   **/
  public function sendCall(){

    if(is_null($this->apiCall_methodAPI)){
      // No API Method Set
      $this->klogger->logError('No API Method Set');
      return false;
    }
    if(is_null($this->apiCall_methodHTTP)){
      // No HTTP Method Set
      $this->klogger->logError('No HTTP Method Set');
      return false;
    }
    if(is_null($this->apiCall_data)){
      // No Data Set
      $this->klogger->logError('No Data Set');
      return false;
    }

    $this->klogger->logDebug('$this->apiCall_methodAPI', $this->apiCall_methodAPI);
    $this->klogger->logDebug('$this->apiCall_methodHTTP', $this->apiCall_methodHTTP);
    $this->klogger->logDebug('$this->apiCall_data', $this->apiCall_data);

    // Initialise Output array
    $output = array(
      'ok'        => null ,
      'http_code' => null ,
      'error'     => null ,
      'response'  => null
    );

    // Build URL
    $target_url = $this->ontraport_url.$this->apiCall_methodAPI;

    if($this->apiCall_methodHTTP == 'GET'){
      if(function_exists('http_build_query')){
        $target_url .= '?'.http_build_query($this->apiCall_data);
      }else{
        $get_params = array();
        foreach($this->apiCall_data as $k => $v){
          $get_params[] = "$k=".urlencode($v);
        }
        $target_url .= '?'.implode('&', $get_params);
      }
    }

    $curl_headers_raw = array(
      'Accept'          => 'application/json' ,
      #'Accept-Encoding' => 'gzip, deflate, sdch' ,
      'Api-Appid'       => $this->ontraport_appid ,
      'Api-Key'         => $this->ontraport_key ,
    );
    $curl_headers = array();
    foreach($curl_headers_raw as $k => $v){
      $curl_headers[] = "$k: $v";
    }
    $this->klogger->logDebug('$curl_header', $curl_headers);

    // Create a cURL handle
    $ch = curl_init();

    // Set the URL
    curl_setopt($ch, CURLOPT_URL, $target_url);
    // Set a Custom Useragent for Tracking Purposes
    curl_setopt($ch, CURLOPT_USERAGENT, 'Ontraport API PHP Class v'.$this->version);
    // Set the Custom Request Headers for Ontraport
    curl_setopt($ch, CURLOPT_HTTPHEADER, $curl_headers);
    // Do not ouput the HTTP request header
    curl_setopt($ch, CURLINFO_HEADER_OUT, true);
    // Do not ouput the HTTP response header
    curl_setopt($ch, CURLOPT_HEADER, true);
    // Save the response to a string
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // This may be necessary, depending on your server's configuration
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    // Get the full response
    curl_setopt($ch, CURLOPT_VERBOSE, true);

    // Execute the CURL Request
    $response_raw = curl_exec($ch);
    $this->klogger->logDebug('$response_raw', $response_raw);
    // Get Details of the Request
    $request_info = curl_getinfo($ch);
    // Split the Response into Header and Body
    list($response_header, $response_body) = explode("\r\n\r\n", $response_raw, 2);

    #if(gzdecode($response_body)){
    #  $response_body = gzdecode($response_body);
    #}

    $this->klogger->logDebug('$response_header', $response_header);
    $this->klogger->logDebug('$response_body', $response_body);

    $output['http_code'] = $request_info['http_code'];

    if($request_info['http_code'] == 200){

      $output['ok']       = true;
      $output['error']    = false;
      $output['response'] = json_decode($response_body);

    }else{
      
      $output['ok']       = false;
      $output['error']    = $response_body;
      $output['response'] = $response_body;
      
    }

    $this->klogger->logDebug('$request_info', $request_info);

    $this->klogger->logDebug('$output', $output);

    return $output;

  }
  
}

?>