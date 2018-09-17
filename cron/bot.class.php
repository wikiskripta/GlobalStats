<?php

/**
 * Mediawiki API access Class
 * @ingroup App
 * @author Josef Martiňák
 * @license MIT
 * @file
 */

class Bot {
    
    private $apiPath;
    private $botLogin;
    private $botPassword;
    private $curl;
    private $path_cookie;

    public function __construct($wikiUrl,$botLogin=false,$botPassword=false) {
        $this->apiPath = $wikiUrl . '/api.php';
        $this->botLogin = $botLogin;
        $this->botPassword = $botPassword;
        $this->curl = curl_init();
        $this->path_cookie = __DIR__ . '/logincookie.txt';
    }

    /**
     * Get response from api call
     * @param array $query
     * @return json response
     */
    public function callApi($query) {
        
        curl_setopt($this->curl, CURLOPT_URL, $this->apiPath);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->curl, CURLOPT_POST, 1);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $query);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, 0);
        $res = curl_exec($this->curl);
        return json_decode($res);
    }

    /**
     * Log bot in
     * Create login cookie
     */
    public function login() {
        
        if (!file_exists($this->path_cookie)) touch($this->path_cookie); 

        $postfields = array(
                'action' => 'query',
                'format'=> 'json',
                'meta' => 'tokens',
                'type' => 'login'
        );

        // get login token
        curl_setopt($this->curl, CURLOPT_URL, $this->apiPath);
        curl_setopt($this->curl, CURLOPT_COOKIESESSION, true);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_POST, true);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $postfields);
        curl_setopt($this->curl, CURLOPT_COOKIEJAR, $this->path_cookie); // you put your cookie in the file
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, 0);
        $res = curl_exec($this->curl);
        $json = json_decode($res);
        $lgToken = $json->query->tokens->logintoken;

        // /!\ don't close the curl conection or initialize a new one or your session id will change !
        $postfields = array(
                'action' => 'login',
                'format'=> 'json',
                'lgtoken' => $lgToken,
                'lgname' => $this->botLogin,
                'lgpassword' => $this->botPassword
        );
        curl_setopt($this->curl, CURLOPT_URL, $this->apiPath);
        curl_setopt($this->curl, CURLOPT_COOKIESESSION, true);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_POST, true);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $postfields);
        curl_setopt($this->curl, CURLOPT_COOKIEFILE, $this->path_cookie); //get the previous cookie
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, 0);
        return $json = json_decode( curl_exec($this->curl) );
    }


    /**
     * Logout
     */
    public function logout() {
        $query = array(
            'action' => 'query',
            'format'=> 'json',
            'meta' => 'tokens',
            'type' => 'csrf'
        );
        $json = $this->callApi($query);
        curl_close($this->curl);
	file_put_contents( $this->path_cookie, "");
    }


    /**
     * Append/prepend text to an article
     * @param string $pagename
     * @param string $text (new content)
     * @param string $action (append/prepend)
     */
    public function addText($pagename, $text, $action = 'append') {

        // get csrf token
        $query = array(
            'action' => 'query',
            'format'=> 'json',
            'meta' => 'tokens',
            'type' => 'csrf'
        );
        $json = $this->callApi($query);
        $csrftoken = $json->query->tokens->csrftoken;

        // edit
        $query = array(
            'action' => 'edit',
            'format'=> 'json',
            'title' => $pagename,
            'summary' => 'LQT archiv',
            'minor' => '1',
            'token' => $csrftoken/*,
            'basetimestamp' => date('Y-m-d\Th:i:s')*/
        );     
        if($action == 'append') $query = array_merge($query, array('appendtext' => $text));
        else $query = array_merge($query, array('prependtext' => $text));
        $json = $this->callApi($query);
    }

}


?>