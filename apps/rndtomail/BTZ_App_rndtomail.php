<?php

/**
 * RandomToMail - send random sentences to an email address
 *
 * This is a simple proof of concept of a Botize application:
 * - There is one trigger, random_sentence, that returns one sentence
 *   from a user-configured list.
 * - There is one action, email_sender, that sends an email with the
 *   from, to, subject and body configured by the user.
 * - Botize authentication is required. User is "theBotize!",
 *   password is "thePassword!". 
 * - Simple user authentication is required. User is "theUser!",
 *   password is "thePassword!".
 * 
 * By combining the trigger and the action, random sentences will be
 * sent periodically to the configured email address.
 * 
 * @author Néstor Soriano
 */

require_once(dirname(dirname(dirname(__FILE__))) . "/BTZ_AppBase.php");

class BTZ_App_rndtomail extends BTZ_AppBase {
    
    /*** Protected methods ***/
    
    protected function getUserAuthMode() {
        return "none"; //credentials
    }
   
    
    protected function getAppTitle($language) {
        return "Random To Mail";
    }
    
    
    protected function getSupportedLanguages() {
        return array("en", "es");
    }
    
    
    protected function getFunctionForm($functionName) {
        if($functionName === "random_sentence") {
            $form = <<< END
{{type_your_sentences_here}}<br/>
<textarea name="sentences" cols="40" rows="10"></textarea>
END;
            return $form;
        } else if($functionName === "email_sender") {
            $form = <<< END
<script type="text/javascript">
    function buttonClick() {
        $(document.body).toggleClass("wait");
        var result = form_request('');
        if(result == null) {
            result = 'Communication with server failed!';
        }
        $(document.body).toggleClass("wait");
        alert(result);
    }
</script>
<style type="text/css">
    body.wait, body.wait *{
        cursor: wait;   
    }
</style>
{{txt_from}}:<br/><input type="text" name="from"/><br/>
{{txt_to}}:<br/><input type="text" name="to"/><br/>
{{txt_subject}}:<br/><input type="text" name="subject"/><br/>
{{txt_body}}:<br/><textarea name="body" cols="40" rows="10"></textarea><br/>
<input type="button" onclick="buttonClick(); return false;" value="{{send_test_email}}"/>
END;
            return $form;
        } else {
            return null;
        }
    }
    

    protected function getFunctionFormTexts($functionName, $language) {
        $texts = array();
        
        $texts["en"] = array(
            "random_sentence" => array (
                "type_your_sentences_here" => "Type your sentences here. One sentence per line."
            ),
            "email_sender" => array(
                "txt_from" => "From address",
                "txt_to" => "To address",
                "txt_subject" => "Subject template",
                "txt_body" => "Body template",
                "send_test_email" => "Send test email"
            )
        );
        
        $texts["es"] = array(
            "random_sentence" => array (
                "type_your_sentences_here" => "Escriba aquí las frases. Una frase por línea."
            ),
            "email_sender" => array(
                "txt_from" => "Remitente",
                "txt_to" => "Destinatario",
                "txt_subject" => "Plantilla del título",
                "txt_body" => "Plantilla del mensaje",
                "send_test_email" => "Enviar mensaje de prueba"
            )
        );
        
        return $texts[$language][$functionName];
    }
    
    
    protected function getFunctionOutputVars($functionName) {
        if($functionName === "random_sentence") {
            return array(
                "sentence" => "text"
            );
        } else {
            return array();
        }
    }
    

    protected function getFunctionOutputVarsTexts($functionName, $language) {
        if($functionName === "random_sentence") {
            $texts = array();
            
            $texts["en"] = array(
                "sentence" => "Sentence randomly chosen from the user provided list"
            );
            
            $texts["es"]  = array(
                "sentence" => "Frase elegida al azar de la lista proporcionada por el usuario"
            );
            
            return $texts[$language];
        } else {
            return array();
        }
    }
    
    
    protected function getFunctionMaxPollInterval($functionName) {
        return "30m";
    }
    
    
    protected function getFunctionCaption($functionName, $language) {
        $texts = array();
        
        $texts["en"] = array(
            "random_sentence" => "Sentences list configurator",
            "email_sender" => "Email sender"
        );
        
        $texts["es"] = array(
            "random_sentence" => "Configurador de la lista de frases",
            "email_sender" => "Enviador de emails"
        );
        
        return $texts[$language][$functionName];
    }
    
    
    public function getBotizeAuthMode() {
        return "none"; //basic
    }
    
    
    public function botizeCredentialsAreValid($user, $password) {
        return $user === "theBotize!" && $password === "thePassword!";
    }    
    

