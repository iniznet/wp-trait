<?php

namespace WPTrait\Collection;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('WPTrait\Collection\Request')) {

    class Request
    {
        public function all($var = 'REQUEST')
        {
            $_array = [];
            foreach ((array)$var as $key) {
                $_array = array_merge($_array, $this->getGlobalVariable($key));
            }
            return (object)$_array;
        }

        public function input($name, $filter = null)
        {
            $inputs = $this->all();
            return (isset($inputs->{$name}) ? $this->filter($inputs->{$name}, $filter) : null);
        }

        private function filter($value, $filter = [])
        {
            foreach ((array)$filter as $func) {
                $value = $func($value);
            }

            return $value;
        }

        public function query($name, $filter = null)
        {
            $inputs = $this->all('GET');
            return (isset($inputs->{$name}) ? $this->filter($inputs->{$name}, $filter) : null);
        }

        public function only($array = [], $filter = null)
        {
            $_array = [];
            $inputs = $this->all();
            foreach ($inputs as $name => $value) {
                $_array[$name] = (in_array($name, (array)$array) ? $this->filter($value, $filter) : null);
            }

            return (object)$_array;
        }

        public function has($name)
        {
            $inputs = $this->all();
            return isset($inputs->{$name});
        }

        public function filled($name)
        {
            $inputs = $this->all();
            return (isset($inputs->{$name}) and !empty(trim($inputs->{$name})));
        }

        public function numeric($name, $positive = null)
        {
            $input = $this->input($name, ['trim']);
            $numeric = ($this->filled($name) and is_numeric($input));
            if ($positive === true) {
                return ($numeric and $input > 0);
            } elseif ($positive === false) {
                return ($numeric and $input < 0);
            }

            return $numeric;
        }

        public function equal($name, $value)
        {
            return ($this->input($name) == $value);
        }

        public function enum($name, $array)
        {
            return (in_array($this->input($name), $array));
        }

        public function redirect($location, $status = 302)
        {
            if (function_exists('wp_redirect')) {
                wp_redirect($location, $status);
            }
        }

        public function cookie($name)
        {
            $inputs = $this->all('COOKIE');
            return (isset($inputs->{$name}) ? $inputs->{$name} : null);
        }

        public function server($name)
        {
            $inputs = $this->all('SERVER');
            return (isset($inputs->{$name}) ? $inputs->{$name} : null);
        }

        public function file($name)
        {
            $files = $this->all('files');
            /**
             * ['name' => '', 'type' => 'image/png', 'tmp_name' => '', 'error' => 0, 'size' => '']
             */
            return (isset($files->{$name}) ? $files->{$name} : null);
        }

        public function hasFile($name)
        {
            $files = $this->all('files');
            return (isset($files->{$name}) and !empty($files->{$name}["name"]));
        }

        public function is_rest()
        {
            #see https://developer.wordpress.org/reference/functions/rest_api_loaded/
            return (defined('REST_REQUEST') && REST_REQUEST);
        }

        public function is_ajax()
        {
            return wp_doing_ajax();
        }

        public function is_cron()
        {
            return wp_doing_cron();
        }

        public function is_xmlrpc()
        {
            return (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST);
        }

        public function is_cli()
        {
            return (defined('WP_CLI') && WP_CLI);
        }

        public function get_method()
        {
            $method = strtoupper($this->server('REQUEST_METHOD'));
            if (in_array($method, ['GET', 'HEAD', 'POST', 'PUT', 'DELETE', 'CONNECT', 'OPTIONS', 'PATCH', 'PURGE', 'TRACE'], true)) {
                return $method;
            }

            return 'GET';
        }

        public function is_method($name)
        {
            return ($this->get_method($name) == strtoupper($name));
        }

        public function new($url, $method = 'GET', $args = [])
        {
            # alias
            if (isset($args['ssl'])) {
                $args['sslverify'] = $args['ssl'];
                unset($args['sslverify']);
            }

            # https://developer.wordpress.org/reference/classes/WP_Http/request/
            return wp_remote_request($url, array_merge(['method' => strtoupper($method)], $args));
        }

        public function download($file_url, $path = false, $timeout = 300, $signature_verification = false)
        {
            if (!function_exists('download_url')) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
            }

            $tmp_file = download_url($file_url, $timeout, $signature_verification);
            if (is_wp_error($tmp_file)) {
                return $tmp_file;
            }

            if ($path != false) {
                copy($tmp_file, $path);
                @unlink($tmp_file);
            }

            return $tmp_file;
        }

        private function getGlobalVariable($name = 'REQUEST')
        {
            switch (strtolower($name)) {
                case "get":
                    $return = ($_GET ?? []);
                    break;
                case "post":
                    $return = ($_POST ?? []);
                    break;
                case "file":
                case "files":
                    $return = ($_FILES ?? []);
                    break;
                case "cookie":
                    $return = ($_COOKIE ?? []);
                    break;
                case "server":
                    $return = ($_SERVER ?? []);
                    break;
                case "session":
                    $return = ($_SESSION ?? []);
                    break;
                default:
                    $return = ($_REQUEST ?? []);
            }

            return $return;
        }
    }
}
