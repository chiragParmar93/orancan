<?php

use Aws\S3\S3Client;

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

// COMMON FUNCTION FOR PRINT_R AND PRE TAG
if (!function_exists('pr')) {

    function pr($array = '', $exit = '')
    {
        echo '<pre>';
        print_r($array);
        echo '</pre>';
        if ($exit == 1) {
            exit;
        }
    }

}

// COMMON FUNCTION FOR CUSTOM ENCRYPTION
if (!function_exists('encrypt_val')) {

    function encrypt_val($val)
    {
        $key    = "cas6978";
        $result = '';
        for ($i = 0; $i < strlen($val); $i++) {
            $char    = substr($val, $i, 1);
            $keychar = substr($key, ($i % strlen($key)), 1);
            $char    = chr(ord($char) + ord($keychar));
            $result .= $char;
        }
        return base64_encode($result);
    }

}

// COMMON FUNCTION FOR CUSTOM DECRYPTION
if (!function_exists('decrypt_val')) {

    function decrypt_val($val)
    {
        $key    = "cas6978";
        $result = '';
        $val    = base64_decode($val);
        for ($i = 0; $i < strlen($val); $i++) {
            $char    = substr($val, $i, 1);
            $keychar = substr($key, ($i % strlen($key)), 1);
            $char    = chr(ord($char) - ord($keychar));
            $result .= $char;
        }
        return $result;
    }

}

// COMMON FUNCTION FOR CHECK EMAIL VALID HOST
if (!function_exists('check_email_host')) {

    function check_email_host($val)
    {
        $hostemail = explode('@', $val);
        $email     = checkdnsrr($hostemail[1]);
        if ($email == 1) {
            return 'valid';
        } else {
            return 'notvalid';
        }
    }

}

// COMMON FUNCTION FOR DATE AND TIME
if (!function_exists('get_date')) {

    function get_date()
    {
        return date('Y-m-d');
    }

}
if (!function_exists('get_time')) {

    function get_time()
    {
        return date('h:i:s');
    }

}
// COMMON FUNCTION FOR DATE AND TIME
if (!function_exists('get_date_time')) {

    function get_date_time()
    {
        return date('Y-m-d h:i:s');
    }

}

// COMMON FUNCTION FOR DATE AND TIME 24 HOURSE
if (!function_exists('get_date_time_24')) {

    function get_date_time_24()
    {
        return date('Y-m-d H:i:s');
    }

}

// COMMON FUNCTION FOR DATE AND TIME
if (!function_exists('get_ip')) {

    function get_ip()
    {
        return $_SERVER['REMOTE_ADDR'];
    }

}

// COMMON FUNCTION FOR CHANGE DATE FORMAT
if (!function_exists('custom_date_format')) {

    function custom_date_format($date)
    {
        return date('m/d/Y', strtotime($date));
    }

}

// COMMON FUNCTION FOR CHANGE TIME DATE FORMAT
if (!function_exists('custom_date_time_format')) {

    function custom_date_time_format($date)
    {
        return date('m/d/Y H:i:s', strtotime($date));
    }

}

// COMMON FUNCTION FOR CHANGE DATE FORMAT
if (!function_exists('db_date_format')) {

    function db_date_format($date)
    {
        return date('Y-m-d', strtotime($date));
    }

}

// COMMON FUNCTION FOR CHANGE DATE AND TIME FORMAT
if (!function_exists('db_date_time_format')) {

    function db_date_time_format($date)
    {
        return date('Y-m-d H:i:s', strtotime($date));
    }

}

// RETURN JAVASCRIPT PATH
function jspath()
{
    return base_url() . "assets/js/";
}

// RETURN PLUGIN PATH
function pluginpath()
{
    return base_url() . "assets/plugins/";
}

// RETURN PAGE PATH
function vendor()
{
    return base_url() . "assets/vendor/";
}

// RETURN CSS PATH
function csspath()
{
    return base_url() . "assets/css/";
}

// RETURN IMAGE PATH
function imagepath()
{
    return base_url() . "assets/images/";
}

// COMMON FUNCTION REALA AUTHORIZIATION

function isFrontuserAuthorized()
{
    $CI = &get_instance();
    if ($CI->session->userdata('frontuser') && $CI->session->userdata['frontuser']['is_logged_in'] == 1) {

    } else {
        redirect('login');
    }
}

//BACK TO WEBSITE LINK
function back_to_bewsite_link()
{
    if ($_SERVER['HTTP_HOST'] == "sasa-srv-2") {
        return "http://sasa-srv-2/reala/";
    } else {
        return "https://reala.co";
    }
}

