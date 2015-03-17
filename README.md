php-jscrambler
==============

1. Get your API credentials at https://jscrambler.com/en/account/api_access

2. Copy the pre-defined configuration file that best suite your needs and add
   there your API credentials and files list.

3. Run the client

   Linux
   -----
   > $ ./jscrambler /your/configuration/file.json

   OR

   Windows
   -------
   > php jscrambler c:\your\configuration\file.json

   OR

   API
   ---

   > JScramblerFacade::process('config.json');


Requirements
------------
PHP 5.2.x or above (http://php.net/downloads.php)
libcurl (http://pt.php.net/manual/en/curl.requirements.php)

Configuration
-------------
Your JScrambler's project configuration is achieved through a JSON file with the following structure:
```json
{
  "filesSrc": ["index.js", "lib/**/*.js"],
  "filesDest": "dist/",
  "keys": {
    "accessKey": "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX",
    "secretKey": "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX"
  },
  "params": {
    "rename_local": "%DEFAULT%",
    "whitespace": "%DEFAULT%",
    "literal_duplicates": "%DEFAULT%"
  }
}
```

API
---
All of the API operations are wrapped in a static facade with the following properties/methods:

### Client
```php
require_once 'jscrambler.php';
$client = new JScramblerFacade::Client(array(
  "accessKey" => 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX',
  "secretKey" => 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX'
));
```

### downloadCode ($client, $projectId, $sourceId = null)
Downloads a project. It returns the content of a Zip file.
```php
JScramblerFacade::downloadCode($client, PROJECT_ID);
```

### getInfo ($client)
Gets information of all the client projects.
```php
JScramblerFacade::getInfo($client);
```

### pollProject ($client, $projectId)
Polls a project.
```php
JScramblerFacade::pollProject($client, PROJECT_ID);
```

### uploadCode ($client, $params)
Uploads a project. The generated project id can be found in the response.
```php
JScramblerFacade::uploadCode($client, array(
  "files"               => array("index.js"),
  "rename_local"        => "%DEFAULT%",
  "whitespace"          => "%DEFAULT%",
  "literal_duplicates"  => "%DEFAULT%"
));
```

### deleteCode ($client, $projectId)
Deletes a project.
```php
JScramblerFacade::deleteCode($client, PROJECT_ID);
```

### process ($configPathOrObject)
Common use case when using the client. First it uploads a project, then it polls the server to download and finally it unzips the project into a folder.
```php
JScramblerFacade::process('config.json');
```

### Options
#### filesSrc
Type: `Array`

An array of string values with paths to the source files of your project that you wish to send to the JScrambler services. It supports minimatch/glob.

#### filesDest
Type: `String`

A string value that is used to provide the destination of the JScrambler's output.


#### keys.accessKey
Type: `String`

A string value that is used to provide the JScrambler API with the access key.

#### keys.secretKey
Type: `String`

A string value that is used to sign requests to the JScrambler API.


#### host
Type: `String`

A string value that is used to provide the JScrambler's host.

#### port
Type: `Number`

A number value that is used to provide the JScrambler's port.

#### apiVersion
Type: `String`

A string value that is used to select the version of JScrambler.

#### deleteProject
Type: `Boolean`

If this is set to `true` then the project will be deleted from JScrambler after it has been downloaded.

#### params
Type: `Object`

You can find a list of all the possible parameters in [here](https://github.com/auditmark/node-jscrambler#jscrambler-options).
