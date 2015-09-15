<?php

namespace Nylas;


use Nylas\Models;
use GuzzleHttp\Client as GuzzleClient;


class Nylas {

    protected $apiServer = 'https://api.nylas.com';
    protected $apiClient;
    protected $apiToken;
    public $apiRoot = 'n';

    public function __construct($appID, $appSecret, $token=NULL, $apiServer=NULL) {
        $this->appID     = $appID;
        $this->appSecret = $appSecret;
        $this->apiToken  = $token;
        $this->apiClient = $this->createApiClient();

        if($apiServer) {
            $this->apiServer = $apiServer;
        }
    }

    protected function createHeaders() {
        $token = 'Basic '.base64_encode($this->apiToken.':');
        $headers = array('headers' => ['Authorization' => $token,
                                       'X-Nylas-API-Wrapper' => 'php']);
        return $headers;
    }

    private function createApiClient() {
        return new GuzzleClient(['base_url' => $this->apiServer]);
    }

    public function createAuthURL($redirect_uri, $login_hint=NULL) {
        $args = array("client_id" => $this->appID,
                      "redirect_uri" => $redirect_uri,
                      "response_type" => "code",
                      "scope" => "email",
                      "login_hint" => $login_hint,
                      "state" => $this->generateId());

        return $this->apiServer.'/oauth/authorize?'.http_build_query($args);
    }

    public function getAuthToken($code) {
        $args = array("client_id" => $this->appID,
                      "client_secret" => $this->appSecret,
                      "grant_type" => "authorization_code",
                      "code" => $code);

        $url = $this->apiServer.'/oauth/token';
        $payload = array();
        $payload['headers']['Content-Type'] = 'application/x-www-form-urlencoded';
        $payload['headers']['Accept'] = 'text/plain';
        $payload['body'] = $args;

        $response = $this->apiClient->post($url, $payload)->json();

        if(array_key_exists('access_token', $response)) {
            $this->apiToken = $response['access_token'];
        }

        return $this->apiToken;
    }

    public function namespaces() {
        $nsObj = new Models\Namespaces($this, NULL);
        return new NylasModelCollection($nsObj, $this, NULL);
    }

    // filter should be filters
    public function getResources($namespace, $klass, $filter) {
        $suffix = ($namespace) ? '/'.$klass->apiRoot.'/'.$namespace : '';
        $url = $this->apiServer.$suffix.'/'.$klass->collectionName;
        $url = $url.'?'.http_build_query($filter);
        $data = $this->apiClient->get($url, $this->createHeaders())->json();

        $mapped = array();
        foreach ($data as $i) {
            $mapped[] = clone $klass->_createObject($this, $namespace, $i);
        }
        return $mapped;
    }

    public function getResource($namespace, $klass, $id, $filters) {
        $extra = '';
        if(array_key_exists('extra', $filters)) {
            $extra = $filters['extra'];
            unset($filters['extra']);
        }
        $response = $this->getResourceRaw($namespace, $klass, $id, $filters);
        return $klass->_createObject($this, $namespace, $response);
    }

    public function getResourceRaw($namespace, $klass, $id, $filters) {
        $extra = '';
        if(array_key_exists('extra', $filters)) {
            $extra = $filters['extra'];
            unset($filters['extra']);
        }
        $prefix = ($namespace) ? '/'.$klass->apiRoot.'/'.$namespace : '';
        $postfix = ($extra) ? '/'.$extra : '';
        $url = $this->apiServer.$prefix.'/'.$klass->collectionName.'/'.$id.$postfix;
        $url = $url.'?'.http_build_query($filters);
        $data = $this->apiClient->get($url, $this->createHeaders())->json();
        return $data;
    }

    public function getResourceData($namespace, $klass, $id, $filters) {
        $extra = '';
        if(array_key_exists('extra', $filters)) {
            $extra = $filters['extra'];
            unset($filters['extra']);
        }
        $prefix = ($namespace) ? '/'.$klass->apiRoot.'/'.$namespace : '';
        $postfix = ($extra) ? '/'.$extra : '';
        $url = $this->apiServer.$prefix.'/'.$klass->collectionName.'/'.$id.$postfix;
        $url = $url.'?'.http_build_query($filters);
        $data = $this->apiClient->get($url, $this->createHeaders())->getBody();
        return $data;
    }

    public function _createResource($namespace, $klass, $data) {
        $prefix = ($namespace) ? '/'.$klass->apiRoot.'/'.$namespace : '';
        $url = $this->apiServer.$prefix.'/'.$klass->collectionName;

        $payload = $this->createHeaders();
        if($klass->collectionName == 'files') {
            $payload['headers']['Content-Type'] = 'multipart/form-data';
            $payload['body'] = $data;
        } else {
            $payload['headers']['Content-Type'] = 'application/json';
            $payload['json'] = $data;
        }

        $response = $this->apiClient->post($url, $payload)->json();
        return $klass->_createObject($this, $namespace, $response);
    }

    public function _updateResource($namespace, $klass, $id, $data) {
        $prefix = ($namespace) ? '/'.$klass->apiRoot.'/'.$namespace : '';
        $url = $this->apiServer.$prefix.'/'.$klass->collectionName.'/'.$id;

        if($klass->collectionName == 'files') {
            $payload['headers']['Content-Type'] = 'multipart/form-data';
            $payload['body'] = $data;
        } else {
            $payload = $this->createHeaders();
            $payload['json'] = $data;
            $response = $this->apiClient->put($url, $payload)->json();
            return $klass->_createObject($this, $namespace, $response);
        }
    }

    public function _deleteResource($namespace, $klass, $id) {
        $prefix = ($namespace) ? '/'.$klass->apiRoot.'/'.$namespace : '';
        $url = $this->apiServer.$prefix.'/'.$klass->collectionName.'/'.$id;

        $payload = $this->createHeaders();
        $response = $this->apiClient->delete($url, $payload)->json();
        return $response;
    }

    private function generateId() {
        // Generates unique UUID
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }

}