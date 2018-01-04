<?php
/**
 * Created by PhpStorm.
 * User: aurelien
 * Date: 28/12/17
 * Time: 14:53
 */

namespace Sample\BusinessApp\Utils;

class StamperyFileNotFoundException extends \Exception {}
class StamperyAuthentException extends \Exception {}


class StamperyUtils
{
    protected $_clientID;
    protected $_clientSecret;
    protected $_baseUrl = "https://api-prod.stampery.com";


    /**
     * StamperyUtils constructor.
     * @param string $_clientID
     * @param string $_clientSecret
     */
    public function __construct($_clientID = null, $_clientSecret = null)
    {
        $clientID = $_clientID;
        $clientSecret = $_clientSecret;
        if (!isset($clientID) && !isset($clientSecret)) {
            $keys = json_decode(file_get_contents(__DIR__."/keys.json"));
            $clientID = $keys->id;
            $clientSecret = $keys->key;
            $this->_clientID = $clientID;
            $this->_clientSecret = $clientSecret;
        } elseif (isset($clientID) && isset($clientSecret)) {
            $this->_clientID = $clientID;
            $this->_clientSecret = $clientSecret;
        } else {
            throw new StamperyAuthentException("You must specify full credentials : id AND pass");
        }
    }


    /**
     * Stamping a sha256 hash
     * @param string $hash sha256 hash to stamp
     */
    public function stampingHash($hash) {
        $curl = curl_init($this->_baseUrl."/stamps");
        curl_setopt($curl, CURLOPT_USERPWD, $this->_clientID.":".$this->_clientSecret);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        $postData = array("hash" => $hash);
        $postData_string = json_encode($postData);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: '.strlen($postData_string)));
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postData_string);
        $result = curl_exec($curl);
        $resultObj = json_decode($result, true);
        curl_close($curl);
        return $resultObj;
    }

    /**
     * Stamping a file from its filepath
     * @param string $filepath
     * @throws StamperyFileNotFoundException
     */
    public function stampingFile($filepath) {
        $hash = $this->getHashFromFile($filepath);
        if (!isset($hash)) {
            throw new StamperyFileNotFoundException("$filepath file was not found");
        } else {
            $stampresult = $this->stampingHash($hash);
            return array("stampresult" => $stampresult, "hash" => $hash);
        }
    }

    /**
     * @param $filepath
     * @return null|string
     */
    public function getHashFromFile($filepath) {
        if (!empty($filepath) && file_exists($filepath)) {
            return hash_file("sha256", $filepath);
        }
        return null;
    }
}