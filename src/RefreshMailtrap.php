<?php

namespace Jtproductions\MailtrapAssertions;

use GuzzleHttp\Client;

trait RefreshMailtrap
{
    private $client;
    private string $inboxID;
    private string $apiKey;

    public function refreshMailtrap()
    {
        $this->apiKey = env('MAILTRAP_API_KEY');
        $this->inboxID = env('MAILTRAP_INBOX_ID');

        $this->requestClient()->patch($this->getMailtrapCleanUrl());
    }

    protected function requestClient()
    {
        if (!$this->client) {
            $this->client = new Client([
                'base_uri' => "https://mailtrap.io",
                'headers'  => ['Api-Token' => $this->apiKey],
            ]);
        }

        return $this->client;
    }

    private function getMailtrapCleanUrl()
    {
        return "/api/v1/inboxes/{$this->inboxID}/clean";
    }
}
