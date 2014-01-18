<?php

/**
 * HTTP request processor for Botize applications
 *
 * This class is the entry point for all the application commands executions.
 * It will search the appropriate application class based on the
 * "app" parameter of the request, and execute the appropriate command
 * based on the "cmd" parameter. Then it will convert the value returned
 * by the command into the appropriate content-type + content, or
 * into the appropriate HTTP status code
 * 
 * @author Néstor Soriano 
 */

require_once("BTZ_AuxClasses.php");

class BTZ_AppRequestProcessor {
    private $appsFolder;
    
    public function __construct($classPath = 'apps') {
        if(substr($classPath, 0, 1) === DIRECTORY_SEPARATOR) {
            $this->appsFolder = $classPath . DIRECTORY_SEPARATOR;
        } else {
            $this->appsFolder = dirname(__FILE__) . DIRECTORY_SEPARATOR .  $classPath . DIRECTORY_SEPARATOR;
        }
        
        ini_set("include_path", ini_get("include_path") . PATH_SEPARATOR . $this->appsFolder);
    }
    
    
    public function processCurrentHttpRequest() {
        $verb = $_SERVER['REQUEST_METHOD'];
        
        if($verb != 'GET' && $verb != 'POST') {
            $this->setHttpErrorCode(400, 'Invalid HTTP verb');
            return;
        }
        
        $requestParameters = ($verb == 'GET' ? $_GET : $_POST);
        
        /* For debugging...
        header("Content-Type: text/json");
        echo json_encode($requestParameters);
        return;
        */
        
        $mandatoryParameters = array('cmd', 'app');
        foreach($mandatoryParameters as $parameterName) {        
            if(!isset($requestParameters[$parameterName])) {
                $this->setHttpErrorCode(400, "'$parameterName' parameter is missing");
                return;
            }
        }
        
        $appName = $requestParameters['app'];
        $className = "BTZ_App_$appName";
        $classFilePath = $this->appsFolder . $appName . DIRECTORY_SEPARATOR . $className . ".php";
        if(!file_exists($classFilePath)) {
            $this->setHttpErrorCode(400, "Unknown application");
            return;
        }
        require_once($classFilePath);
        if(!class_exists($className)) {
            $this->setHttpErrorCode(400, "Unknown application");
            return;
        }
        
        $command = strtolower($requestParameters['cmd']);
        if(substr($command, 0, 4) === "get_" xor $verb === 'GET') {
            $this->setHttpErrorCode(400, "Invalid HTTP verb for this command");
            return;
        }
        
        $appHandler = new $className();
        $botizeAuthMode = $appHandler->getBotizeAuthMode();
        if($botizeAuthMode == "basic") {
             if (!isset($_SERVER['PHP_AUTH_USER'])) {
                 $this->setHttpErrorCode(401, "Unauthorized");
                 header("WWW-Authenticate: Basic realm=\"{$appHandler->getAppIdentifier()}\"");
                 return;
             }
             if(!$appHandler->botizeCredentialsAreValid($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {
                 $this->setHttpErrorCode(401, "Unauthorized");
                 return;
             }
        } else if($botizeAuthMode == "none") {
            //No authentication required
        } else {
            $this->setHttpErrorCode(500, "Unknown authentication mode");
            return;
        }
        $result = $appHandler->processCommand($command, $requestParameters);
        
        if($result instanceof BTZ_HttpErrorResult) {
            $this->setHttpErrorCode($result->code, $result->message);
            return;
        } else if($result instanceof BTZ_CommandResult) {
            //No further processing needed
        } else if(is_array($result) || is_object($result)) {
            $jsonResult = json_encode($result);
            $result = new BTZ_CommandResult("text/json", $jsonResult);
        } else {
            $maybeJson = json_decode($result);
            $result = new BTZ_CommandResult($maybeJson ? "text/json" : "text/plain", $result);
        }
        
        header("Content-Type: {$result->contentType}");
        
        echo $result->content;
    }
    
    private function setHttpErrorCode($code, $message) {
        /* For debugging...
        $x = array("output" => "$code $message");
        header("Content-Type: text/json");
        echo json_encode($x);
        */
        
        header("{$_SERVER['SERVER_PROTOCOL']} $code $message");
    }
}

?>
