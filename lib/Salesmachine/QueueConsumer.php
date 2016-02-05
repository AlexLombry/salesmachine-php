<?php

/**
 * Class Salesmachine_QueueConsumer
 */
abstract class Salesmachine_QueueConsumer extends Salesmachine_Consumer
{
    /**
     * @var string
     */
    protected $type = "QueueConsumer";

    /**
     * @var array
     */
    protected $queue;

    /**
     * @var int
     */
    protected $max_queue_size = 1000;

    /**
     * @var int
     */
    protected $batch_size = 100;

    /**
     * Salesmachine_QueueConsumer constructor.
     * Store our secret and options as part of this consumer
     *
     * @param $token
     * @param string $secret
     * @param array $options
     */
    public function __construct($token, $secret, $options = array())
    {
        parent::__construct($token, $secret, $options);

        if (isset($options["max_queue_size"])) {
            $this->max_queue_size = $options["max_queue_size"];
        }

        if (isset($options["batch_size"])) {
            $this->batch_size = $options["batch_size"];
        }

        $this->queue = array();
    }

    /**
     * Flush our queue on destruction
     */
    public function __destruct()
    {
        $this->flush();
    }

    /**
     * Sets a contact
     *
     * @param  array $message
     * @return boolean whether the track call succeeded
     */
    public function set_contact(array $message)
    {
        return $this->enqueue($message);
    }

    /**
     * Sets an account
     *
     * @param  array $message
     * @return boolean whether the track call succeeded
     */
    public function set_account(array $message)
    {
        return $this->enqueue($message);
    }

    /**
     * Tracks an event
     *
     * @param  array $message
     * @return boolean whether the track call succeeded
     */
    public function track_event(array $message)
    {
        return $this->enqueue($message);
    }

    /**
     * Tracks a pageview event
     *
     * @param  array $message
     * @return boolean whether the track call succeeded
     */
    public function track_pageview(array $message)
    {
        return $this->enqueue($message);
    }


    /**
     * Adds an item to our queue.
     *
     * @param  mixed $item
     * @return boolean whether the queue has room
     */
    protected function enqueue($item)
    {
        $count = count($this->queue);

        if ($count > $this->max_queue_size) {
            return false;
        }

        $count = array_push($this->queue, $item);

        if ($count > $this->batch_size) {
            $this->flush();
        }

        return true;
    }

    /**
     * Flushes our queue of messages by batching them to the server
     *
     * @return bool
     */
    public function flush()
    {
        $count = count($this->queue);
        $success = true;

        while ($count > 0 && $success) {

            $batch = array_splice($this->queue, 0, min($this->batch_size, $count));
            $success = $this->flushBatch($batch);

            $count = count($this->queue);
        }

        return $success;
    }

    /**
     * Given a batch of messages the method returns
     * a valid payload.
     *
     * @param $batch
     * @return mixed
     */
    protected function payload($batch)
    {
        return $batch;
        /*
        TO MODIFY WHEN BULK MODE IS ACTIVATED
        return array(
          "batch" => $batch,
          "sentAt" => date("c"),
        );*/
    }

    /**
     * Flushes a batch of messages.
     *
     * @param $batch
     * @return mixed
     */
    abstract function flushBatch($batch);
}
