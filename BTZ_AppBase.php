<?php

/**
 * Base class for creating Botize applications.
 * 
 * You should modify the getBotizeAuthMode and botizeCredentialsAreValid
 * methods appropriately if you want all your applications to share
 * the same set of Botize credentials. Othwerise no changes are
 * needed in this class, applications are implemented by creating
 * derived classes and overriding the appropriate protected methods.
 * 
 * Please read the associated documentation as it contains
 * important information not included here.
 *
 * @author Néstor Soriano
 */

require_once(dirname(__FILE__) . "/BTZ_AuxClasses.php");

abstract class BTZ_AppBase {
    
    /***** 
     * PROTECTED METHODS
     * 
     * These are intended to be overriden in derived classes as needed.
     *****/
    
    /**
     * Gets the user authentication mode for the application.
     * 
     * @return string One of: "none", "credentials" or "web".
     */
    protected function getUserAuthMode() {
        return "none";
    }
    
    
    /**
     * Gets the application title in the specified language.
     * 
     * @param string $language Language code
     * @return string Application title in the specified language
     */
    protected function getAppTitle($language) {
        return getAppIdentifier();
    }
    
    
    /**
     * Gets the Botize API version the application expectes to interact with.
     *  
     * @return int Botize API version number
     */
    protected function getSupportedApiVersion() {
        return 1;
    }
    
    
    /**
     * Returns an array with the languages supported by this application.
     * All the functions with a @language parameter will be invoked
     * with the values of this array only.
     * 
     * @return array String array with the codes of the supported languages.
     */
    protected function getSupportedLanguages() {
        return array("en");
    }
    
    
    /**
     * Gets the path for the images, relative to the directory
     * where the application class is. If null is returned,
     * images will be retrieved by using the get_image command instead.
     * 
     * @return string Path for the images, or null
     */
    protected function getImagesPath() {
        return "images";
    }
    
    
    /**
     * Gets a value indicating if a given function is currently disabled.
     * 
     * @param string $functionName Name of the function to check
     * @return boolean True if the function with the specified name
     * is currently disabled, false otherwise.
     */
    protected function isFunctionDisabled($functionName) {
        return false;
    }
    
    
    /**
     * Gets the form HTML for a given function.
     * 
     * @param string $functionName Name of the function to get the form for
     * @return string String with the HTML for the form,
     * or null if the specified function has no associated form.
     */
    protected function getFunctionForm($functionName) {
        return null;
    }
    
    
    /**
     * Gets the form texts for a given function in a given language.
     * 
     * @param string $functionName Name of the function to get the form texts for
     * @param string $language Language code to get the texts in
     * @return array Associative array where the keys are the text identifiers
     * and the values are the texts themselves
     */
    protected function getFunctionFormTexts($functionName, $language) {
        return array();
    }
    
    
    /**
     * Gets the output variables for a given trigger.
     * 
     * @param string $functionName Name of the trigger to get the output variables for
     * @return array Associative array where the keys are the variable names
     * and the values are the variable types
     */
    protected function getFunctionOutputVars($functionName) {
        return array();
    }
   
    
    /**
     * Gets the custom input variables expected/accepted by a given action.
     * 
     * @param string $functionName Name of the action to get the input variables for
     * @return array Associative array where the keys are the variable names
     * and the values are the variable types. Optional variables should have
     * a "?" at the end of the name, for example "name?".
     */
    protected function getFunctionInputVars($functionName) {
        return array();
    }
    
    
    /**
     * Gets the descriptions of the output variables for a given trigger.
     * 
     * @param string $functionName Name of the trigger to get the output variables texts for
     * @param string $language Language code to get the texts in
     * @return array Associative array where the keys are the variable names and
     * the values are the texts themselves. The keys returned by this method should match
     * the ones returned by getFunctionOutputVars.
     */
    protected function getFunctionOutputVarsTexts($functionName, $language) {
        return array();
    }
    
    
    /**
     * Gets the maximum polling interval for a given trigger.
     * Botize will never invoke process_trigger for the specified trigger
     * faster than the interval returned by this method. 
     * 
     * @param string $functionName The trigger name to get the interval for
     * @return string A positive integer number followed by "m" (minutes),
     * "h" (hours) or "d" (days). The maximum allowed value is the equivalent to
     * one year.
     */
    protected function getFunctionMaxPollInterval($functionName) {
        return "1m";
    }
    
    
    /**
     * Gets the display name of a given function in a specified language.
     * 
     * @param string $functionName Name of the function to get the name for
     * @param string $language Language code to get the name in
     * @return string Display name of the specified function
     * in the specified language
     */
    protected function getFunctionCaption($functionName, $language) {
        return $functionName;
    }
    
    
    /**
     * Gets one of the application images. This method will be invoked
     * only if getImagesPath returns null.
     * 
     * @param string $imageName Name of the image to retrieve, without extension.
     * @return mixed One of:
     * - Null, if the image name is unknown or the image is not found
     * - An instance of BTZ_HttpErrorResult, if anything else goes wrong
     * - The image contents, in PNG format
     */
    protected function getImage($imageName) {
        return null;
    }
    
    
    /**
     * Checks if the provided user credentials are valid.
     * This method will be invoked only if the user authentication mode
     * for this app is "credentials".
     * 
     * @param string $user User name provided
     * @param string $password Password provided
     * @return mixed One of:
     * - False, if credentials are not valid
     * - True, if credentials are valid and there is no additional data to save
     * - Anything else, if credentials are valid and there is additional data to save.
     * The value returned by the function will be passed as "auth_saved_data"
     * when invoking process_trigger and do_action (if an array or an object is returned,
     * it will be converted to JSON)
     */
    protected function userCredentialsAreValid($user, $password) {
        return false;
    }
    
    
    /**
     * Begins the user web authentication process.
     * This method will be invoked only if the user authentication mode
     * for this app is "web".
     * 
     * @param string $callback Botize URL where the user should be
     * redirected after the authentication process finishes.
     * @return mixed An instance of BTZ_BeginAuthenticateUserOutputData
     * or BTZ_HttpErrorResult.
     */
    protected function beginAuthenticateUser($callback) {
        return new BTZ_HttpErrorResult(500, "Not implemented");
    }
    
    
    /**
     * Finishes the user web authentication process.
     * This method will be invoked only if the user authentication mode
     * for this app is "web", and after beginAuthenticateUser has been invoked
     * and the browser has been redirected to the callback url specified.
     *  
     * @param string $service_data An associative array with
     * all the parameters passed by the authenticating service to Botize
     * in the callback url.
     * @param string $saved_temp_data Any data that was specified in the 
     * "temp_data_to_save" member of the object returned by beginAuthenticateUser.
     * @return mixed An instance of BTZ_EndAuthenticateUserOutputData or BTZ_HttpErrorResult.
     */
    protected function endAuthenticateUser($service_data, $saved_temp_data) {
        return new BTZ_HttpErrorResult(500, "Not implemented");
    }
    
    
    /**
     * Validates the data introduced by user in the configuration form.
     * 
     * @param string $functionName The name of the function whose form is being validated
     * @param array $formData An associative array where the keys are the form
     * input element names and the values are the element values.
     * @param array $trigger_output_vars Present only when the function is
     * an action (for triggers it will be null). An associative array where
     * the keys are the names of the output variables produced by the
     * trigger of the same task, and the values are the variable types.
     * @param string $language The language code to return error messages in.
     * If that language is not supported by the application, messages should be
     * returned in English.
     * @param array $authentication Present only when the user has
     * authenticated to a third-party service for the function on the
     * current task, null otherwise. Contains the "user_id" and
     * "auth_saved_data" elements.
     * @return mixed One of:
     * - null or an empty array, if all data is valid
     * - An array of strings with error messages if data is invalid
     * - An instance of BTZ_HttpErrorResult if something goes wrong
     */
    protected function validateFormData($functionName, $formData, $trigger_output_vars, $language, $authentication) {
        return null;
    }
    
    
    /**
     * Receives a request from a function configuration form via a Javascript
     * call to form_request.
     * 
     * @param string $functionName The name of the function whose form is
     * making the request.
     * @param array $formData An associative array where the keys are the form
     * input element names and the values are the element values
     * @param array $trigger_output_vars Present only when the function is
     * an action (for triggers it will be null). An associative array where
     * the keys are the names of the output variables produced by the
     * trigger of the same task, and the values are the variable types.
     * @param string $language The language code of the language that
     * Botize is using for the user interface displayed to the user that
     * is configuring the task.
     * @param array $authentication Present only when the user has
     * authenticated to a third-party service for the function on the
     * current task, null otherwise. Contains the "user_id" and
     * "auth_saved_data" elements.
     * @param string input The data passed as a parameter to the Javascript
     * form_request method that has triggered the invocation of the
     * form_request command. 
     * @return mixed One of:
     * - A string value to be used as the return value for the
     * form_request function. Null should never be returned.
     * - An instance of BTZ_HttpErrorResult if something goes wrong
     */
    protected function formRequest($functionName, $formData, $trigger_output_vars, $language, $authentication, $input) {
        return "";
    }
    
    
    /***** 
     * PUBLIC METHODS
     * 
     * These are used by the AppRequestProcessor class. 
     * 
     * getBotizeAuthMode and botizeCredentialsAreValid should be
     * modified if Botize authentication is needed and all the applications
     * will share the same set of credentials.
     * 
     *****/
    
