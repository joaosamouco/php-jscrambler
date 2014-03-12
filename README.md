Quickstart
==========

1. Get your API credentials at https://jscrambler.com/en/account/api_access

2. Copy the pre-defined configuration file that best suite your needs and add
   there your API credentials and files list.

3. Run the client

   Linux
   --------
   $ ./jscrambler /destination/directory/ /your/configuration/file.json
    
   OR
 
   Windows
   --------
   > jscrambler.bat c:\destination\directory c:\your\configuration\file.json


Content
=======

jscrambler
    GNU/Linux command line JScrambler php client bootstrap

    $ jscrambler /tmp/ ./configs/config.json

jscrambler.bat 
    Windows command line JScrambler php client bootstrap

    > jscrambler.bat c:\temp\ configs\config.json

jscrambler.php
    JScrambler php client

configs/
    Pre-defined configuration template files, ready for JScrambler php client

    config.json
	ready for optimization: Rename local, Whitespace removal, Duplicated
	literals elimination and Dictionary compression.

    minifiction.json
	ready for minification: Rename local, Whitespace removal and Duplicated
	literals elimination

    obfuscation.json
	ready for obfuscation: String splitting, Function reordering, Function
	outlining, Dot notation elimination, Expiration date, Rename local,
	 Whitespace removal and Duplicated literals elimination

    optimization.json
	the same than config.json

    self-defending.json
	self-defending enabled configuration file: String splitting, Function
	reordering, Function outlining, Dot notation elimination, Expiration
	date, Rename local, Whitespace removal, Duplicated literals elimination
	and Self-defending

includes/
    jscrambler.php
	JScrambler php client library

Requirements
============