// RETURN IMAGE PATH
function upload_imagepath()
{
    $CI = &get_instance();
    return APPPATH . "upload/";
}

function view_imagepath()
{
    return base_url() . "application/upload/";
}

function writeLogFile($mixData, $strFileName = '', $strFilePath = '')
{
    try {
        $blnIsPrintArray = true;
        date_default_timezone_set("Asia/Kolkata");
        if (empty($strFileName)) {
            $strFileName = date("Y-m-d-H-i-s") . ".txt";
        }
        if (empty($strFilePath)) {
            $strFilePath = APPPATH . 'logs/';
            // $strFilePath = './log/';
        }

        $strText = "\r\n";
        $strText .= date("Y-m-d H:i:s");
        $strText .= "\r\n--------------------------------------------------------\r\n";
        file_put_contents($strFilePath . $strFileName, $strText, FILE_APPEND);
        if ($blnIsPrintArray) {
            file_put_contents($strFilePath . $strFileName, print_r($mixData, 1), FILE_APPEND);
        } else {
            file_put_contents($strFilePath . $strFileName, json_encode($mixData), FILE_APPEND);
        }

        $strText = "\r\n=======================================================\r\n";
        file_put_contents($strFilePath . $strFileName, $strText, FILE_APPEND);
    } catch (Exception $exc) {
        echo $exc->getTraceAsString();
    }
}

function is_valid_json($strJSON, $isReturnData = false, $isArray = true)
{
    try {
        if ($isArray) {
            $data = json_decode($strJSON, true);
        } else {
            $data = json_decode($strJSON);
        }

        return (json_last_error() == JSON_ERROR_NONE) ? ($isReturnData ? $data : true) : false;
    } catch (Exception $ex) {
        throw $ex;
    }
}

function curl_request($url, $method = "GET", $params = '', $headers = array(), $isReturnTransfer = true, $isSSLVerify = false)
{
    try {
        $arrMethod = array('POST', 'GET');
        if (!in_array($method, $arrMethod)) {
            throw new Exception("Invalid method passed by user.");
        }
        if (!is_callable('curl_init')) {
            throw new Exception("cURL is not installed!");
        }
        if (!empty($params)) {
            if (!is_valid_json($params)) {
                throw new Exception("Invalid JSON: " . json_last_error_msg());
            }
        }
        $ch = curl_init();
        // Set the url
        curl_setopt($ch, CURLOPT_URL, $url);
        $arrHeader = array('Content-Type: application/json', 'x-li-format: json');
        if (!empty($headers)) {
            $arrHeader = array_merge($arrHeader, $headers);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $arrHeader);
        if ($method == "GET") {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        } else {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }

        // Set the result output to be a string.
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, $isReturnTransfer);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $isSSLVerify);

        $output = curl_exec($ch);
        if ($output === false) {
            $strMessage = "Error Number:" . curl_errno($ch) . "<br>";
            $strMessage .= "Error String:" . curl_error($ch);
            throw new Exception($strMessage);
        }
        curl_close($ch);

        $decoded = json_decode($output, 1);
        return $decoded;
    } catch (Exception $ex) {
        throw $ex;
    }
}

function addActivityLog($strTitle, $strDesc = "")
{
    try {
        $objCI = &get_instance();
        $objCI->load->model('activity_log_model');
        $frontuser                      = $objCI->session->userdata('frontuser');
        $arrData                        = array();
        $arrData['RegistrationId']      = $frontuser['registration_id'];
        $arrData['UserId']              = $frontuser['user_id'];
        $arrData['ActivityTitle']       = $strTitle;
        $arrData['ActivityDescription'] = $strDesc;
        $objCI->activity_log_model->Add($arrData);
    } catch (Exception $ex) {
        echo $ex->getMessage();
    }
}

function show_limited_words($text, $limit, $html = false)
{
    if (!$html) {
        $text = strip_tags($text);
    }

    if (str_word_count($text, 0) > $limit) {
        $words = str_word_count($text, 2);
        $pos   = array_keys($words);
        $text  = substr($text, 0, $pos[$limit]) . '...';
    }
    return $text;
}

