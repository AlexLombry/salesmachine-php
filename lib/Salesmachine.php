<?php
if (!function_exists('json_encode')) {
    throw new Exception('Salesmachine needs the JSON PHP extension.');
}
require(dirname(__FILE__) . '/Salesmachine/Client.php');

/**
 * Class Salesmachine
 */
class Salesmachine
{
    /**
     * @var
     */
    private static $client;

    /**
     * Initializes the default client to use. Uses the socket consumer by default.
     * @param $token
     * @param $secret
     * @param array $options
     * @throws Exception
     */
    public static function init($token, $secret, $options = array())
    {
        self::assert($token, "Salesmachine::init() requires token");
        self::assert($secret, "Salesmachine::init() requires secret");

        $options['batch_size'] = 100;

        if (isset($options['use_buffer'])) {
            $options['batch_size'] = $options['use_buffer'] ? 100 : 1;
        }

        self::$client = new Salesmachine_Client($token, $secret, $options);
    }

    /**
     * Sets a contact
     *
     * @param $contact_uid
     * @param array $message
     * @return mixed
     * @throws Exception
     */
    public static function set_contact($contact_uid, array $message = array())
    {
        self::checkClient();

        return self::$client->set_contact($contact_uid, $message);
    }

    /**
     * Sets an account
     *
     * @param $account_uid
     * @param array $message
     * @return mixed
     * @throws Exception
     */
    public static function set_account($account_uid, array $message = array())
    {
        self::checkClient();

        return self::$client->set_account($account_uid, $message);
    }

    /**
     * Tracks an event
     *
     * @param $contact_uid
     * @param $event_uid
     * @param array $message
     * @return mixed
     * @throws Exception
     */
    public static function track_event($contact_uid, $event_uid, array $message = array())
    {
        self::checkClient();

        return self::$client->track_event($contact_uid, $event_uid, $message);
    }

    /**
     * Tracks a pageview
     *
     * @param $contact_uid
     * @param array $message
     * @return mixed
     * @throws Exception
     */
    public static function track_pageview($contact_uid, array $message = array())
    {
        self::checkClient();

        return self::$client->track_pageview($contact_uid, $message);
    }

    /**
     * Validate common properties.
     *
     * @param $msg
     * @throws Exception
     */
    /*public static function validate($msg)
    {
        $userId = !empty($msg["contact_uid"]);
        self::assert($userId, "Salesmachine requires contact_uid for any request.");
    }*/

    /**
     * Flush the client
     */
    public static function flush()
    {
        self::checkClient();

        return self::$client->flush();
    }

    /**
     * Check the client.
     *
     * @throws Exception
     */
    private static function checkClient()
    {
        if (null != self::$client) return;
        throw new Exception("Salesmachine::init() must be called before any other tracking method.");
    }

    /**
     * Assert `value` or throw.
     *
     * @param array $value
     * @param string $msg
     * @throws Exception
     */
    private static function assert($value, $msg)
    {
        if (!$value) throw new Exception($msg);
    }
}