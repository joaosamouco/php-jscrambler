<?php
/*
 * Copyright 2013, 2013 AuditMark
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the Lesser GPL
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 */

class Jscrambler {

    private $access_key = null;
    private $secret_key = null;
    private $api_host = 'api.jscrambler.com';
    private $api_port = 80;
    private $api_version = null;

    public function __construct($access_key, $secret_key, $api_host = null, $api_port = null, $api_version = 3) {
        $this->access_key = $access_key;
        $this->secret_key = $secret_key;
        if ($api_host !== null)
            $this->api_host = $api_host;
        if ($api_port !== null)
            $this->api_port = $api_port;
        if ($api_version === null) {
            $this->api_version = 3;
        } else {
            $this->api_version = $api_version;
        }
    }

    public function get($resource_path, $params = array()) {
        return $this->http_request('GET', $resource_path, $params);
    }

    public function post($resource_path, $params = array()) {
        return $this->http_request('POST', $resource_path, $params);
    }

    public function delete($resource_path, $params = array()) {
        return $this->http_request('DELETE', $resource_path, $params);
    }

    private function api_url() {
        $api_host_and_port = $this->api_host;
        if ($this->api_port != 80)
            $api_host_and_port .= ":{$this->api_port}";
        return ($this->api_port == 443 ? "https" : "http") . "://" . $api_host_and_port . "/v{$this->api_version}";
    }

    private function http_request($request_method, $resource_path, $params = null) {
        $request_method = strtoupper($request_method);
        $signed_data = null;
        $url_query_part = null;
        if ($request_method == 'POST')
            $signed_data = $this->signed_query($request_method, $resource_path, $params);
        else
            $url_query_part = '?' . $this->array_to_query($this->signed_query($request_method, $resource_path, $params));

        $url = $this->api_url() . $resource_path . ($url_query_part ? $url_query_part : '');

        $curl = curl_init($url);
        if ($signed_data) {
            curl_setopt($curl, CURLOPT_POST, 1);
            @curl_setopt($curl, CURLOPT_POSTFIELDS, $signed_data);
        }
        if (defined('CURLOPT_PROTOCOLS'))
            curl_setopt($curl, CURLOPT_PROTOCOLS, CURLPROTO_HTTP);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $request_method);
        curl_setopt($curl, CURLOPT_PORT, $this->api_port);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 0);


        /* $transfer = array("transfer" => curl_exec($curl),
          "info" => curl_getinfo($curl)); */

        $transfer = curl_exec($curl);
        curl_close($curl);
        return $transfer;
    }

    private static function construct_file_params($js_files) {
        if (!is_array($js_files))
            $js_files = array($js_files);
        $contents = array();
        foreach ($js_files as $key => $file_path) {
            if (!is_string($file_path) || ($data = @file_get_contents($file_path)) === false)
                throw new Exception("Unable to read file '{$file_path}'");
            $contents["file_$key"] = "@$file_path"; // curl_setopt: To post a file, prepend a filename with @
        }
        return $contents;
    }

    private function signed_query($request_method, $resource_path, $params = array(), $timestamp = null) {
        $signed_params = $this->signed_params($request_method, $resource_path, $params, $timestamp);
        return $signed_params;
    }

    private function signed_params($request_method, $resource_path, $params = array(), $timestamp = null) {
        if ($request_method == 'POST' &&
                array_key_exists('files', $params)) {
            try {
                $file_params = self::construct_file_params($params['files']);
                unset($params['files']);
                $params = array_merge($params, $file_params);
            } catch (Exception $e) {
                echo $e->getFile() . "({$e->getLine()}): " . $e->getMessage() . "\nTrace:\n" . $e->getTraceAsString();
                exit;
            }
        }
        $auth_params = $params;
        $auth_params['timestamp'] = $timestamp ? $timestamp : date('c');
        $auth_params['access_key'] = $this->access_key;
        $auth_params['signature'] = $this->generate_hmac_signature($request_method, $resource_path, $auth_params);

        return $auth_params;
    }

    private function generate_hmac_signature($request_method, $resource_path, $params = array()) {
        foreach ($params as $key => $value)
            if (strpos($key, "file_") !== FALSE)
                $params[$key] = md5_file(substr($params[$key], 1));
        $hmac_signature_data = self::hmac_signature_data($request_method, $resource_path, $this->api_host, $params);
        $hashing_context = hash_init('sha256', HASH_HMAC, $this->secret_key);
        hash_update($hashing_context, $hmac_signature_data);
        return base64_encode(hash_final($hashing_context, true));
    }

    private function hmac_signature_data($request_method, $resource_path, $host, $params = array()) {
        return strtoupper($request_method) . ';' . strtolower($host) . ';' . $resource_path . ';' . self::url_query_string($params);
    }

    private static function url_query_string($params = array()) {
        ksort($params, SORT_STRING);
        return self::array_to_query($params);
    }

    private static function array_to_query($array) {
        $kv = array();
        foreach ($array as $key => $value) {
            $kv[] = self::urlencode($key) . '=' . self::urlencode($value);
        }
        return implode('&', $kv);
    }

    private static function urlencode($data) {
        $encoded_str = urlencode($data);
        $encoded_str = str_replace("%7E", "~", $encoded_str);
        $encoded_str = str_replace("+", "%20", $encoded_str);
        return $encoded_str;
    }

}

?>