    public final function getAppIdentifier() {
        $id = substr(get_class($this), 8);
        return $id;
    }
    
    
    public final function processCommand($commandName, $requestParameters) {
        $methodName = "cmd_$commandName";
        if(method_exists($this, $methodName)) {
            $result = $this->$methodName($requestParameters);
            return $result;
        } else {
            return new BTZ_HttpErrorResult("400", "Unknown command");
        }
    }
    
    
    /**
     * Gets the authentication mode that Botize should use when
     * communicating with this app.
     * 
     * @return string One of "none" or "basic".
     */
    public function getBotizeAuthMode() {
        return "none";
    }
    
    
    /**
     * Checks if the credentials supplied by Botize are valid.
     * This method will be invoked only if getBotizeAuthMode returns "basic".
     * 
     * @param string $user User name provided by Botize
     * @param string $password Password provided by Botize
     * @return boolean True if the credentials are valid, false otherwise.
     */
    public function botizeCredentialsAreValid($user, $password) {
        return true;
    }
    
    
      /***** 
      * COMMAND HANDLING METHODS
      * 
      * Method names must be: "cmd_(command name)"
      * Input is an associative array with the GET or POST paraters.
      * Output can be:
      * - An instance of BTZ_HttpErrorResult, if somethign goes wrong.
      * - An instance of BTZ_CommandResult, to return non-text and non-json results.
      * - An object or an array, will be serialized to JSON and returned as "text/json".
      * - Anything else will be assumed to be an string.
      *   If the string is a valid JSON structure, it will be returned as "text/json".
      *   Otherwise, it will be returned as "text/plain".
      *****/
    
