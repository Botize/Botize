<?php

/**
 * Evernotize - Botize application for Evernote
 *
 * @author Néstor Soriano
 */

// Client credentials. Fill in these values with the consumer key and consumer secret 
// that you obtained from Evernote. If you do not have an Evernote API key, you may
// request one from http://dev.evernote.com/documentation/cloud/
define('OAUTH_CONSUMER_KEY', 'YourConsumerKey');
define('OAUTH_CONSUMER_SECRET', 'YourConsumerSecret');

// Replace this value with FALSE to use Evernote's production server
define('SANDBOX', TRUE);

require_once(dirname(dirname(dirname(__FILE__))) . "/BTZ_AppBase.php");

define("trigger_name", "new_shared_note");
define("action_name", "create_note");

define("EVERNOTE_LIBS", dirname(__FILE__) . DIRECTORY_SEPARATOR . "evernote_sdk");
ini_set("include_path", ini_get("include_path") . PATH_SEPARATOR . EVERNOTE_LIBS);

require_once 'Evernote/Client.php';
require_once 'packages/Types/Types_types.php';

use Evernote\Client;

class BTZ_App_evernotize extends BTZ_AppBase {
    
    /*** Protected methods ***/
    
    protected function getUserAuthMode() {
        return "web";
    }
   
    
    protected function getAppTitle($language) {
        return "Evernotize";
    }
    
    
    protected function getSupportedLanguages() {
        return array("en", "es");
    }
    
    
    protected function getFunctionForm($functionName) {
        if($functionName == action_name) {
            $form = <<< END
{{txt_title}}:<br/><input type="text" name="title"/><br/>
{{txt_body}}:<br/><textarea name="body" cols="40" rows="10"></textarea><br/>
{{txt_notebook}}:<br/><input type="text" name="notebook"/><br/>
{{txt_tags}}:<br/><input type="text" name="tags"/><br/>
END;
            return $form;
        } else {
            return null;
        }
    }
    

    protected function getFunctionFormTexts($functionName, $language) {
        $texts = array();
        
        $texts["en"] = array(
            action_name => array (
                "txt_title" => "Note title",
                "txt_body" => "Note body",
                "txt_notebook" => "Notebook",
                "txt_tags" => "Tags"
            )
        );
        
        $texts["es"] = array(
            action_name => array (
                "txt_title" => "Título de la nota",
                "txt_body" => "Cuerpo de la nota",
                "txt_notebook" => "Cuaderno",
                "txt_tags" => "Etiqueta"
            )
        );
        
        return $texts[$language][$functionName];
    }
    
    
    protected function getFunctionOutputVars($functionName) {
        if($functionName == trigger_name) {
            return array(
                "title" => "text",
                "url" => "url",
                "created" => "date"
            );
        } else {
            return array();
        }
    }
    

    protected function getFunctionOutputVarsTexts($functionName, $language) {
        if($functionName == trigger_name) {
            $texts = array();
            
            $texts["en"] = array(
                "title" => "Title",
                "url" => "Url",
                "created" => "Creation date"
            );
            
            $texts["es"]  = array(
                "title" => "Título",
                "url" => "Url",
                "created" => "Fecha de creación"
            );
            
            return $texts[$language];
        } else {
            return array();
        }
    }
    
    
    protected function getFunctionMaxPollInterval($functionName) {
        return "15m";
    }
    
    
    protected function getFunctionCaption($functionName, $language) {
        $texts = array();
        
        $texts["en"] = array(
            trigger_name => "New shared note",
            action_name => "Create note"
        );
        
        $texts["es"] = array(
            trigger_name => "Nueva nota compartida",
            action_name => "Crear nota"
        );
        
        return $texts[$language][$functionName];
    }
    
    
    public function getBotizeAuthMode() {
        return "none";
    }
    
    
    public function botizeCredentialsAreValid($user, $password) {
        return $user == "theBotize!" && $password == "thePassword!";
    }    
    

