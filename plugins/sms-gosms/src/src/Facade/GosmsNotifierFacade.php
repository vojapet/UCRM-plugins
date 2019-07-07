<?php

declare(strict_types=1);

namespace SmsNotifier\Facade;

use SmsNotifier\Data\NotificationData;
use SmsNotifier\Data\PluginData;
use SmsNotifier\Factory\MessageTextFactory;
use SmsNotifier\Service\Logger;
use SmsNotifier\Service\OptionsManager;
use SmsNotifier\Service\SmsNumberProvider;
use SMS\GoSMS;

class GosmsNotifierFacade extends AbstractMessageNotifierFacade {

    /** @var Client */
    private $gosmsClient;

    /** @var PluginData */
    private $pluginData;

    public function __construct(
        Logger $logger,
        MessageTextFactory $messageTextFactory,
        SmsNumberProvider $smsNumberProvider,
        OptionsManager $optionsManager
    )
    {
        parent::__construct($logger, $messageTextFactory, $smsNumberProvider);
        // load config data
        $this->pluginData = $optionsManager->load();
    }


    /*
     * Get Gosms SMS API object (unless it's already initialized)
     */
    public function getGosmsClient()
    {
        if (!$this->gosmsClient) {
            $this->logger->info('Gosms client_id - ' . $this->pluginData->gosmsClientID);
            $this->logger->info('Gosms client_secret - ' . $this->pluginData->gosmsClientSecret);
            $this->gosmsClient = new GoSMS(
                $this->pluginData->gosmsClientID,
                $this->pluginData->gosmsClientSecret
            );
            $this->gosmsClient->authenticate();
            $this->logger->info('Gosms channel - ' . $this->pluginData->gosmsChannel);
            $this->gosmsClient->setChannel((int)$this->pluginData->gosmsChannel);
        }
        return $this->gosmsClient;
    }


    /*
     * Send message through the Gosms client
     */
    protected function sendMessage(
        NotificationData $notificationData,
        string $clientSmsNumber,
        string $messageBody
    ): void
    {
        $client = $this->getGosmsClient();
        $client->setMessage($messageBody);
        $client->setRecipient($clientSmsNumber);
        $response = $client->send();
        $this->logger->info('Gosms message sent to `' . $clientSmsNumber . '`');
    }

}