//For get total MLS Agent(free + purchase)
function TotalMLSAgent()
{
    $CI           = &get_instance();
    $session_data = $CI->session->userdata('frontuser');
    //Get user details
    $CI->load->model('signup_model');
    $selected_fileds   = array();
    $selected_fileds[] = 'AdditionalMLSAgentPurchased';
    $selected_fileds[] = 'PlanId';
    $where_user_data   = "RegistrationId = " . $session_data['registration_id'];
    $user_detials      = $CI->signup_model->Get($where_user_data, $selected_fileds);
    $user_detials      = $user_detials[0];
    if ($user_detials['PlanId'] > 0) {
        //Get plan details
        $CI->load->model('plan_model');
        $selected_fileds   = array();
        $selected_fileds[] = 'MLSAgent';
        $where_plan        = "PlanId = " . $user_detials['PlanId'];
        $plan_details      = $CI->plan_model->Get($where_plan, $selected_fileds);
        $plan_details      = $plan_details[0];
        $TotalMLSAgent     = $user_detials['AdditionalMLSAgentPurchased'] + $plan_details['MLSAgent'];
    } else {
        $TotalMLSAgent = $user_detials['AdditionalMLSAgentPurchased'];
    }
    return $TotalMLSAgent;
}

//For get total data storage(free + purchase)
function TotalDataStorage($storage_type = 'GB')
{
    $CI           = &get_instance();
    $session_data = $CI->session->userdata('frontuser');
    //Get user details
    $CI->load->model('signup_model');
    $selected_fileds   = array();
    $selected_fileds[] = 'AdditionalStoragePurchased';
    $selected_fileds[] = 'PlanId';
    $where_user_data   = "RegistrationId = " . $session_data['registration_id'];
    $user_detials      = $CI->signup_model->Get($where_user_data, $selected_fileds);
    $user_detials      = $user_detials[0];
    if ($user_detials['PlanId'] > 0) {
        //Get plan details
        $CI->load->model('plan_model');
        $selected_fileds   = array();
        $selected_fileds[] = 'Storage';
        $selected_fileds[] = 'StorageType';
        $where_plan        = "PlanId = " . $user_detials['PlanId'];
        $plan_details      = $CI->plan_model->Get($where_plan, $selected_fileds);
        $plan_details      = $plan_details[0];
        if ($plan_details['StorageType'] == 'GB') {
            $DataStorage = $plan_details['Storage'] * 1024; // Convert GB to MB
        } else {
            $DataStorage = $plan_details['Storage'];
        }
        $TotalDataStorage = $user_detials['AdditionalStoragePurchased'] + $DataStorage;
        if ($storage_type == 'MB') {
            $TotalDataStorage = $TotalDataStorage;
        } else {
            $TotalDataStorage = $TotalDataStorage / 1024; //Convert MB to GB
        }
    } else {
        $TotalDataStorage = $user_detials['AdditionalStoragePurchased'];
        if ($storage_type == 'MB') {
            $TotalDataStorage = $TotalDataStorage;
        } else {
            $TotalDataStorage = $TotalDataStorage / 1024; //Convert MB to GB
        }
    }

    return round($TotalDataStorage, 2);
}

//For get total email account(free + purchase)
function TotalEmailAccount()
{
    $CI           = &get_instance();
    $session_data = $CI->session->userdata('frontuser');
    //Get user details
    $CI->load->model('signup_model');
    $selected_fileds   = array();
    $selected_fileds[] = 'AdditionalEmailAccountPurchased';
    $selected_fileds[] = 'PlanId';
    $where_user_data   = "RegistrationId = " . $session_data['registration_id'];
    $user_detials      = $CI->signup_model->Get($where_user_data, $selected_fileds);
    $user_detials      = $user_detials[0];
    if ($user_detials['PlanId'] > 0) {
        //Get plan details
        $CI->load->model('plan_model');
        $selected_fileds   = array();
        $selected_fileds[] = 'EmailAccount';
        $where_plan        = "PlanId = " . $user_detials['PlanId'];
        $plan_details      = $CI->plan_model->Get($where_plan, $selected_fileds);
        $plan_details      = $plan_details[0];

        $TotalEmailAccount = $user_detials['AdditionalEmailAccountPurchased'] + $plan_details['EmailAccount'];
    } else {
        $TotalEmailAccount = $user_detials['AdditionalEmailAccountPurchased'] + 1; //Free 1 email account for free trial user.
    }
    return $TotalEmailAccount;
}

