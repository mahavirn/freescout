<?php

namespace Tests\Unit;

use Symfony\Component\Mime\Email;
use Tests\TestCase;

/**
 * APP_CUSTOM_MAIL_HEADERS="IsTransactional:True;Reply-To:noreply@example.org"
 */
class CustomMailHeadersTest extends TestCase
{
    public function testCustomHeadersAreAdded()
    {
        config(['app.custom_mail_headers' => 'IsTransactional:True; Reply-To:noreply@example.org;Return-Path:bounce@example.org;Invalid']);

        $message = (new Email())->from('support@example.com')->to('customer@example.org')->text('Test');
        \MailHelper::addCustomHeaders($message);

        $this->assertEquals('True', $message->getHeaders()->get('IsTransactional')->getBodyAsString());
        $this->assertEquals('noreply@example.org', $message->getReplyTo()[0]->getAddress());
        $this->assertEquals('bounce@example.org', $message->getReturnPath()->getAddress());
        // Message can be built.
        $this->assertStringContainsString('IsTransactional: True', $message->toString());
    }
}
