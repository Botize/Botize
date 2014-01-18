## Sample application: Evernotize ##

Evernotize (application id: evernotize) is an example of an application that interacts with a third party service (Evernote) and uses web based user authentication. 

- There is one trigger that checks if a new note is available in a shared notebook. For simplicity, simply the first shared notebook of the user is checked. The *data\_to\_save/saved\_data* mechanism is user to keep track of the last produced note data.
- There is one action that allows to create a new note.
- There is one action that allows sending one email message.
- Botize authentication is required. User is "theBotize!", password is "thePassword!". 
- Web based user authentication is required. Authentication is performed by Evernote using OAuth.
- The action has a function configuration form.

Note that before using the application, the OAuth consumer key and secret must be appropriately set in the code. Also, there is a constant that allows using the Evernote sandbox environment instead of live data.

See the Botize API documentation and the source code of the application itself for more details.
