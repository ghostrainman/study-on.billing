<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AuthControllerTest extends WebTestCase
{
    public function testRegistration(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/v1/register', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'username' => 'testim@test.com',
            'password' => '123456'
        ]));

        $this->assertResponseStatusCodeSame(201);

        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('token', $data);
        $this->assertArrayHasKey('username', $data);
        $this->assertEquals('testim@test.com', $data['username']);
    }

    public function testRegistrationWithExistingEmail(): void
    {
        $client = static::createClient();

        // Регистрация первого пользователя
        $client->request('POST', '/api/v1/register', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'username' => 'testim@test.com',
            'password' => '123456'
        ]));

        // Повторная регистрация с тем же email
        $client->request('POST', '/api/v1/register', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'username' => 'testim@test.com',
            'password' => '1234567'
        ]));

        $this->assertResponseStatusCodeSame(400);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
    }

    public function testLogin(): void
    {
        $client = static::createClient();

        // Предварительная регистрация пользователя
        $client->request('POST', '/api/v1/register', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'username' => 'testim@test.com',
            'password' => '123456'
        ]));

        // Логинимся
        $client->request('POST', '/api/v1/auth', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'username' => 'testim@test.com',
            'password' => '123456'
        ]));

        $this->assertResponseIsSuccessful();

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('token', $data);
    }

    public function testLoginWithInvalidCredentials(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/v1/auth', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'username' => 'testim@test.com',
            'password' => '123456'
        ]));

        $this->assertResponseStatusCodeSame(401);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $data);
    }

    public function testCurrentUserWithToken(): void
    {
        $client = static::createClient();

        // Регистрация
        $client->request('POST', '/api/v1/register', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'username' => 'testim@test.com',
            'password' => '123456'
        ]));

        $data = json_decode($client->getResponse()->getContent(), true);
        $token = $data['token'];

        // Доступ к текущему пользователю с токеном
        $client->request('GET', '/api/v1/users/current', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

        $this->assertResponseIsSuccessful();

        $userData = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('testim@test.com', $userData['username']);
    }

    public function testCurrentUserWithoutToken(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/v1/users/current');

        $this->assertResponseStatusCodeSame(401);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $data);
    }
}
