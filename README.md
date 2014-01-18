## Botize application infraestructure for PHP v1.0 ##


Author: Nestor Soriano, konamiman@konamiman.com


#### **1\.** WHAT IS THIS?

This infraestructure allows you to easily create applications for Botize (www.botize.com) using PHP. The supplied classes contain code that shields you from most of the boilerplate processing and error checking needed, so that you can focus on implementing the functionality of your applications.

The infraestructure is designed to allow you to host several Botize applications controlled by one single entry point.

Please read the Botize API documentation (www.botize.com/api) for more details on how Botize applications work.


#### **2\.** THE SETUP

To start using the infrastructure you should perform the following steps:

a. Create a folder on your server where you will place all the infrastructure code and your applications.

b. Copy the *BTZ\_AppBase.php*, *BTZ\_AppRequestProcessor.php* and *BTZ\_AuxClasses.php* into the folder you have just created.

c. In the same folder, create a folder named *apps*.

d. Create a new PHP file in the same folder or anywhere else in your server, whose public URL will be the entry point that you supply to Botize when you register your application(s). The code for this file consists of simply these lines:  

    <?php
    require_once("BTZ_AppRequestProcessor.php");
    $proc = new BTZ_AppRequestProcessor();
    $proc->processCurrentHttpRequest();
    ?>

If the entry point PHP file is not in the same directory as the BTZ\_AppRequestProcessor.php file, you will need to adjust the path supplied to the require\_once directive.


#### **3\.** ADDING APPLICATIONS

For each Botize application that you want to create, follow these steps:

a. Decide a unique alphanumeric identifier for your application, for example "someCoolApp". See the Botize API documentation for details on the allowed characters and lengths for the application identifiers.

b. Inside the *app* folder you created during the setup, create a folder whose name is equal to the application identifier.

c. Inside the folder created in the previous step, create a file with a class. The class name must be "BTZ\_App\_" followed by the application identifier (for example: "BTZ\_App\_someCoolApp") and the file name must be the class name followed by ".php".

d. Include the following line at the beginning of the class file you have just created:

    require_once(dirname(dirname(dirname(__FILE__))) . "/BTZ_AppBase.php");

e. Modify your class so that it extends the *BTZ_AppBase* class:

    class BTZ_App_someCoolApp extends BTZ_AppBase {
        ...
    }

f. Override the protected methods of the *BTZ\_AppBase* class as needed. Depending on the complexity of your application you will need to override more or less of these methods, see the *BTZ\_AppBase* class source code for details.

g. Create one method for each function (trigger/action) that you want to be exposed by your application. These methods should be as follows:

- The method visibility is "protected".
- The method name is *trigger\_(trigger id)* or *action\_(action id)*. See the Botize API documentation for details on the allowed characters and lengths for the function identifiers. 
- The method has one input parameter of type *BTZ\_FunctionInputData*.
- The method must return an instance of *BTZ\_FunctionOutputData*, or if something goes wrong, an instance of *BTZ\_HttpErrorResult*.
- The method MUST NOT throw errors of eceptions. All unexpected errors or exceptions should be caught and an instance of *BTZ\_HttpErrorResult* with a code of 500 should be returned.

h. If your application class will supply the images via the get\_image command, override the *getImagesPath* method so that it returns null, and implement the getImage method appropriately. See the Botize API documentation for a list of the required images.

i. If your application class will NOT supply the images via the get\_image command, add a folder named as the value returned by the getImagesPath method of your class (the default name is "images") to the folder where the application class file is placed, and place the image files in this folder. See the Botie API documentation for a list of the required images.

j. If needed, add more files to the folder where the application class file is placed (such as additional code files, data files, or anything else that your application may need).

Enjoy!