    private function cmd_get_app_info($requestParameters) {
        $myFunctionCount = count($this->getFunctionMethods());
        
        $info = new stdClass();
        $info->app = $this->getAppIdentifier();
        $info->botize_api_version = $this->getSupportedApiVersion();
        $info->functions_count = $myFunctionCount;
        $info->user_auth_mode = $this->getUserAuthMode();
        
        $languages = $this->getSupportedLanguages();
        $info->texts = new stdClass();
        foreach($languages as $language) {
            $info->texts->$language = new stdClass();
            $info->texts->$language->title = $this->getAppTitle($language);
        }
        
        $imagesPath = $this->getImagesPath();
        if($imagesPath) {
            $info->images_path = $imagesPath;
        }
        
        return $info;
    }
    
    private function getFunctionMethods() {
        $myMethods = get_class_methods($this);
        $myFunctions = array_filter($myMethods, array($this, "isFunctionMethod"));
        return $myFunctions;
    }
    
    private function isFunctionMethod($methodName) {
        return strpos($methodName, "trigger_") === 0 || strpos($methodName, "action_") === 0;
    }
    
    
    private function cmd_get_function_info($requestParameters) {
        if(!array_key_exists("fn", $requestParameters)) {
            return new BTZ_HttpErrorResult(400, "'fn' parameter is missing");
        }
        
        $fn = intval($requestParameters["fn"]);
        $functionMethods = array_values($this->getFunctionMethods()); 
        if($fn <=0 || $fn > count($functionMethods)) {
            return new BTZ_HttpErrorResult(400, "'fn' parameter is invalid");
        }
        
        $methodName = $functionMethods[$fn - 1];
        $underscoreIndex = strpos($methodName, "_");
        
        $info = new stdClass();
        $info->app = $this->getAppIdentifier();
        $info->type = substr($methodName, 0, $underscoreIndex);
        $info->id = substr($methodName, $underscoreIndex+1);
        $info->disabled = $this->isFunctionDisabled($info->id);
        
        $form = $this->getFunctionForm($info->id);
        if($info->type === "trigger") {
            $input_vars = null;
            $output_vars = $this->getFunctionOutputVars($info->id);
        } else {
            $input_vars = $this->getFunctionInputVars($info->id);
            $output_vars = null;
        }
        
        if($form) {
            $info->form = $form;
        }
        $languages = $this->getSupportedLanguages();
        $info->texts = new stdClass();
        foreach($languages as $language) {
            $info->texts->$language = new stdClass();
            $info->texts->$language->caption = $this->getFunctionCaption($info->id, $language);
            if($form) {
                $info->texts->$language->form = new stdClass();
                $formTexts = $this->getFunctionFormTexts($info->id, $language);
                foreach($formTexts as $key=>$value) {
                    $info->texts->$language->form->$key = $value;
                }
            }
            if(is_array($output_vars)) {
                $info->texts->$language->output_vars = new stdClass();
                $varsTexts = $this->getFunctionOutputVarsTexts($info->id, $language);
                foreach($varsTexts as $key=>$value) {
                    $info->texts->$language->output_vars->$key = $value;
                }
            }
        }
        
        if(is_array($output_vars)) {
            $info->trigger_data = new stdClass();
            $info->trigger_data->output_vars = new stdClass();
            $info->trigger_data->max_poll_interval = $this->getFunctionMaxPollInterval($info->id);
            foreach($output_vars as $key=>$value) {
                $info->trigger_data->output_vars->$key = $value;
            }
        }
        if(is_array($input_vars)) {
            $info->action_data = new stdClass();
            $info->action_data->input_vars = new stdClass();
            foreach($input_vars as $key=>$value) {
                $info->action_data->input_vars->$key = $value;
            }
        }
        
        return $info;
     }
    
    
     private function cmd_get_image($requestParameters) {
         if(!array_key_exists("img", $requestParameters)) {
            return new BTZ_HttpErrorResult(400, "'img' parameter is missing");
         }
         
         $imageData = $this->getImage($requestParameters["img"]);
         if($imageData instanceof BTZ_HttpErrorResult) {
             return $imageData;
         } else if($imageData == null) {
             return new BTZ_HttpErrorResult(404, "Not found");
         } else {
             return new BTZ_CommandResult("image/png", $imageData);
         }
     }
     
     
     private function cmd_process_trigger($requestParameters) {
         return $this->invokeFunction("trigger", $requestParameters);
     }
     
