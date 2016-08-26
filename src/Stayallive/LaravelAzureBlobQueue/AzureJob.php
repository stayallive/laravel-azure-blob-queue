<?php

namespace Stayallive\LaravelAzureBlobQueue;

use Illuminate\Queue\Jobs\Job;
use Illuminate\Container\Container;
use WindowsAzure\Queue\QueueRestProxy;
use WindowsAzure\Queue\Models\WindowsAzureQueueMessage;

class AzureJob extends Job
{

    /**
     * The Azure QueueRestProxy instance.
     *
     * @var \WindowsAzure\Queue\QueueRestProxy
     */
    protected $azure;

    /**
     * The Azure WindowsAzureQueueMessage instance.
     *
     * @var \WindowsAzure\Queue\Models\WindowsAzureQueueMessage
     */
    protected $job;

    /**
     * The queue that the job belongs to.
     *
     * @var string
     */
    protected $queue;

    /**
     * Create a new job instance.
     *
     * @param \Illuminate\Container\Container                     $container
     * @param \WindowsAzure\Queue\QueueRestProxy                  $azure
     * @param \WindowsAzure\Queue\Models\WindowsAzureQueueMessage $job
     * @param  string                                             $queue
     *
     * @return \Stayallive\LaravelAzureBlobQueue\AzureJob
     */
    public function __construct(Container $container, QueueRestProxy $azure, WindowsAzureQueueMessage $job, $queue)
    {
        $this->azure     = $azure;
        $this->job       = $job;
        $this->queue     = $queue;
        $this->container = $container;
    }

    /**
     * Fire the job.
     *
     * @return void
     */
    public function fire()
    {
        $this->resolveAndFire(json_decode($this->getRawBody(), true));
    }

    /**
     * Delete the job from the queue.
     *
     * @return void
     */
    public function delete()
    {
        $this->azure->deleteMessage($this->queue, $this->job->getMessageId(), $this->job->getPopReceipt());
    }

    /**
     * Release the job back into the queue.
     *
     * @param  int $delay
     *
     * @return void
     */
    public function release($delay = 0)
    {
        $this->azure->updateMessage($this->queue, $this->job->getMessageId(), $this->job->getPopReceipt(), null, $delay);
    }

    /**
     * Get the number of times the job has been attempted.
     *
     * @return int
     */
    public function attempts()
    {
        return $this->job->getDequeueCount();
    }

    /**
     * Get the IoC container instance.
     *
     * @return \Illuminate\Container\Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Get the underlying Azure client instance.
     *
     * @return \WindowsAzure\Queue\QueueRestProxy
     */
    public function getAzure()
    {
        return $this->azure;
    }

    /**
     * Get the underlying raw Azure job.
     *
     * @return \WindowsAzure\Queue\Models\WindowsAzureQueueMessage
     */
    public function getAzureJob()
    {
        return $this->job;
    }

    /**
     * Get the raw body string for the job.
     *
     * @return string
     */
    public function getRawBody()
    {
        return $this->job->getMessageText();
    }
}