    protected function userCredentialsAreValid($user, $password) {
        return $user === "theUser!" && $password === "thePassword!";
    }
    
    
    protected function formRequest($functionName, $formData, $trigger_output_vars, $language, $authentication, $input) {
        if($functionName != "email_sender") {
            return "ERROR: Bad function!";
        }
        
        $fields = array("from", "to", "subject", "body");
        foreach($fields as $field) {
            if(!$formData[$field]) {
                if($language == "es") {
                    return "Todos los elementos del formulario deben estar establecidos para poder enviar un mensaje de prueba.";
                } else {
                    return "The form must be completely filled before sending a test email.";
                }
            }
        }
        
        if(strpos($formData["from"], "{{") !== false || strpos($formData["to"], "{{") !== false) {
            if($language == "es") {
                return "No es posible enviar un mensaje de prueba si 'from' o 'to' contienen marcadores de sustitución de datos.";
            } else {
                return "Can't send test email if 'from' or 'to' contains data substitution markers.";
            }
        }
        
        $sendOk = mail(
                $formData["to"],
                "(TEST) " . $formData["subject"],
                $formData["body"],
                "From: {$formData["from"]}"
                );
        if($sendOk) {
            if($language == "es") {
                return "¡Mensaje de prueba enviado correctamente!";
            } else {
                return "Test message sent correctly!";
            }
        } else {
            if($language == "es") {
                return "El envío del mensaje de prueba ha fallado.";
            } else {
                return "Failed to send test message.";
            }
        }
    }
        
    protected function validateFormData($functionName, $formData, $trigger_output_vars, $language, $authentication) {
        if($language != "es") {
            $language = "en";
        }
        
        $results["en"] = array(
            "random_sentence" => array(
                "Please specify at least one sentence"
            ), 
            "email_sender" => array(
                "All fields are mandatory"
            )
        );
        
        $results["es"] = array(
            "random_sentence" => array(
                "Introduzca al menos una frase"
            ), 
            "email_sender" => array(
                "Todos los campos son obligatorios"
            )
        );
    
        
        if($functionName === "random_sentence") {
            $fields = array("sentences");
        } else if($functionName === "email_sender") {
            $fields = array("from", "to", "subject", "body");
        } else {
            return new BTZ_HttpErrorResult(400, "Unknown function '$functionName'");
        }
        
        foreach($fields as $field) {
            if(!$formData[$field]) {
                return $results[$language][$functionName];
            }
        }
        
        return null;
    }
    
    
    /*** Functions ***/
    
    function trigger_random_sentence($inputData) {
        if(!$inputData->form_data) {
            return new BTZ_HttpErrorMessage(400, "form_data is missing");
        }
        
        if(!$inputData->form_data["sentences"]) {
            //Sanity check only.
            //Should never happen since the form validation prevents
            //submitting an empty sentences list.
            $result = new BTZ_FunctionOutputData();
            $result->status_code = 200;
            $result->status_message = "No sentences configured";
            return $result;
        }
        
        $sentences = explode("\r\n", $inputData->form_data["sentences"]);
        $sentences = array_filter($sentences, array($this, "non_empty_string"));
        
        //Do not repeat the same sentence returned in the previous trigger execution
        $previousIndex = $inputData->form_data["saved_data"] ? intval($inputData->form_data["saved_data"]) : -1;
        do {
            $index = rand(0, count($sentences)-1);
        } while(count($sentences) > 1 && $index == $previousIndex);
        
        $sentence = trim($sentences[$index]);
        
        $result = new BTZ_FunctionOutputData();
        $result->output_data = array("sentence" => $sentence);
        $result->data_to_save = $index;
        return $result;
    }
    
    function non_empty_string($string) {
        return isset($string) && trim($string) != "";
    }
    
    
    function action_email_sender($inputData) {
        if(!$inputData->form_data) {
            return new BTZ_HttpErrorMessage(400, "form_data is missing");
        }
        
        //Sanity check only.
        //Should never happen since the form validation prevents
        //submitting the form with an empty field.
        $fields = array("from", "to", "subject", "body"); 
        foreach($fields as $field) {
            if(!$inputData->form_data[$field]) {
                 $result = new BTZ_FunctionOutputData();
                 $result->status_code = 201;
                 $result->status_message = "'$field' data is missing";
                 return $result;
            }
        }
        
        $result = new BTZ_FunctionOutputData();
        $sendOk = mail(
                $inputData->form_data["to"],
                $inputData->form_data["subject"],
                $inputData->form_data["body"],
                "From: {$inputData->form_data["from"]}"
                );
        if(!$sendOk) {
            $result->status_code = 100;
            $result->status_message = "Mail send failed";
        }
        
        return $result;
    }
}

?>
