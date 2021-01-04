<?php

namespace Jtproductions\MailtrapAssertions;

use GuzzleHttp\Client;
use Illuminate\Testing\Assert;
use http\Exception\RuntimeException;

trait MailtrapAssertions
{
    private $client;
    private $mailtrapInbox;
    private $mailtrapApiKey;

    /**
     *
     */
    public function mailtrapSetup()
    {
        $this->mailtrapApiKey = env('MAILTRAP_API_KEY');
        $this->mailtrapInbox = env('MAILTRAP_INBOX_ID');
    }

    /**
     * Empties your mailbox
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function emptyMailbox()
    {
        $this->mailtrapClient()->patch($this->getMailboxCleanUrl());
    }

    /**
     * @return Client
     */
    private function mailtrapClient(): Client
    {
        if (!$this->client) {
            $this->client = new Client([
                'base_uri' => "https://mailtrap.io",
                'headers'  => ['Api-Token' => $this->mailtrapApiKey],
            ]);
        }

        return $this->client;
    }

    /**
     * Returns the url to clean the inbox
     *
     * @return string
     */
    private function getMailboxCleanUrl(): string
    {
        return "/api/v1/inboxes/{$this->mailtrapInbox}/clean";
    }

    /**
     * Checks if the mailtrap inbox is empty
     */
    protected function assertMailboxEmpty()
    {
        return $this->assertMailboxCount(0);
    }

    /**
     * Checks if the mailtrap inbox has $count messages
     *
     * @param  int  $count
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function assertMailboxCount(int $count)
    {
        $data = $this->mailtrapClient()->get($this->getMailtrapMessagesUrl());
        $mailboxCount = count($this->parseJson($data->getBody()));

        return Assert::assertEquals($count, $mailboxCount);
    }

    /**
     * Returns the string to the messages of the inbox
     *
     * @return string
     */
    private function getMailtrapMessagesUrl()
    {
        return "/api/v1/inboxes/{$this->mailtrapInbox}/messages";
    }

    /**
     * Parse the json API feedback
     *
     * @param $body
     *
     * @return array|mixed
     */
    private function parseJson($body)
    {
        $data = json_decode((string)$body, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new RuntimeException('Unable to parse response body into JSON: '.json_last_error());
        }

        return $data === null ? [] : $data;
    }
}
