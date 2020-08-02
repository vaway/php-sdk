<?php
namespace VAWAY\Api;
/**
 *  ------------------------------------------------------------------------
 *  
 *  Open Source CodeIgniter Vaid Library (ALL-IN-ONE).
 *
 *  This library is based on a brand new PHP 7 to work with CodeIgniter 
 *  since version 3 and MongoDB since version 3.2. I have implemented the 
 *  following main functions MongoDB:  select, insert, update, delete and 
 *  aggregate documents; execute commands.
 *  
 *  @author   SinhThanh <sinhthanh.dev@gmail.com>
 *  @since    14.10.2016
 *  @license  MIT License
 *  
 *  @version  1.1.0
 *  @link     
 *
 *  ------------------------------------------------------------------------
 *
 *  NOTE: I express my gratitude to Alexander (@link https://github.com/Alexter-progs) for the translation into English.
 *
 *  ------------------------------------------------------------------------
 */


class VAid
{

    /**
     *  Default config parameters.
     *
     *  During library initialization and reconnection arguments
     *  recursively replaced with passed to method accordingly to priority type:
     *  
     *  1) High priority - data passed during [__construct()], to methods [connect()] and [reconnect()].
     *  2) Medium priority - data from config file [/config/mongo_db.php].
     *  3) Low priority - values specified as default for class parameters
     */


    private $config = [
        'app_id'          => '',
        'app_key'          => '',
        'app_secret'          => '',
        'debug' => false
    ];

    private $endpoint = 'https://id.vaway.vn/api/v1/';

    /**
     *  Format in which query results should be presented.
     *  
     *  @var 'array' or 'object'
     */
    private $return_as = 'array';

    /**
     *  Library constructor. Accepts array with two arguments: config_group and config.
     *  
     *  1) Argument config_group contains name of used group in config file.
     *  2) Argument config may contain data like in group from config file:
     *     settings, connection_string, connection, driver and etc.
     *
     *  @param  array  $config  [Group name and config parameters]
     *  
     *  @uses   $this->load->library('mongo_db', ['config' => ['connection_string' => 'localhost:27015']]);
     */
    function __construct(array $config = [])
    {
        if (isset($config['app_id']) == false || isset($config['app_key']) == false || isset($config['app_secret']) == false) {
            $this->error('Config item app_id && app_key && app_secret is required');
        }
    }

    public function profile($access_token)
    {
        $args = [
            'method' => 'GET',
            'endpoint' => 'account/profile?access_token=' . $access_token
        ];

        return $this->request($args);
    }


    public function upgrade(array $args = [])
    {

        $args = [
            'method' => 'POST',
            'endpoint' => 'stats/upgrade',
            'data' => $args
        ];

        return $this->request($args);
    }

    /**
     * for partner
     */

    public function get_settings()
    {
        $args = [
            'method' => 'GET',
            'endpoint' => 'partner/setting'
        ];
        return $this->request($args);
    }


    public function settings(array $args = [])
    {

        $logo = (isset($args['logo']) ? $args['logo'] : "");
        $name = (isset($args['name']) ? $args['name'] : 0);
        $signup = (isset($args['signup']) ? $args['signup'] : true);
        $signin = (isset($args['signin']) ? $args['signin'] : true);
        $upgrade = (isset($args['upgrade']) ? $args['upgrade'] : ["main", "free"]);

        $args = [
            'method' => 'POST',
            'endpoint' => 'partner/setting',
            'data' => [
                "logo" => $logo,
                "name" => $name,
                "signup" => $signup,
                "signin" => $signin,
                "upgrade" => $upgrade
            ]
        ];
        return $this->request($args);
    }


    /**
     * for package
     */
    public function get_packages()
    {
        $args = [
            'method' => 'GET',
            'endpoint' => 'partner/packages'
        ];
        return $this->request($args);
    }

    public function save_packages($package_id = 0, array $args = [])
    {
        $name = (isset($args['name']) ? $args['name'] : "");
        $price_regular = (isset($args['price_regular']) ? $args['price_regular'] : "");
        $price_sale = (isset($args['price_sale']) ? $args['price_sale'] : "");
        $status = (isset($args['status']) ? $args['status'] : "");
        $value = (isset($args['value']) ? $args['value'] : "");

        $args = [
            'method' => 'POST',
            'endpoint' => 'partner/packages/' . $package_id,
            'data' => [
                "price_regular" => $price_regular,
                "name" => $name,
                "price_sale" => $price_sale,
                "status" => $status,
                "value" => $value
            ]
        ];
        return $this->request($args);
    }

    public function delete_packages($package_id = 0)
    {
        $args = [
            'method' => 'DELETE',
            'endpoint' => 'partner/packages' . $package_id
        ];

        return $this->request($args);
    }

    /**
     * for curl request
     */
    public function request(array $args = [])
    {
        $endpoint = (isset($args['endpoint']) ? $args['endpoint'] : ""); //endpoint for api

        if ($this->config['debug']) $endpoint .= '?sandbox=1&app_id=' . $this->config['app_id']; //if debug mode

        $method = (isset($args['method']) ? $args['method'] : "POST");

        $curl = curl_init();

        $config = array(
            CURLOPT_URL => $this->endpoint . $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => array(
                "Authorization: key=" . md5($this->config['app_id'] . '|' . $this->config['app_key'] . '|' . $this->config['app_secret']),
                "Content-Type: application/x-www-form-urlencoded"
            ),
        );

        switch ($method) {
            case 'value':
                # for post method ...
                $config['CURLOPT_POSTFIELDS'] = (isset($args['data']) ? $args['data'] : "");
                break;
            default:
                # code...
                break;
        }

        curl_setopt_array($curl, $config);

        $response = curl_exec($curl);

        if (curl_exec($curl) === false) {
            if ($response) {
                return json_decode($response, true);
            } else {
                return false;
            }
        } else {
            return false;
        }

        curl_close($curl);
    }

    ///////////////////////////////////////////////////////////////////////////
    //
    //  ERROR handler.
    //
    ///////////////////////////////////////////////////////////////////////////

    /**
     *  Error handler.
     *  
     *  @param   string   $text  [Error message]
     *  @param   integer  $code  [Error code]
     *  @return  $this
     */
    private function error(string $text = '', string $method = '', int $code = 500): self
    {
        // Log errors.
        $message = $text;

        // Show method where error occurred.
        if ($method != '') {
            $message = "{$method}(): $message";
        }
        $error = [
            "success" => false,
            "message" => $message
        ];
        die(json_encdoe($error));
    }
}
