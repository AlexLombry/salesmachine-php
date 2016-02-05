<?php

/**
 * Class Salesmachine_Consumer_ForkCurl
 */
class Salesmachine_Consumer_ForkCurl extends Salesmachine_QueueConsumer
{
    /**
     * @var string
     */
    protected $type = "ForkCurl";

    /**
     * @var array
     */
    protected $endpoint;

    /**
     * Salesmachine_Consumer_ForkCurl constructor.
     * Creates a new queued fork consumer which queues fork and identify
     * calls before adding them to
     *
     * @param $token
     * @param string $secret
     * @param array $endpoint
     * @param array $options
     *        boolean  "debug" - whether to use debug output, wait for response.
     *        number   "max_queue_size" - the max size of messages to enqueue
     *        number   "batch_size" - how many messages to send in a single request
     */
    public function __construct($token, $secret, $endpoint, $options = array())
    {
        $this->endpoint = $endpoint;
        parent::__construct($token, $secret, $options);
    }

    /**
     * Make an async request to our API. Fork a curl process, immediately send
     * to the API. If debug is enabled, we wait for the response.
     *
     * @param $messages array of all the messages to send
     * @return bool whether the request succeeded
     */
    public function flushBatch($messages)
    {
        $body = $this->payload($messages);
        $payload = json_encode($body);

        try {
            // init curl, send data and headers
            $ch = curl_init($this->generateApiUrl());
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

            // Send the request & save response to $response
            $response = curl_exec($ch);
            curl_close($ch);

            if ($this->debug()) {
                var_dump(curl_getinfo($ch));
            }

            return $response;
        } catch (Exception $e) {
            $this->handleError($e->getCode(), $e->getMessage());

            return false;
        }
    }

    /**
     * Generate api url base on ssl and host data
     *
     * @return string
     */
    private function generateApiUrl()
    {
        $protocol = $this->ssl() ? "https://" : "http://";
        $id = $this->token . ":" . $this->secret . "@";
        $host = $this->host();
        $path = "/v1/" . $this->endpoint;

        return $protocol . $id . $host . $path;
    }
}