    protected function validateFormData($functionName, $formData, $trigger_output_vars, $language, $authentication) {
        if($language != "es") {
            $language = "en";
        }
        
        $results["en"] = array(
            action_name => array(
                "All fields are mandatory"
            )
        );
        
        $results["es"] = array(
            action_name => array(
                "Todos los campos son obligatorios"
            )
        );
    
        
        $fields = array("title", "body", "notebook");
        
        foreach($fields as $field) {
            if(!$formData[$field]) {
                return $results[$language][$functionName];
            }
        }
        
        return null;
    }
    
    
    protected function beginAuthenticateUser($callback) {
        if (strlen(OAUTH_CONSUMER_KEY) == 0 || strlen(OAUTH_CONSUMER_SECRET) == 0) {
            return new BTZ_HttpErrorResult(500, "Evernote OAuth keys are not configured in server");
        }
        
        $data = array(
            'oauth_consumer_key' => OAUTH_CONSUMER_KEY,
            'oauth_signature' => OAUTH_CONSUMER_SECRET . "&",
            'oauth_signature_method' => 'PLAINTEXT',
            'oauth_timestamp' => time(),
            'oauth_nonce' => uniqid(),
            'oauth_callback' => $callback
        );
        
        $result = $this->curlUsingGet($this->evernote_url() . '/oauth', $data);
        
        if(is_numeric($result)) {
            return new BTZ_HttpErrorResult(500, "Error obtaining temporary token, curl or HTTP error code: " . $result);
        }
        
        parse_str($result, $temp_credentials);
        
        if($temp_credentials["oauth_callback_confirmed"] != "true") {
            return new BTZ_HttpErrorResult(500, "Error obtaining temporary token: callback not confirmed");
        }
        
        $temp_token = $temp_credentials["oauth_token"];
        $auth_url = $this->evernote_url() . "/OAuth.action?oauth_token=" . urlencode($temp_token);
        
        $result = new BTZ_BeginAuthenticateUserOutputData($auth_url, $temp_token);
        return $result;
    }
    
    
    protected function endAuthenticateUser($service_data, $saved_temp_data) {
        if (!isset($service_data['oauth_verifier'])) {
            $result = new BTZ_EndAuthenticateUserOutputData(false, null, null);
            return $result;
        }
        
        if(!$saved_temp_data) {
            return new BTZ_HttpErrorResult(400, "Temporary authentication data is empty");
        }
        
        $saved_temp_token = $saved_temp_data;
        $received_temp_token = $service_data['oauth_token'];
        if($received_temp_token != $saved_temp_token) {
            return new BTZ_HttpErrorResult(400, "Temporary access token mismatch");
        }
        
        $data = array(
            'oauth_consumer_key' => OAUTH_CONSUMER_KEY,
            'oauth_signature' => OAUTH_CONSUMER_SECRET . "&",
            'oauth_signature_method' => 'PLAINTEXT',
            'oauth_timestamp' => time(),
            'oauth_nonce' => uniqid(),
            'oauth_token' => $received_temp_token,
            'oauth_verifier' => $service_data['oauth_verifier']
        );
        
        $result = $this->curlUsingGet($this->evernote_url() . '/oauth', $data); 
        
        if(is_numeric($result)) {
            return new BTZ_HttpErrorResult(500, "Error obtaining access token, curl or HTTP error code: " . $result);
        }
        
        parse_str($result, $token_data);
        //$user_id = $token_data['edam_userId'];
        $auth_data_to_save = array(
            'access_token' => $token_data['oauth_token'],
            'access_token_secret' => $token_data['oauth_token_secret']
        );
        
        $client = new Evernote\Client(array('token' => $auth_data_to_save["access_token"]));
        try {
            $userStore = $client->getUserStore();
            $user = $userStore->getUser($auth_data_to_save["access_token"]);
        } catch (Exception $e) {
            $outData = $this->GetOutDataForException($e);
            return new BTZ_HttpErrorResult(500, "Error obtaining username: " . $outData->status_message);
        }
        
        $result = new BTZ_EndAuthenticateUserOutputData(
                        true,
                        $user->username,
                        json_encode($auth_data_to_save));
        return $result;
    }
    
    function evernote_url() {
        if(SANDBOX == true) {
            return "https://sandbox.evernote.com";
        } else {
            return "https://www.evernote.com";
        }
    }
    
