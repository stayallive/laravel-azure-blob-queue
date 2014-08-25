<?php

namespace Stayallive\LaravelAzureBlobQueue;

use Illuminate\Queue\Queue;
use Illuminate\Queue\QueueInterface;
use WindowsAzure\Queue\QueueRestProxy;
use WindowsAzure\Queue\Models\CreateMessageOptions;
use WindowsAzure\Queue\Models\PeekMessagesOptions;

class AzureQueue extends Queue implements QueueInterface {

    /**
     * The Azure IServiceBus instance.
     *
     * @var \WindowsAzure\Queue\QueueRestProxy
     */
    protected $azure;

    /**
     * The name of the default queue.
     *
     * @var string
     */
    protected $default;

    /**
     * Create a new Azure IQueue queue instance.
     *
     * @param \WindowsAzure\Queue\QueueRestProxy $azure
     * @param  string                            $default
     *
     * @return \Stayallive\LaravelAzureBlobQueue\AzureQueue
     */
    public function __construct(QueueRestProxy $azure, $default) {
        $this->azure   = $azure;
        $this->default = $default;
    }

    /**
     * Push a new job onto the queue.
     *
     * @param  string $job
     * @param  mixed  $data
     * @param  string $queue
     *
     * @return void
     */
    public function push($job, $data = '', $queue = null) {
        $this->pushRaw($this->createPayload($job, $data), $queue);
    }

    /**
     * Push a raw payload onto the queue.
     *
     * @param  string $payload
     * @param  string $queue
     * @param  array  $options
     *
     * @return mixed
     */
    public function pushRaw($payload, $queue = null, array $options = array()) {
        $this->azure->createMessage($this->getQueue($queue), $payload);
    }

    /**
     * Push a new job onto the queue after a delay.
     *
     * @param  int    $delay
     * @param  string $job
     * @param  mixed  $data
     * @param  string $queue
     *
     * @return void
     */
    public function later($delay, $job, $data = '', $queue = null) {
        $payload = $this->createPayload($job, $data);

        $options = new CreateMessageOptions;
        $options->setVisibilityTimeoutInSeconds($delay);

        $this->azure->createMessage($this->getQueue($queue), $payload, $options);
    }

    /**
     * Pop the next job off of the queue.
     *
     * @param  string $queue
     *
     * @return \Illuminate\Queue\Jobs\Job|null
     */
    public function pop($queue = null) {
        $queue = $this->getQueue($queue);

        $result = $this->azure->peekMessages($queue, new PeekMessagesOptions);

        $messages = $result->getQueueMessages();

        if (count($messages) > 0) {
            return new AzureJob($this->container, $this->azure, $messages[0], $queue);
        }

        return null;
    }

    /**
     * Get the queue or return the default.
     *
     * @param  string|null $queue
     *
     * @return string
     */
    public function getQueue($queue) {
        return $queue ?: $this->default;
    }

    /**
     * Get the underlying Azure IQueue instance.
     *
     * @return \WindowsAzure\Queue\Internal\IQueue
     */
    public function getAzure() {
        return $this->azure;
    }

}