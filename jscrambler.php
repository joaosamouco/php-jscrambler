<?php
/**
 * Simple program that uses JScrambler client in php to upload a JavaScript 
 * project for obfuscation and poll the server to download the obfuscated
 * project as soon as it is finished.
 *
 * SYNOPIS
 *  jscrambler.php destination_path [poll_delay_in_seconds] [config_file_path]
 *
 * DESCRIPTION
 *  destiantion_path        Where archive retrieved from JScrambler will be
 *                          stored
 *  
 *  poll_delay_in_seconds   Number of seconds to wait before each JScrambler
 *                          project status check
 *
 *  config_file_path        The absolute path to your custom configuration file
 *                          or one of the delivered files (for those you just
 *                          have to provide the file name)
 *
 * For usage on CLI you can use one of the bootstrap scripts (jscrambler for
 * Linux like operationg systems and jscrambler.bat for Windows)
 *
 * You can call it on CLI as follow (for the delivered self-defending.json
 * configuration file)
 * 
 * <code>
 *  $ ./jscrambler /destination/path self-defending.json
 * </code>
 *
 * Or with your custom configuration file
 *
 * <code>
 * 	$ ./jscrambler /destination/path /project/build/my-config.json
 * </code>
 *
 * @copyright   2013, 2014 AuditMark
 * @license     This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the License,
 * or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License
 * for more details.
 * 
 * You should have received a copy of the Lesser GPL along with this program.
 * If not, see <http://www.gnu.org/licenses/>
 */

require_once __DIR__.'/includes/jscrambler.php';

/**
 * Default directory for configuration files
 */
define('DEFAULT_CONFIG_DIR',__DIR__.DIRECTORY_SEPARATOR.'configs');

/**
 * Default configuration file
 */
define('DEFAULT_CONFIG_FILE',DEFAULT_CONFIG_DIR.DIRECTORY_SEPARATOR.'config.json');

/**
 * Default delay between each JScrambler Web API query for job status
 */
define('DEFAULT_DELAY',5);

