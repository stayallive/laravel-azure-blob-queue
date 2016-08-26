<?php

namespace Stayallive\LaravelAzureBlobQueue;

use WindowsAzure\Common\ServicesBuilder;
use Illuminate\Queue\Connectors\ConnectorInterface;

class AzureConnector implements ConnectorInterface
{

    /**
     * Establish a queue connection.
     *
     * @param  array $config
     *
     * @return \Illuminate\Queue\QueueInterface
     */
    public function connect(array $config)
    {
        $connectionString = 'DefaultEndpointsProtocol=' . $config['protocol'] . ';AccountName=' . $config['accountname'] . ';AccountKey=' . $config['key'];
        $queueRestProxy   = ServicesBuilder::getInstance()->createQueueService($connectionString);

        return new AzureQueue($queueRestProxy, $config['queue']);
    }
}
