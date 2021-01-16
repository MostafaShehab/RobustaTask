<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use DatabaseMigrations;

    public function testRequiredFieldsForRegistration()
    {
        $this->json('POST', 'api/register', ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJson([
                "success" => false,
                "message" => "Validation Error.",
                "data" => [
                    "name" => ["The name field is required."],
                    "email" => ["The email field is required."],
                    "password" => ["The password field is required."],
                    "c_password" => ["The c password field is required."]
                ]
            ]);
    }
    
    public function testUnsuccessfulRegistrationMissingInput()
    {
        $userData = [
            "name" => "John Doe",
            "email" => "doe@example.com",
            "password" => "demo12345"
        ];

        $response = $this->json('POST', 'api/register', $userData, ['Accept' => 'application/json']);

        $response->assertStatus(422)
            ->assertJsonStructure([
                "success",
                "message",
                "data"
            ])
            ->assertJson([
                "data" => ["c_password" => ["The c password field is required."]]
            ]);
    }
    
    public function testSuccessfulRegistration()
    {
        $userData = [
            "name" => "John Doe",
            "email" => "doe@example.com",
            "password" => "demo12345",
            "c_password" => "demo12345"
        ];

        $response = $this->json('POST', 'api/register', $userData, ['Accept' => 'application/json']);

        $response->assertStatus(201)
            ->assertJsonStructure([
                "success",
                "data" => [
                    '*' => [
                        'token',
                        'name'
                    ]
                ],
                "message"
            ])
            ->assertJson([
                "data" => [
                    ['name' => "John Doe"]
                ]
            ]);
    }
    
    public function testSuccessfulLogin() {

        $userData = [
            "name" => "John Doe",
            "email" => "doe@example.com",
            "password" => "demo12345",
            "c_password" => "demo12345"
        ];

        $response = $this->json('POST', 'api/register', $userData, ['Accept' => 'application/json']);
        
        $userData = [
            "email" => "doe@example.com",
            "password" => "demo12345"
        ];

        $response = $this->json('POST', 'api/login', $userData, ['Accept' => 'application/json']);

        $response->assertStatus(200);

    }

    public function testUnsuccessfulLogin() {

        $userData = [
            "name" => "John Doe",
            "email" => "doe@example.com",
            "password" => "demo12345",
            "c_password" => "demo12345"
        ];

        $response = $this->json('POST', 'api/register', $userData, ['Accept' => 'application/json']);
        
        $userData = [
            "email" => "doe@example.com",
            "password" => "password"
        ];

        $response = $this->json('POST', 'api/login', $userData, ['Accept' => 'application/json']);
        
        $response->assertStatus(401)
            ->assertJsonStructure([
                "success",
                "message",
                "data"
            ])
            ->assertJson([
                "data" => ["error" => "Unauthorised"]
            ]);

    }
    
}