    //Executes a HTTP GET request to the specified $url,
    //passing the associative array $data in the query string.
    //Returns the body of the HTTP response, or in case of error,
    //the error code returned by curl_errno or the HTTP status code.
    //
    //Adapted from a sample found here:
    //http://www.silverphp.com/php-curl-function-for-post-and-get-request.html
    function curlUsingGet($url, $data)
    {
        if(empty($url) OR empty($data))
        {       
            return 'Error: invalid Url or Data';
        }

        //url-ify the data for the get  : Actually create datastring
        $fields_string = '';
        foreach($data as $key=>$value) { $fields_string .= $key.'='.urlencode($value).'&'; }
        $fields_string = rtrim($fields_string,'&');

        $urlStringData = $url.'?'.$fields_string;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Set curl to return the data instead of printing it to the browser.
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,10); # timeout after 10 seconds, you can increase it
        curl_setopt($ch, CURLOPT_USERAGENT , "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)"); 
        curl_setopt($ch, CURLOPT_URL, $urlStringData); #set the url and get string together
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER , false);

        $data = curl_exec($ch);
        $curl_errno = (int)curl_errno($ch);
        $info = curl_getinfo($ch);
        curl_close($ch); 
        
        if($curl_errno != 0) {
            return $curl_errno;
        }
        
        $status_code = (int)$info['http_code'];
        if($status_code < 200 || $status_code > 299) {
            return $status_code;
        }
        
        return $data;
    }
    
    
    /*** Functions ***/
    
    function trigger_new_shared_note($inputData) {
        //NOTE: In order to keep things simple, 
        //new notes are searched for only in the
        //first available public notebook.
        
        if(!isset($inputData->authentication)) {
            return new BTZ_HttpErrorResult(400, "Received input data (authentication) is invalid");
        }
        
        $authData = json_decode($inputData->authentication["auth_saved_data"], true);
        if($authData == null) {
            return new BTZ_HttpErrorResult(400, "Received input data (auth_saved_data) is invalid");
        }
        
        $token = $authData["access_token"];
        if($token == null) {
            return new BTZ_HttpErrorResult(400, "Received input data does not contain access token");
        }
        
        try {
            $client = new Evernote\Client(array('token' => $token));
            $noteStore = $client->getNoteStore();
            $notebooks = $noteStore->listNotebooks();
        } catch (Exception $e) {
            return $this->GetOutDataForException($e);
        }
        
        $publicNotebook = null;
        foreach($notebooks as $notebook) {
            if($notebook->publishing) {
                $publicNotebook = $notebook;
                break;
            }
        }

        if($publicNotebook == null) {
            $outData = new BTZ_FunctionOutputData();
            $outData->status_code = 200;
            $outData->status_message = "No public notebooks available";
            return $outData;
        }

        $filter = new EDAM\NoteStore\NoteFilter();
        $filter->ascending = false;
        $filter->notebookGuid = $publicNotebook->guid;
        $filter->order = 4; //update sequence number 
        
        $result = $noteStore->findNotes($filter, 0, 1);

        if(count($result->notes) == 0) {
            $outData = new BTZ_FunctionOutputData();
            $outData->status_code = 0;
            $outData->status_message = "No notes available on this notebook";
            return $outData;
        }
        
        $note = $result->notes[0];
        $noteUpdateCount = $note->updateSequenceNum;
        
        if(!isset($inputData->saved_data)) {
            $outData = new BTZ_FunctionOutputData();
            $outData->status_code = 1;
            $outData->status_message = "No previous update count available";
            $outData->data_to_save = $currentUpdateCount;
            return $outData;
        }
        
        $previousUpdateCount = intval($inputData->saved_data);
        if($noteUpdateCount <= $previousUpdateCount) {
            $outData = new BTZ_FunctionOutputData();
            $outData->status_code = 0;
            $outData->status_message = "No new notes available";
            $outData->data_to_save = $noteUpdateCount;
            return $outData;
        }
        
        $outData = new BTZ_FunctionOutputData();
        $outData->status_code = 0;
        $outData->status_message = "Ok";
        $outData->data_to_save = $noteUpdateCount;
        $outData->output_data = array(
            "title" => $note->title,
            "url" => $this->evernote_url() . "/view/" . $note->guid,
            "created" => date("Y-m-d\TH:i:s", $note->updated)
        );
        return $outData;
    }
    
    
    function action_create_note($inputData) {
        if(!isset($inputData->authentication)) {
            return new BTZ_HttpErrorResult(400, "Received input data (authentication) is invalid");
        }
        
        $authData = json_decode($inputData->authentication["auth_saved_data"], true);
        if($authData == null) {
            return new BTZ_HttpErrorResult(400, "Received input data (auth_saved_data) is invalid");
        }
        
        $token = $authData["access_token"];
        if($token == null) {
            return new BTZ_HttpErrorResult(400, "Received input data does not contain access token");
        }
        
        if(!isset($inputData->form_data)) {
            return new BTZ_HttpErrorResult(400, "Received input data is invalid (missing form_data)");
        }
        
        $form_data = $inputData->form_data;
        
        foreach(array("title", "notebook", "tags", "body") as $itemName) {
            if($form_data[$itemName] == null) {
                return $this->FunctionError(200, "'$itemName' is missing in form data.");
            }
        }
        
        $notebookName = $form_data["notebook"];
        
        try {
            $client = new Evernote\Client(array('token' => $token)); 
            $noteStore = $client->getNoteStore();
            $notebooks = $noteStore->listNotebooks();
        } catch (Exception $e) {
            return $this->GetOutDataForException($e);
        }
        
        $notebook = null;
        foreach($notebooks as $inspectedNotebook) {
            if($inspectedNotebook->name == $notebookName) {
                $notebook = $inspectedNotebook;
                break;
            }
        }
        
        if($notebook == null) {
            return $this->FunctionError(100, "No notebook exists named '$notebookName'.");
        }
        
        $note = new EDAM\Types\Note();
        $note->guid = $notebook->guid;
        $note->title = $form_data["title"];
        $note->content = '<?xml version="1.0" encoding="UTF-8"?><!DOCTYPE en-note SYSTEM "http://xml.evernote.com/pub/enml2.dtd">';
        $note->content .= "<en-note>${form_data['body']}</en-note>";
        $note->notebookGuid = $notebook->guid;
        
        $tags = explode(",", str_replace(array(", ", " ,"), ",", $form_data["tags"]));
        foreach($tags as $tag) {
            $note->tagNames[] = trim($tag);
        }
          
        $result = null;
        try {
            $noteStore->createNote($token, $note);
        } catch (Exception $e) {
            return $this->GetOutDataForException($e);
        }
        
        $outData = new BTZ_FunctionOutputData();
        return $outData;
    }
    
        
    function GetOutDataForException($e) {
        //http://discussion.evernote.com/topic/5355-creating-note-in-php
        if($e instanceof EDAM\Error\EDAMSystemException) {
                if (isset(EDAM\Error\EDAMErrorCode::$__names[$e->errorCode])) {
                        return $this->FunctionError(100, EDAM\Error\EDAMErrorCode::$__names[$e->errorCode] . ": " . $e->parameter);
                } else {
                        return $this->FunctionError(100, $e->getCode() . ": " . $e->getMessage());
                }
        } else if($e instanceof EDAM\Error\EDAMUserException) {
                if ($e->errorCode == $GLOBALS['EDAM\Error\E_EDAMErrorCode']['AUTH_EXPIRED']) {
                        //resetSession();
                        //getRequestToken();
                        return $this->FunctionError(200, "Authorization expired. You must re-authorize access with Evernote.");
                } else {
                        if (isset(EDAM\Error\EDAMErrorCode::$__names[$e->errorCode])) {
                                return $this->FunctionError(100, EDAM\Error\EDAMErrorCode::$__names[$e->errorCode] . ": " . $e->parameter);
                        } else {
                                return $this->FunctionError(100, $e->getCode() . ": " . $e->getMessage());
                        }
                }
        } else if($e instanceof EDAM\Error\EDAMNotFoundException) {
                if (isset(EDAM\Error\EDAMErrorCode::$__names[$e->errorCode])) {
                        return $this->FunctionError(100, EDAM\Error\EDAMErrorCode::$__names[$e->errorCode] . ": " . $e->parameter);
                } else {
                        return $this->FunctionError(100, $e->getCode() . ": " . $e->getMessage());
                }
        } else {
                return $this->FunctionError(100, "Unexpected error: " . $e->getMessage());
        }
    }
    
    
    function FunctionError($errorCode, $errorMessage, $data_to_save = null, $debugger = null) {
        $outData = new BTZ_FunctionOutputData();
        $outData->status_code = $errorCode;
        $outData->status_message = $errorMessage;
        $outData->data_to_save = $data_to_save;
        $outData->debugger = $debugger;
        return $outData;
    }
    
//public enum NoteSortOrder
//  {
//    CREATED = 1,
//    UPDATED = 2,
//    RELEVANCE = 3,
//    UPDATE_SEQUENCE_NUMBER = 4,
//    TITLE = 5,
//  }
//}
}
?>
