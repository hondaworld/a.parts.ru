<?php

namespace App\Tests\Functional\Home;

use App\Security\ManagerProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class IndexTest extends WebTestCase
{
    public function testAuth(): void
    {

        $client = static::createClient();
        $crawler = $client->request('GET', 'http://a.parts.test/login');

        // Validate a successful response and some content
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h3', 'Авторизация');
    }

    public function testIndex(): void
    {

        $client = static::createClient();

        $managerFetcher = static::$container->get(ManagerProvider::class);
        $testUser = $managerFetcher->loadUserByUsername('admin');

        // simulate $testUser being logged in
        $client->loginUser($testUser);

        $crawler = $client->request('GET', 'http://a.parts.test/');

        // Validate a successful response and some content
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Главная');
    }
}