     private function cmd_do_action($requestParameters) {
         return $this->invokeFunction("action", $requestParameters);
     }
     
     private function invokeFunction($functionType, $requestParameters) {
         if(!array_key_exists("id", $requestParameters)) {
            return new BTZ_HttpErrorResult(400, "'id' parameter is missing");
         }
         if(!array_key_exists("data", $requestParameters)) {
            return new BTZ_HttpErrorResult(400, "'data' parameter is missing");
         }
         
         $functionName = $requestParameters["id"];
         $methodName = "{$functionType}_{$functionName}";
         if(!method_exists($this, $methodName)) {
             return new BTZ_HttpErrorResult(400, "Unknown function '$functionName'");
         }
         
         $data = json_decode($requestParameters["data"], true);
         
         if(!$data) {
             return new BTZ_HttpErrorResult(400, "'data' has no valid json data");
         }
         
         if($this->getUserAuthMode() != "none" && !array_key_exists("authentication", $data)) {
             return new BTZ_HttpErrorResult(401, "User authentication data not provided");
         }
         
         if($functionType === "action" && !array_key_exists("input_data", $data)) {
             return new BTZ_HttpErrorResult(400, "'input_data' is missing in 'data'");
         }
         
         $inputData = new BTZ_FunctionInputData();
         foreach($data as $key=>$value) {
             if(property_exists($inputData, $key)) {
                 $inputData->$key = $value;
             }
         }
         
         $outputData = $this->$methodName($inputData);
         if($outputData instanceof BTZ_HttpErrorResult) {
             return $outputData;
         } else if(!($outputData instanceof BTZ_FunctionOutputData)) {
             return new BTZ_HttpErrorResult(500, "'$methodName' returned invalid result type");
         }
         
         if($outputData->data_to_save) {
             $outputData->data_to_save = $this->jsonIfArrayOrObject($outputData->data_to_save);
         }
         
         return $outputData;
     }
     
     
     private function cmd_authenticate_user($requestParameters) {
         if($this->getUserAuthMode() != "credentials") {
             return new BTZ_HttpErrorResult(400, "This app does not support the credentials authentication mode");
         }
         
         if(!array_key_exists("data", $requestParameters)) {
            return new BTZ_HttpErrorResult(400, "'data' parameter is missing");
         }
         
         $data = json_decode($requestParameters["data"]);
         if(!$data) {
             return new BTZ_HttpErrorResult(400, "'data' has no valid json data");
         }
         
         if(!$data->user_id || !$data->password) {
             return new BTZ_HttpErrorResult(400, "User id or password missing in 'data'");
         }
         
         $authResult = $this->userCredentialsAreValid($data->user_id, $data->password);
         if(gettype($authResult) == null) {
             return new BTZ_HttpErrorResult(500, "userCredentialsAreValid returned null");
         }
         
         $output = new stdClass();
         $output->valid_credentials = (!is_bool($authResult) || $authResult == true);
         if($output->valid_credentials) {
             $output->user_id = $data->user_id;
             if(!is_bool($authResult)) {
                 $output->auth_data_to_save = $this->jsonIfArrayOrObject($authResult);
             }
         }
         
         return $output;
     } 
     
     
     private function cmd_begin_authenticate_user($requestParameters) {
         if($this->getUserAuthMode() != "web") {
             return new BTZ_HttpErrorResult(400, "This app does not support the web authentication mode");
         }
         
         if(!array_key_exists("data", $requestParameters)) {
            return new BTZ_HttpErrorResult(400, "'data' parameter is missing");
         }
         
         $data = json_decode($requestParameters["data"]);
         if(!$data) {
             return new BTZ_HttpErrorResult(400, "'data' has no valid json data");
         }
         
         if(!$data->callback) {
             return new BTZ_HttpErrorResult(400, "callback missing in 'data'");
         }
         
         $result = $this->beginAuthenticateUser($data->callback);
         if($result instanceof BTZ_HttpErrorResult) {
             return $result;
         } else if(!($result instanceof BTZ_BeginAuthenticateUserOutputData)) {
             return new BTZ_HttpErrorResult(500, "'beginAuthenticateUser' returned invalid result type");
         }
         
         if($result->temp_data_to_save) {
             $result->temp_data_to_save = $this->jsonIfArrayOrObject($result->temp_data_to_save);
         }
         
         return $result;
     }
     
     
     private function cmd_end_authenticate_user($requestParameters) {
         if($this->getUserAuthMode() != "web") {
             return new BTZ_HttpErrorResult(400, "This app does not support the web authentication mode");
         }
         
         if(!array_key_exists("data", $requestParameters)) {
            return new BTZ_HttpErrorResult(400, "'data' parameter is missing");
         }
         
         $data = json_decode($requestParameters["data"]);
         if(!$data) {
             return new BTZ_HttpErrorResult(400, "'data' has no valid json data");
         }
         
         if(isset($data->service_data)) {
             $service_data = $data->service_data;
         } else {
             $service_data = array();
         }
         
         if(isset($data->saved_temp_data)) {
             $saved_temp_data = $data->saved_temp_data;
         } else {
             $saved_temp_data = null;
         }
         
         $result = $this->endAuthenticateUser((array)$data->service_data, $saved_temp_data);
         if($result instanceof BTZ_HttpErrorResult) {
             return $result;
         } else if(!($result instanceof BTZ_EndAuthenticateUserOutputData)) {
             return new BTZ_HttpErrorResult(500, "'beginAuthenticateUser' returned invalid result type");
         }
         
         $result->valid_credentials = (bool)$result->valid_credentials;
         if($result->valid_credentials) {
             if(!$result->user_id) {
                return new BTZ_HttpErrorResult(500, "'beginAuthenticateUser' returned empty user id");
             } else if(is_array($result->user_id) || is_object($result->user_id)) {
                return new BTZ_HttpErrorResult(500, "'beginAuthenticateUser' returned array or object as user id");
             }
         }
         if($result->auth_data_to_save) {
             $result->auth_data_to_save = $this->jsonIfArrayOrObject($result->auth_data_to_save);
         }
         
         return $result;
     }
     
     
     private function cmd_validate_form_data($requestParameters) {
         return $this->form_validate_or_request($requestParameters, false);
     }
     