try
{
	if ($argc < 2 OR $argc > 4)
	{
		die(sprintf(
			"Usage: %s destination_path [poll_delay_in_seconds] [config_file_path]\n",
			$argv[0]
		));
	}

	// defaults
	$config_file_path = NULL;
	$delay_seconds = DEFAULT_DELAY;

	// Validate command line options
	if ( ! is_dir($argv[1]) OR ! is_writable($argv[1]))
		throw new Exception('destination is not a directory or it is not writable');

	// keep destination path with trailling slash
	$destination_path = rtrim($argv[1],DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

	// optional arguments
	if (isset($argv[2]) AND ! empty($argv[2]))
	{
		if (ctype_digit($argv[2]))
			$delay_seconds = intval($argv[2]);
		elseif (is_string($argv[2]))
			$config_file_path = $argv[2];
	}

	// check the 3rd optional
	if (isset($argv[3]) AND ! empty($argv[3]))
	{
		$config_file_path = $argv[3];

		if ( ! is_file($argv[3]))
		{
			// assume that user is referencing a default configuration file
			$config_file_path = DEFAULT_CONFIG_DIR.'/'.$argv[3];
		}
	}

	// if no configuration file was given, default to DEFAULT_CONFIG_FILE
	if ($config_file_path === NULL)
		$config_file_path = DEFAULT_CONFIG_FILE;

	// Parse config json parameters
	$config_file_contents = file_get_contents($config_file_path);

	if ($config_file_contents === false)
	{
		throw new Exception(sprintf(
			'"%s" configuration file not found',
			$config_file_path
		));
	}

	$options = json_decode($config_file_contents);

	if (empty($options))
	{
		throw new Exception(sprintf(
			'Failed to decode "%s" configuration file',
			$config_file_path
		));
	}

	printf('Using "%s" as configuration file.'.PHP_EOL,$config_file_path);

	echo 'Checking connection credentials...',PHP_EOL;

	if ( ! isset($options->connection))
	{
		throw new Exception(
			'"connection" parameter was not found on configuration file'
		);
	}
	elseif ( ! isset($options->connection->access_key))
	{
		throw new Exception(
			'"access_key" parameter was not found on configuration file'
		);
	}
	elseif ( ! isset($options->connection->secret_key))
	{
		throw new Exception(
			'"secret_key" parameter was not found on configuration file'
		);
	}

	if ( ! isset($options->files))
		throw new Exception('"files" parameter was not found on configuration file');

	// create the list of files to be sent (no validation is performed)
	if (is_string($options->files))
	{
		$files_list = $options->files;
	}
	else
	{
		$files_list = array();
		foreach ($options->files as $filename)
		{
			$files_list[] = $filename;
		}
	}

	$post_params = array('files' => $options->files);

	if ( ! isset($options->parameters))
	{
		throw new Exception(
			'"parameters" parameter was not found on configuration file'
		);
	}

	foreach ($options->parameters as $key => $value)
	{
		$post_params[$key] = $value;
	}

	// Init jscrambler client
	$server = $port = NULL;

	if (isset($options->connection->server) AND
		! empty($options->connection->server))
	{
		$server = $options->connection->server;
	}

	if (isset($options->connection->port) AND
		! empty($options->connection->port))
	{
		$port = $options->connection->port;
	}

	$jscrambler = new Jscrambler(
		$options->connection->access_key, 
		$options->connection->secret_key, 
		$server,
		$port
	);

	// Send project to api server
	echo "Sending files to server.",PHP_EOL;
	$post_response = $jscrambler->post('/code.json', $post_params);

	// if sending project fails
	if ($post_response == NULL)
		throw new Exception('Failed to send project to server');

	// parse json response
	$post_response_obj = json_decode($post_response);

	if (empty($post_response_obj))
		throw new Exception('Failed to decode json POST response');

	if ( ! isset($post_response_obj->id) || isset($post_response_obj->error))
		throw new Exception("Something went wrong.\n$post_response\n");

	if (isset($post_response_obj->warnings))
	{
		if (isset($post_response_obj->warnings->unknown_parameters))
		{
			printf(
				'Unknown parameters found [%s]'.PHP_EOL,
				implode(",", $post_response_obj->warnings->unknown_parameters)
			);
		}
	}

	// poll server
	echo "Polling server...\n";
	while(true)
	{
		// get project information
		$get_response = $jscrambler->get("/code/{$post_response_obj->id}.json");

		// parse GET response
		$get_response_obj = json_decode($get_response);

		if (empty($get_response_obj))
			throw new Exception('Failed to decode json GET response');

		if ($get_response_obj->error_id == "0")
		{
			echo "Ready to download.\n";
			break;
		}
		elseif (isset($get_response_obj->error_id) ||
				isset($get_response_obj->error_message))
		{
			throw new Exception(sprintf(
				"Error found.\nid: %s\nmessage: %s",
				$get_response_obj->error_id,
				$get_response_obj->error_message
			));
		}
		elseif ( ! isset($get_response_obj->id) || isset($get_response_obj->error))
		{
			throw new Exception(sprintf(
				"Something went wrong.\n%s\n",
				$get_response)
			);
		}

		printf("Not ready. Next poll in %d seconds\n",$delay_seconds);
		sleep($delay_seconds);
	}

	$filename = "{$get_response_obj->id}.{$get_response_obj->extension}";
	$result_path = $destination_path.$filename;

	printf('Writting file to "%s"...'.PHP_EOL,$result_path);

	file_put_contents($result_path, $jscrambler->get("/code/{$post_response_obj->id}.zip"));

	echo 'Done.',PHP_EOL;

	exit(0);
}
catch (Exception $e)
{
    printf('ERROR - %s'.PHP_EOL,$e->getMessage());

    exit(1);
}

die;