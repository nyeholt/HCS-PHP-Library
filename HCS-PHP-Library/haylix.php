<?php
/**
 * This is the Haylix Cloud Storage PHP API.
 *
 * See the included tests directory for additional sample code.
 *
 * Requres PHP 5.x (for Exceptions and OO syntax) and PHP's cURL module.
 *
 * See COPYING for license information.
 *
 * @author Peter Pang <peter.pang@haylix.com>
 * @copyright Copyright (c) 2012, Haylix AU, Inc.
 * @package php_sdk
 */

define("MEL_AUTHURL", "https://api.s.mel.secureinf.net");

class CloudStorage
{

    public function __construct($account, $username, $password)
    {

        $this->account = $account;
        $this->username = $username;
        $this->password = $password;

        $this->token = "";
        $this->conn = "";

    }

    public function __destruct()
    {

        if ($this->conn != "")
        {
            curl_close($this->conn);
        }

    }

    private function get_conn($method, $uri, $headers, $timeout=10, $upload="", $download="", $nobody="")
    {
        $this->conn = curl_init();

        curl_setopt($this->conn, CURLOPT_URL, MEL_AUTHURL.$uri);
        curl_setopt($this->conn, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($this->conn, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($this->conn, CURLOPT_HEADER, 1);
        curl_setopt($this->conn, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->conn, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($this->conn, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($this->conn, CURLINFO_HEADER_OUT, true);

        if ($upload != "")
        {
            curl_setopt($this->conn, CURLOPT_POSTFIELDS, $upload);
        }

        if ($download != "")
        {
            curl_setopt($this->conn, CURLOPT_FILE, $download);
        }

        if ($nobody != "")
        {
            curl_setopt($this->conn, CURLOPT_NOBODY, 1);
        }

        return 0;
    }

    private function close_conn()
    {
        curl_close($this->conn);

        $this->conn = "";

        return 0;
    }

    private function get_token()
    {

        $headers = array("X-Auth-User: ".$this->account.":".$this->username, "X-Auth-Key: ".$this->password);

        $this->get_conn("GET", "/auth/v0", $headers);

        $response = curl_exec($this->conn);

        $code = curl_getinfo($this->conn, CURLINFO_HTTP_CODE);

        $this->close_conn();

        if ($code == 200)
        {
            list($header, $body) = explode("\r\n\r\n", $response, 2);  

            $headers = explode("\r\n", $header);

            $tmp = explode(": ", $headers[3], 2);

            $this->token = $tmp[1];

            return 0;
        }


        return -1;
    }


    public function add_user($username, $password)
    {
        $headers = array("X-Auth-User-Key:".$password, "X-Auth-Admin-User: ".$this->account.":".$this->username, "X-Auth-Admin-Key: ".$this->password);

        $this->get_conn("PUT", "/auth/v0/".$this->account."/".$username, $headers);

        $response = curl_exec($this->conn);

        $code = curl_getinfo($this->conn, CURLINFO_HTTP_CODE);

        $this->close_conn();

        if ($code == 201)
        {
            return 0;
        }

        if ($code == 202)
        {
            return 1;
        }

        return -1;
    }


    public function del_user($username)
    {
        $headers = array("X-Auth-Admin-User: ".$this->account.":".$this->username, "X-Auth-Admin-Key: ".$this->password);

        $this->get_conn("DELETE", "/auth/v0/".$this->account."/".$username, $headers);

        $response = curl_exec($this->conn);

        $code = curl_getinfo($this->conn, CURLINFO_HTTP_CODE);

        $this->close_conn();

        if ($code == 204)
        {
            return 0;
        }

        if ($code == 404)
        {
            return 1;
        }

        return -1;
    }


    public function add_container($container)
    {
        if ($this->token == "")
        {
            if ($this->get_token() < 0)
            {
                return -1;
            }
        }

        $headers = array("X-Auth-Token: ".$this->token);

        $this->get_conn("PUT", "/v0/".$this->account."/".$container, $headers);

        $response = curl_exec($this->conn);

        $code = curl_getinfo($this->conn, CURLINFO_HTTP_CODE);

        $this->close_conn();

        if ($code == 201)
        {
            return 0;
        }

        if ($code == 202)
        {
            return 1;
        }

        return -2;
    }

    public function del_container($container)
    {
        if ($this->token == "")
        {
            if ($this->get_token() < 0)
            {
                return -1;
            }
        }

        $headers = array("X-Auth-Token: ".$this->token);

        $this->get_conn("DELETE", "/v0/".$this->account."/".$container, $headers);

        $response = curl_exec($this->conn);

        $code = curl_getinfo($this->conn, CURLINFO_HTTP_CODE);

        $this->close_conn();

        if ($code == 204)
        {
            return 0;
        }

        if ($code == 404)
        {
            return 1;
        }

        return -2;
    }

    public function list_container()
    {
        if ($this->token == "")
        {
            if ($this->get_token() < 0)
            {
                return -1;
            }
        }

        $headers = array("X-Auth-Token: ".$this->token);

        $this->get_conn("GET", "/v0/".$this->account, $headers);

        $response = curl_exec($this->conn);

        $code = curl_getinfo($this->conn, CURLINFO_HTTP_CODE);

        $this->close_conn();

        if ($code == 200)
        {
            list($header, $body) = explode("\r\n\r\n", $response, 2);  
            return $body;
        }

        if ($code == 404 || $code == 204)
        {
            return "";
        }

        return -2;
    }

	public function upload_file($container, $filename)
	{
		$file = file_get_contents($filename);
		$this->upload_data($container, $file, $filename);
	}
	
    public function upload_data($container, $file, $filename) 
    {
        if ($file == FALSE) 
        {
            return -1;
        }

        $arr = explode("/", $filename);


        if ($this->token == "")
        {
            if ($this->get_token() < 0)
            {
                return -2;
            }
        }

        $headers = array("X-Auth-Token: ".$this->token);

        $this->get_conn("PUT", "/v0/".$this->account."/".$container."/".$arr[sizeof($arr) - 1], $headers, 360, $file, "", "");

        $response = curl_exec($this->conn);

        $code = curl_getinfo($this->conn, CURLINFO_HTTP_CODE);

        $this->close_conn();

        if ($code == 201)
        {
            return 0; 
        }

        if ($code == 202)
        {
            return 1; 
        }

        return -3;
    }


    public function download_file($container, $filename, $target="")
    {

        if ($target == "")
        {
            $target = $filename;
        }

        $out = fopen($target, 'wb'); 

        if ($out == FALSE)
        {
            return -1;
        }
        
        if ($this->token == "")
        {
            if ($this->get_token() < 0)
            {
                fclose($out); 
                return -2;
            }
        }

        $headers = array("X-Auth-Token: ".$this->token);

        $this->get_conn("GET", "/v0/".$this->account."/".$container."/".$filename, $headers, 360, "", $out, "");

        $response = curl_exec($this->conn);

        $code = curl_getinfo($this->conn, CURLINFO_HTTP_CODE);

        $this->close_conn();
        fclose($out); 

        if ($code == 200)
        {
            return 0; 
        }

        if ($code == 404)
        {
            return 1;
        }


        return -3;
    }

    public function del_file($container, $filename)
    {

        if ($this->token == "")
        {
            if ($this->get_token() < 0)
            {
                return -1;
            }
        }

        $headers = array("X-Auth-Token: ".$this->token);

        $this->get_conn("DELETE", "/v0/".$this->account."/".$container."/".$filename, $headers);

        $response = curl_exec($this->conn);

        $code = curl_getinfo($this->conn, CURLINFO_HTTP_CODE);

        $this->close_conn();

        if ($code == 204)
        {
            return 0; 
        }

        if ($code == 404)
        {
            return 1;
        }

        return -2;
    }

    public function list_file($container)
    {

        if ($this->token == "")
        {
            if ($this->get_token() < 0)
            {
                return -1;
            }
        }

        $headers = array("X-Auth-Token: ".$this->token);

        $this->get_conn("GET", "/v0/".$this->account."/".$container."/", $headers);

        $response = curl_exec($this->conn);

        $code = curl_getinfo($this->conn, CURLINFO_HTTP_CODE);

        $this->close_conn();

        if ($code == 200)
        {
            list($header, $body) = explode("\r\n\r\n", $response, 2);  
            return $body;
        }

        if ($code == 404 || $code == 204)
        {
            return "";
        }

        return -2;
    }

    public function public_container($container)
    {

        if ($this->token == "")
        {
            if ($this->get_token() < 0)
            {
                return -1;
            }
        }

        $headers = array("X-Auth-Token: ".$this->token, "X-Container-Read: .r:*");

        $this->get_conn("POST", "/v0/".$this->account."/".$container, $headers);

        $response = curl_exec($this->conn);

        $code = curl_getinfo($this->conn, CURLINFO_HTTP_CODE);

        $this->close_conn();

        if ($code == 204)
        {
            return 0;
        }

        if ($code == 404)
        {
            return 1;
        }

        return -2;
    }

    public function unpublic_container($container)
    {

        if ($this->token == "")
        {
            if ($this->get_token() < 0)
            {
                return -1;
            }
        }

        $headers = array("X-Auth-Token: ".$this->token, "X-Container-Read: \n");

        $this->get_conn("POST", "/v0/".$this->account."/".$container, $headers);

        $response = curl_exec($this->conn);

        $code = curl_getinfo($this->conn, CURLINFO_HTTP_CODE);

        $this->close_conn();

        if ($code == 204)
        {
            return 0;
        }

        if ($code == 404)
        {
            return 1;
        }

        return -2;
    }

    public function refer_container($container, $refer)
    {

        if ($this->token == "")
        {
            if ($this->get_token() < 0)
            {
                return -1;
            }
        }

        $headers = array("X-Auth-Token: ".$this->token, "X-Container-Read: .r:".$refer);

        $this->get_conn("POST", "/v0/".$this->account."/".$container, $headers);

        $response = curl_exec($this->conn);

        $code = curl_getinfo($this->conn, CURLINFO_HTTP_CODE);

        $this->close_conn();

        if ($code == 204)
        {
            return 0;
        }

        if ($code == 404)
        {
            return 1;
        }

        return -2;
    }

    public function info_file($container, $filename)
    {
        if ($this->token == "")
        {
            if ($this->get_token() < 0)
            {
                return -1;
            }
        }

        $headers = array("X-Auth-Token: ".$this->token);

        $this->get_conn("HEAD", "/v0/".$this->account."/".$container."/".$filename, $headers, 10, "", "", TRUE);

        $response = curl_exec($this->conn);

        $code = curl_getinfo($this->conn, CURLINFO_HTTP_CODE);

        $this->close_conn();

        if ($code == 200)
        {
            return $response;
        }

        if ($code == 404)
        {
            return "";
        }

        return -2;
    }

    public function info_container($container)
    {
        if ($this->token == "")
        {
            if ($this->get_token() < 0)
            {
                return -1;
            }
        }

        $headers = array("X-Auth-Token: ".$this->token);

        $this->get_conn("HEAD", "/v0/".$this->account."/".$container, $headers, 10, "", "", TRUE);

        $response = curl_exec($this->conn);

        $code = curl_getinfo($this->conn, CURLINFO_HTTP_CODE);

        $this->close_conn();

        if ($code == 204)
        {
            return $response;
        }

        if ($code == 404)
        {
            return "";
        }

        return -2;
    }

    public function http_container($container)
    {

        if ($this->token == "")
        {
            if ($this->get_token() < 0)
            {
                return -1;
            }
        }

        $headers = array("X-Auth-Token: ".$this->token, "X-Container-Read: .r:*,.r:http");

        $this->get_conn("POST", "/v0/".$this->account."/".$container, $headers);

        $response = curl_exec($this->conn);

        $code = curl_getinfo($this->conn, CURLINFO_HTTP_CODE);

        $this->close_conn();

        if ($code == 204)
        {
            return 0;
        }

        if ($code == 404)
        {
            return 1;
        }

        return -2;
    }

    public function https_container($container)
    {

        if ($this->token == "")
        {
            if ($this->get_token() < 0)
            {
                return -1;
            }
        }

        $headers = array("X-Auth-Token: ".$this->token, "X-Container-Read: .r:*,.r:https");

        $this->get_conn("POST", "/v0/".$this->account."/".$container, $headers);

        $response = curl_exec($this->conn);

        $code = curl_getinfo($this->conn, CURLINFO_HTTP_CODE);

        $this->close_conn();

        if ($code == 204)
        {
            return 0;
        }

        if ($code == 404)
        {
            return 1;
        }

        return -2;
    }

    public function rw_container($container, $read_users="", $write_users="")
    {

        $ru = "";
        $wu = "";
        $i = 0;

        $rs = sizeof($read_users);

        if ($rs > 5)
        {
            return -1;
        }

        $ws = sizeof($write_users);

        if ($ws > 5)
        {
            return -2;
        }

        if ($read_users != "")
        {
            foreach ($read_users as $value)
            {
                if ($i == $rs - 1)
                {
                    $ru .= $this->account.":".$value;    
                }
                else
                {
                    $ru .= $this->account.":".$value.",";    
                }

                $i++;
            }
        }

        $i = 0;

        if ($write_users != "")
        {
            foreach ($write_users as $value)
            {
                if ($i == $ws - 1)
                {
                    $wu .= $this->account.":".$value;    
                }
                else
                {
                    $wu .= $this->account.":".$value.",";    
                }

                $i++;
            }
        }


        if ($this->token == "")
        {
            if ($this->get_token() < 0)
            {
                return -3;
            }
        }

        if ($ru != "" && $wu !="")
        {
            $headers = array("X-Auth-Token: ".$this->token, "X-Container-Read: ".$ru, "X-Container-Write: ".$wu);
        }
        else if ($ru != "" && $wu == "")
        {
            $headers = array("X-Auth-Token: ".$this->token, "X-Container-Read: ".$ru);
        }
        else if ($ru == "" && $wu != "")
        {
            $headers = array("X-Auth-Token: ".$this->token, "X-Container-Write: ".$wu);
        }
        else
        {
            return -4;
        }

        $this->get_conn("POST", "/v0/".$this->account."/".$container, $headers);

        $response = curl_exec($this->conn);

        $code = curl_getinfo($this->conn, CURLINFO_HTTP_CODE);

        $this->close_conn();

        if ($code == 204)
        {
            return 0;
        }

        if ($code == 404)
        {
            return 1;
        }

        return -5;
    }


    public function list_user()
    {
        $headers = array("X-Auth-Admin-User: ".$this->account.":".$this->username, "X-Auth-Admin-Key: ".$this->password);

        $this->get_conn("GET", "/auth/v0/".$this->account, $headers);

        $response = curl_exec($this->conn);

        $code = curl_getinfo($this->conn, CURLINFO_HTTP_CODE);

        $this->close_conn();

        if ($code == 200)
        {
            list($header, $body) = explode("\r\n\r\n", $response, 2);
            return $body;
        }

        return -1;
    }
}