PHP 5.2.x or above (http://php.net/downloads.php)
libcurl (http://pt.php.net/manual/en/curl.requirements.php)


API Resources
=============

The Web API offers all the necessary functionality to request for JavaScript project 
obfuscation, delete projects, download obfuscated projects and get the information 
necessary to manage all your obfuscated project versions.

POST /code.json
    Upload multiple JavaScript and HTML sources for obfuscation

GET /code.json
    Get information about your submitted projects

GET /code/project_id.json
    Get information about the project and its sources with the specific project_id

GET /code/project_id/source_id.json
    Get information about the project source with the specific source_id and project_id


GET /code/project_id.zip
    Download the zip archive containing the resulting project with the specific project_id

GET /code/project_id/source_id.extension
    Download a project source with the specific source_id and extension belonging to the 
    project with the specific project_id

DELETE /code/project_id.zip
    Delete a project with the specific project_id


Transformation Parameters
=========================

asserts_elimination
-------------------

Removes function definitions and function calls with a given name.

Value

name1;name2;... - assert function names


constant_folding
----------------

Simplifies constant expressions at compile-time to make your code faster at run-time.

Value

%DEFAULT% - default behavior


dead_code
---------

Randomly injects dead code into the source code.

Value

%DEFAULT% - default behavior


dead_code_elimination
---------------------

Removes dead code and void code from your JavaScript.

Value

%DEFAULT% - default behavior


debugging_code_elimination
--------------------------

Removes statements and public variable declarations used to control the output of debugging 
messages that help you debug your code.

Value

name1;name2;... - debugging code names


dictionary_compression
----------------------

Dictionary compression to shrink even more your source code.

Value

%DEFAULT% - default behavior


domain_lock
-----------

Locks your project to a list of domains you specify.

Value

domain1;domain2;... - your domains


dot_notation_elimination
------------------------

Transforms dot notation to subscript notation.

Value

%DEFAULT% - default behavior


exceptions_list
---------------

There are some names that should never be replaced or reused to create new declarations e.g. 
document, toUpperCase. Public declarations existing in more than one source file should not be 
replaced if you submit only a part of the project where they appear. Therefore a list of 
irreplaceable names and the logic to make distinction between public and local names already 
exists on JScrambler to avoid touching those names. Use this parameter to add your own exceptions.

Value

name;name1;name2;... - list of exceptions that will never be replaced or used to create new declarations

expiration_date

Sets your JavaScript to expire after a date of your choosing.

Value

date - date format YYYY/MM/DD


function_outlining
------------------

Turns statements into new function declarations

Value

%DEFAULT% - default behavior


function_reorder
----------------

Randomly reorders your source code's function declarations.

Value

%DEFAULT% - default behavior


literal_hooking
---------------

Replaces literals by a randomly sized chain of ternary operators. You may configure the minimum 
and maximum number of predicates per literal, as the occurrence probability of the transformation. 
This allows you to control how big the obfuscated JavaScript grows and the potency of the transformation.

Value

%DEFAULT% - default behavior

min;max[;percentage]


literal_duplicates
------------------

Replaces literal duplicates by a symbol

Value

%DEFAULT% - default behavior


member_enumeration
------------------

Replaces Browser and HTML DOM objects by a member enumeration.

Value

%DEFAULT% - default behavior


mode
----

Value

starter - Standard protection and optimization behavior. Enough for most JavaScript applications

mobile - Transformations are applied having into account the limitations and needs of mobile devices

html5 - Protects your HTML5 and Web Gaming applications by targeting the new HTML5 features


name_prefix
-----------

Set a prefix to be appended to the new names generated by JScrambler.

Value

prefix


rename_all
----------

Renames all identifiers found at your source code. By default, there is a list of JavaScript and 
HTML DOM names that will not be replaced. If you need to add additional exceptions use the exceptions_list parameter.

Value

%DEFAULT% - default behavior


rename_local
------------

Renames local names only. The best way to replace names without worrying about name dependencies.

Value

%DEFAULT% - default behavior


status
------

Get information about your submitted projects by status.

Value

finished - Projects that were successfully processed

failed - Projects that were not successfully processed because errors have been found

deleted - Projects that have been deleted

fetched - Projects that are being processed

onqueue - Projects that are still on queue to be processed

canceled - Projects that were canceled by the user

Value

finished - single status

failed;canceled - multiple status


string_splitting
----------------

Splits strings found at your source code.

Values

%DEFAULT% - default behavior

occurrences[;concatenation]

Description

occurrences - Percentage of occurrences. Accepted values between 0.01 and 1.

concatenation - Percentage of concatenation occurrences. Accepted values between 0 and 1 (0 means 
chunks of a single character and 1 the whole string).


symbol_table
------------

The symbol table contains key-value pairs representing the source code names and their replacements. 
You may add this parameter to a GET request and retrieve the symbol table. You may also use it as 
parameter of a POST to pre-populate a symbol table of a new obfuscation request.

GET

Get the symbol table of the project with the specific project_id.

Value

%DEFAULT% - default behavior

POST

Pre-populate the symbol table

Value

name,replacement;name1,replacement1;... Pairs containing the JavaScript source code identifier and its replacement.


whitespace
----------

Shrink the size of your JavaScript removing unnecessary whitespaces and newlines from the source code.

Value

%DEFAULT% - default behavior



Status Codes
============

When something goes wrong with your request an error message is returned to signal the issue along with 
the respective HTTP status code.

400 Bad Request
---------------

Requesting the server for something that it does not understand or is invalid


401 Unauthorized
----------------

You will get this error when the authentication fails. This happens when the user provides a bad access key 
or a bad signature.


402 Payment Required
--------------------

A premium subscription is needed to request transformations from the server.

404 Not Found
-------------

When the resource your are trying to retrieve is not found.

500 Internal Server Error
-------------------------

If a server error occurs while processing our support team is notified. 

501 Not Implemented
-------------------

When you request for something that is not implemented you will get this error.



For more information and detail please refer to our Website Documentation:
https://jscrambler.com/en/help/webapi/