<?php
require(__DIR__ . '/Consumer.php');
require(__DIR__ . '/QueueConsumer.php');
require(__DIR__ . '/Consumer/File.php');
require(__DIR__ . '/Consumer/ForkCurl.php');

/**
 * Class Salesmachine_Client
 */
class Salesmachine_Client
{

    /**
     * VERSION
     */
    const VERSION = "1.0.2";

    /**
     * @var
     */
    private $consumer;

    /**
     * @var
     */
    private $token;

    /**
     * @var
     */
    private $mode;

    /**
     * Salesmachine_Client constructor.
     * Create a new analytics object with your app's secret key
     *
     * @param $token
     * @param $secret
     * @param array $options
     */
    public function __construct($token, $secret, $options = array())
    {
        $consumers = array(
            "file" => "Salesmachine_Consumer_File",
            "fork_curl" => "Salesmachine_Consumer_ForkCurl",
        );

        # Use our curl single-request consumer by default
        $consumer_type = isset($options["consumer"])
            ? $options["consumer"]
            : "fork_curl";

        $Consumer = $consumers[$consumer_type];

        $this->consumer = new $Consumer($token, $secret, "batch", $options);

        $this->token = $token;
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->consumer->__destruct();
    }

    /**
     * Sets a contact
     *
     * @param $contact_uid
     * @param array $message
     * @return mixed whether the track call succeeded
     */
    public function set_contact($contact_uid, array $message = array())
    {
        $data = array();
        $data['contact_uid'] = $contact_uid;
        $data['params'] = $message;
        $data['method'] = 'contact';
        return $this->consumer->set_contact($this->message($data));
    }

    /**
     * Sets an account
     *
     * @param $account_uid
     * @param array $message
     * @return mixed whether the track call succeeded
     */
    public function set_account($account_uid, array $message = array())
    {
        $data = array();
        $data['account_uid'] = $account_uid;
        $data['params'] = $message;
        $data['method'] = 'account';

        return $this->consumer->set_account($this->message($data));
    }

    /**
     * Tracks an event
     *
     * @param $contact_uid
     * @param $event_uid
     * @param array $message
     * @return mixed whether the track call succeeded
     */
    public function track_event($contact_uid, $event_uid, array $message = array())
    {
        $data = array();
        $data['contact_uid'] = $contact_uid;
        $data['event_uid'] = $event_uid;
        $data['params'] = $message;
        $data['method'] = 'event';

        return $this->consumer->track_event($this->message($data));
    }

    /**
     * Tracks a pageview
     *
     * @param $contact_uid
     * @param array $message
     * @return mixed
     */
    public function track_pageview($contact_uid, array $message = array())
    {
        $data = array();
        $data['contact_uid'] = $contact_uid;
        $data['event_uid'] = "pageview";
        $data['params'] = $message;
        $data['method'] = 'event';

        return $this->consumer->track_event($this->message($data));
    }


    /**
     * Flush any async consumers
     *
     * @return bool
     */
    public function flush()
    {
        if (!method_exists($this->consumer, 'flush')) {
            return false;
        }

        return $this->consumer->flush();
    }

    /**
     * Formats a timestamp by making sure it is set
     * and converting it to iso8601.
     *
     * The timestamp can be time in seconds `time()` or `microseconds(true)`.
     * any other input is considered an error and the method will return a new date.
     *
     * Note: php's date() "u" format (for microseconds) has a bug in it
     * it always shows `.000` for microseconds since `date()` only accepts
     * ints, so we have to construct the date ourselves if microtime is passed.
     *
     * @param $ts
     * @return bool|string
     */
    private function formatTime($ts)
    {
        // time()
        if ($ts == null) $ts = time();
        if (is_integer($ts)) return date("c", $ts);

        // anything else return a new date.
        if (!is_float($ts)) return date("c");

        // fix for floatval casting in send.php
        $parts = explode(".", (string)$ts);
        if (!isset($parts[1])) return date("c", (int)$parts[0]);

        // microtime(true)
        $sec = (int)$parts[0];
        $usec = (int)$parts[1];
        $fmt = sprintf("Y-m-d\TH:i:s%sP", $usec);
        return date($fmt, (int)$sec);
    }

    /**
     * Add common fields to the given `message`
     *
     * @param $msg
     * @param string $def
     * @return mixed
     */
    private function message($msg, $def = "")
    {
        /* To define later eventually*/
        //$created_at = $this->formatTime(null);
        return $msg;
    }

    /**
     * Generate a random messageId.
     * https://gist.github.com/dahnielson/508447#file-uuid-php-L74
     *
     * @return string
     */
    private static function messageId()
    {
        return sprintf("%04x%04x-%04x-%04x-%04x-%04x%04x%04x"
            , mt_rand(0, 0xffff)
            , mt_rand(0, 0xffff)
            , mt_rand(0, 0xffff)
            , mt_rand(0, 0x0fff) | 0x4000
            , mt_rand(0, 0x3fff) | 0x8000
            , mt_rand(0, 0xffff)
            , mt_rand(0, 0xffff)
            , mt_rand(0, 0xffff));
    }

    /**
     * Add the Salesmachine.io context to the request
     *
     * @return array additional context
     */
    private function getContext()
    {
        return array(
            "library" => array(
                "name" => "analytics-php",
                "version" => self::VERSION
            )
        );
    }
}