//For get total users
function TotalUser()
{
    $CI           = &get_instance();
    $session_data = $CI->session->userdata('frontuser');
    //Get user details
    $CI->load->model('signup_model');
    $selected_fileds   = array();
    $selected_fileds[] = 'PlanId';
    $where_user_data   = "RegistrationId = " . $session_data['registration_id'];
    $user_detials      = $CI->signup_model->Get($where_user_data, $selected_fileds);
    $user_detials      = $user_detials[0];
    if ($user_detials['PlanId'] > 0) {
        //Get plan details
        $CI->load->model('plan_model');
        $selected_fileds   = array();
        $selected_fileds[] = 'User';
        $where_plan        = "PlanId = " . $user_detials['PlanId'];
        $plan_details      = $CI->plan_model->Get($where_plan, $selected_fileds);
        $plan_details      = $plan_details[0];
        $TotalUser         = $plan_details['User'];
    } else {
        $TotalUser = 0;
    }
    return $TotalUser;
}

//For get total used MLS Agent
function TotalMLSAgentUsed()
{
    $CI           = &get_instance();
    $session_data = $CI->session->userdata('frontuser');
    //Get total MLS Agent
    $CI->load->model('mls_agent_model');
    $where_data      = "RegistrationId = " . $session_data['registration_id'];
    $total_mls_agent = $CI->mls_agent_model->Get_total($where_data);
    return $total_mls_agent;
}

//For get total used email account
function TotalEmailAccountUsed()
{
    $CI           = &get_instance();
    $session_data = $CI->session->userdata('frontuser');
    //Get total email account
    $CI->load->model('nylas_integration_model');
    $where_data          = "RegistrationId = " . $session_data['registration_id'];
    $total_email_account = $CI->nylas_integration_model->Get_total($where_data);
    return $total_email_account;
}

//For get total used user
function TotalUserUsed()
{
    $CI = &get_instance();
    //Get total user
    $CI->load->model('member_model');
    $total_user = $CI->member_model->Total();
    return $total_user;
}

//For get total used data storage from amazon S3 bucket
function TotalDataStorageUsed($storage_type = 'GB')
{
    $CI           = &get_instance();
    $session_data = $CI->session->userdata('frontuser');
    $CI->load->library("Aws");
    //Create object for S3 bucket
    $S3 = S3Client::factory(array(
        'credentials' => array(
            'key'    => API_KEY_FOR_AWS_S3,
            'secret' => API_SECRET_FOR_AWS_S3,
        ),
    ));
    //Get folder size
    $objects = $S3->getIterator('ListObjects', array(
        "Bucket" => BUCKET_FOR_AWS_S3,
        "Prefix" => $session_data['DBname'],
    ));
    $size = 0;
    $i    = 0;
    foreach ($objects as $object) {
        $size = $size + $object['Size'];
        $i++;
    }
    if ($storage_type == 'MB') {
        return number_format($size / 1048576, 2); // For convert bytes to MB
    } else {
        return number_format($size / 1073741824, 2); // For convert bytes to GB
    }
}

//
function is_admin_user_Authorized()
{

    $CI = &get_instance();
    if ($CI->session->userdata('re_admin_available') && $CI->session->userdata['re_admin_available']['is__admin_logged_in'] == 1) {

    } else {
        redirect('admin');
    }
}

function recursiveRemoveDirectory($directoryPath)
{
    foreach (glob("{$directoryPath}/*") as $file) {
        if (is_dir($file)) {
            recursiveRemoveDirectory($file);
        } else {
            unlink($file);
        }
    }
    rmdir($directoryPath);
}

function send_email($to_email, $subject, $message = '', $alt_message = "", $arr_headers = [], $arr_attachments = [], $from_email = '', $to_name = '', $from_name = '', $reply_to = '', $reply_to_name = '', $cc = '', $bcc = '')
{
    $CI = &get_instance();
    $CI->load->library('email');

    $CI->email->clear();
    $CI->email->to($to_email);
    if (!empty($from_email)) {
        $CI->email->from($from_email, $from_name);
    }

    if (!empty($reply_to)) {
        $CI->email->reply_to($reply_to, $reply_to_name);
    }

    if (!empty($cc)) {
        $CI->email->cc($cc);
    }

    if (!empty($bcc)) {
        $CI->email->bcc($bcc);
    }

    if (!empty($alt_message)) {
        $CI->email->set_alt_message($alt_message);
    }

    if (!empty($arr_headers)) {
        foreach ($arr_headers as $key => $value) {
            $CI->email->set_header($key, $value);
        }
    }
    if (!empty($arr_attachments)) {
        foreach ($arr_attachments as $key => $value) {
            $CI->email->attach($value);
        }
    }
    $CI->email->subject($subject);
    $CI->email->message($message);

    return $CI->email->send();
}
