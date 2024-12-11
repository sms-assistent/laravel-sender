<?php

namespace SmsAssistent\Sender;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;

class Sender
{
    /**
     * Settings
     *
     */
    private $useragent;
    private $username;
    private $password;
    private $sender_name;


    private $errors;
    private $statuses;


    /**
     * Create a new instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->useragent   = Config::get('sender.useragent');
        $this->username    = Config::get('sender.username');
        $this->password    = Config::get('sender.password');
        $this->sender_name = Config::get('sender.sender_name');

        $this->errors = Lang::get('sender::errors');
        $this->statuses = Lang::get('sender::statuses');
    }

    /**
     * @param $url
     * @param $data
     * @return array
     */
    private function send($url, $data = false, $contentType = false)
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->useragent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
        curl_setopt($ch, CURLOPT_POST, 1);

        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        curl_setopt($ch, CURLOPT_TIMEOUT, 1200);

        $header = [];
        if ($contentType) {
            $header[] = 'Content-Type: ' . $contentType;
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        $content = curl_exec($ch);
        $err = curl_errno($ch);
        $errmsg = curl_error($ch);
        $header = curl_getinfo($ch);

        curl_close($ch);

        $header['errno'] = $err;
        $header['errmsg'] = $errmsg;
        $header['content'] = $content;

        return $header;
    }

    /**
     * @param $phones
     * @param $message
     * @return array
     */
    public function sendBulk($phones, $message)
    {
        $response = [];

        $source = 'https://userarea.sms-assistent.by/api/v1/xml';

        $data = '<?xml version="1.0" encoding="utf-8" ?>';
        $data .= '<package login="' . $this->username . '" password="' . $this->password . '">';
        $data .= '<message>';

        foreach ($phones as $phone) {
            $data .= '<msg recipient="' . $phone . '" sender="' . $this->sender_name . '" validity_period="86400">' . $message . '</msg>';
        }

        $data .= '</message>';
        $data .= '</package>';

        $proceed = $this->send($source, $data, 'text/xml');

        $xml = json_decode(json_encode(simplexml_load_string($proceed['content'])), true);

        if (isset($xml['error'])) {

            $response['error']['code'] = $xml['error'];
            $response['error']['description'] = $this->errors[$xml['error']];

            return $response;
        }

        if (strlen($proceed['content']) > 1) {
            foreach ($xml['message']['msg'] as $key => $message) {
                $response[$key]['id'] = $message['@attributes']['sms_id'];
                $response[$key]['phone'] = $phones[$key];
            }
        }

        return $response;
    }

    /**
     * @param $phone
     * @param $message
     * @return array
     */
    public function sendOne($phone, $message)
    {
        $response = [];

        $params = [
            'user' => $this->username,
            'password' => $this->password,
            'recipient' => $phone,
            'message' => $message,
            'sender' => $this->sender_name,
        ];

        // Endpoint
        $source = 'https://userarea.sms-assistent.by/api/v1/send_sms/plain';
        $proceed = $this->send($source, $params);

        if (in_array($proceed['content'], array_keys($this->errors))) {

            $response['error']['code'] = $proceed['content'];
            $response['error']['description'] = $this->errors[$proceed['content']];

            return $response;
        }

        $response[0]['id'] = $proceed['content'];
        $response[0]['phone'] = $phone;

        return $response;
    }

    /**
     * @param $ids
     * @return array
     */
    public function getStatus($ids)
    {
        $response = [];

        $array = is_array($ids);

        $source = 'https://userarea.sms-assistent.by/api/v1/xml';

        $data = '<?xml version="1.0" encoding="utf-8" ?>';
        $data .= '<package login="' . $this->username . '" password="' . $this->password . '">';
        $data .= '<status>';

        if (!$array) $data .= '<msg sms_id="' . $ids . '"/>';
        else {
            foreach ($ids as $id) {
                $data .= '<msg sms_id="' . $id . '"/>';
            }
        }


        $data .= '</status>';
        $data .= '</package>';

        $proceed = $this->send($source, $data, 'text/xml');

        $xml = json_decode(json_encode(simplexml_load_string($proceed['content'])), true);

        if (isset($xml['error'])) {

            $response['error']['code'] = $xml['error'];
            $response['error']['description'] = $this->errors[$xml['error']];

            return $response;
        }

        if (strlen($proceed['content']) > 1) {

            if (!$array) {
                $response[0]['id'] = $ids;
                $response[0]['status'] = $xml['status']['msg'];
            } else {
                foreach ($xml['status']['msg'] as $id => $status) {
                    $response[$id]['id'] = $id;
                    $response[$id]['status'] = $status;
                }
            }

        }

        return $response;
    }
}