     private function cmd_form_request($requestParameters) {
         return $this->form_validate_or_request($requestParameters, true);
     }
     
     private function form_validate_or_request($requestParameters, $isRequest) {
         if(!array_key_exists("id", $requestParameters)) {
            return new BTZ_HttpErrorResult(400, "'id' parameter is missing");
         }
         if(!array_key_exists("data", $requestParameters)) {
            return new BTZ_HttpErrorResult(400, "'data' parameter is missing");
         }
         
         $data = json_decode($requestParameters["data"]);
         if(!$data) {
             return new BTZ_HttpErrorResult(400, "'data' has no valid json data");
         }
         
         if(!property_exists($data, "language")) {
             return new BTZ_HttpErrorResult(400, "language missing in data");
         }
         
         if(!property_exists($data, "form_data")) {
             return new BTZ_HttpErrorResult(400, "form_data missing in data");
         }
         
         if(!is_object($data->form_data)) {
             return new BTZ_HttpErrorResult(400, "form_data is a scalar value, must be an array");
         }
         
         if(property_exists($data, 'trigger_output_vars') && !is_object($data->trigger_output_vars)) {
             return new BTZ_HttpErrorResult(400, "trigger_output_vars is a scalar value, must be an array");
         }
         
         if(property_exists($data, 'authentication') && !is_object($data->authentication)) {
             return new BTZ_HttpErrorResult(400, "authentication is a scalar value, must be an array");
         }
         
         if($isRequest && !property_exists($data, 'input')) {
             return new BTZ_HttpErrorResult(400, "input missing in data");
         }
         
         $trigger_output_vars =
             property_exists($data, 'trigger_output_vars') ?
             (array)$data->trigger_output_vars :
             null;
         
         $authentication =
             property_exists($data, 'authentication') ?
             (array)$data->authentication :
             null;
         
         $result =
                 $isRequest ?
                 $this->formRequest($requestParameters["id"], (array)$data->form_data, $trigger_output_vars, $data->language, $authentication, $data->input) :
                 $this->validateFormData($requestParameters["id"], (array)$data->form_data, $trigger_output_vars, $data->language, $authentication);
         if($result instanceof BTZ_HttpErrorResult) {
             return $result;
         } else if(isset($result) && !$isRequest && !is_array($result)) {
             return new BTZ_HttpErrorResult(500, "validateFormData did not return an array");
         } else if(isset($result) && $isRequest && (is_array($result) || is_object($result))) {
             return new BTZ_HttpErrorResult(500, "validateFormData did not return a scalar value");
         }
         
         $output = new stdClass();
         if($isRequest) {
            $output->output = $result;
         } else {
            $output->valid_data = !isset($result) || count($result) == 0;
            if(!$output->valid_data) {
                $output->error_essages = array_values($result);
            }
         }
         
         return $output;
     }
     
     
     private function jsonIfArrayOrObject($data) {
         return is_object($data) || is_array($data) ?
                json_encode($data) : $data;
     }
     
     /*****
      * FUNCTION METHODS
      * 
      * Function methods must be implemented in derived classes as follows:
      * 
      * - Method is protected.
      * - Method name is trigger_(name) or action_(name).
      * - Method has one input parameter of type BTZ_FunctionInputData.
      * - Method must return an instance of BTZ_HttpErrorResult or BTZ_FunctionOutputData.
      */
     
     /* Example:
     protected function trigger_the_trigger_name($inputParameters) {
         if($this->ParametersAreValid($inputParameters)) {
            $outData = new BTZ_FunctionOutputData();
            $outData->data_to_save = "Data to save";
            $outData->output_data = array(
                "var1" => "value1",
                "var2" => 1234
            );
            return $outData;
         } else {
             return new BTZ_HttpErrorResult(400, "Invalid input data");
         }
    }
    */
}

?>
