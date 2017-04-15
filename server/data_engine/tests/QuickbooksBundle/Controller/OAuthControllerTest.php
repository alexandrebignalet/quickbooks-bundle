<?php

namespace Tests\QuickbooksBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class OAuthControllerTest extends WebTestCase
{
    public function testOAuthConnectionPage()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/disconnect');
        $crawler = $client->request('GET', '/oauth_connection');

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("You are not currently authenticated!")')->count()
        );
    }
}