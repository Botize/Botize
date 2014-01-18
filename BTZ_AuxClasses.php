<?php

final class BTZ_HttpErrorResult {
    public $code;
    public $message;
    
    function __construct($code, $message) {
        $this->code = $code;
        $this->message = $message;
    }
}


final class BTZ_CommandResult {
    public $contentType;
    public $content;
    
    function __construct($contentType, $content) {
        $this->contentType = $contentType;
        $this->content = $content;
    }
}


final class BTZ_FunctionInputData {
    public $input_data;
    public $form_data;
    public $saved_data;
    public $authentication;
}


final class BTZ_FunctionOutputData {
    public $status_code = "0";
    public $status_message = "Ok";
    public $output_data;
    public $data_to_save;
    public $debugger;
}


final class BTZ_BeginAuthenticateUserOutputData {
    public $authentication_url;
    public $temp_data_to_save;
    
    function __construct($authentication_url, $temp_data_to_save) {
        $this->authentication_url = $authentication_url;
        $this->temp_data_to_save = $temp_data_to_save;
    }
}

final class BTZ_EndAuthenticateUserOutputData {
    public $valid_credentials;
    public $user_id;
    public $auth_data_to_save;
    
    function __construct($valid_credentials, $user_id, $auth_data_to_save) {
        $this->valid_credentials = $valid_credentials;
        $this->user_id = $user_id;
        $this->auth_data_to_save = $auth_data_to_save;
    }
}

?>
