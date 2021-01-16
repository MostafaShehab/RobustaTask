<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SeatsStopsControllerTest extends TestCase
{
    use DatabaseMigrations;
    
    public function testUnauthenticatedFreeSeats() {

        // No user login therefore this request is unauthenticated
        $response = $this->json('GET', 'api/freaseats/Cairo/Asyut', [], ['Accept' => 'application/json']);
        $response->assertStatus(401)
            ->assertJson([
                'message' => "Unauthenticated."
            ]);

    }
    
    public function testAuthenticatedSuccessfullFreeSeats() {

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

        // Populate DB with mock data (for convenience it is the same data in the dump file)
        $this->populateDatabase();

        // Access token automatically sent with this request since it is saved in session
        $response = $this->json('GET', 'api/freaseats/Cairo/Asyut', [], ['Accept' => 'application/json']);

        $response->assertStatus(200)
            ->assertJsonStructure([
                "success",
                "data" => [
                    "*" => [
                        'id',
                        'name',
                        'order_of_stop',
                        'trip_id',
                        'created_at',
                        'updated_at',
                        'seat_id',
                        'stop_id',
                        'is_booked',
                        'booking_user_id',
                        'dest_order'
                    ]
                ],
                "message"
            ]);

    }

    public function testFreeSeatsIncorrectStops() {

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

        // Populate DB with mock data (for convenience it is the same data in the dump file)
        $this->populateDatabase();

        // Access token automatically sent with this request since it is saved in session
        $response = $this->json('GET', 'api/freaseats/Giza/Asyut', [], ['Accept' => 'application/json']);

        $response->assertStatus(422)
            ->assertJson([
                "success" => false,
                "message" => "No such stops exist"
            ]);

    }

    public function testUnauthenticatedBookSeats() {

        // No user login therefore this request is unauthenticated
        $response = $this->json('POST', 'api/book/1/1/2/3/1', [], ['Accept' => 'application/json']);
        $response->assertStatus(401)
            ->assertJson([
                'message' => "Unauthenticated."
            ]);

    }

    public function testAuthenticatedSuccessfulBookSeats() {

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

        // Populate DB with mock data (for convenience it is the same data in the dump file)
        $this->populateDatabase();

        // Access token automatically sent with this request since it is saved in session

        // Seats are not booked so this API call succeeds
        $response = $this->json('POST', 'api/book/1/1/1/3/1', [], ['Accept' => 'application/json']);
        $response->assertStatus(201)
            ->assertJsonStructure([
                "success",
                "data" => [],
                "message"
            ])
            ->assertJson([
                "data" => [
                    2
                ]
            ]);

    }

    public function testAuthenticatedFailedBookSameSeats() {

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

        // Populate DB with mock data (for convenience it is the same data in the dump file)
        $this->populateDatabase();
        
        $response = $this->json('POST', 'api/book/1/1/1/3/1', [], ['Accept' => 'application/json']);
        
        // Seats are already booked
        $response = $this->json('POST', 'api/book/1/1/1/3/1', [], ['Accept' => 'application/json']);
        $response->assertStatus(422)
            ->assertJson([
                "success" => false,
                "message" => "Could not book seats since they are already booked"
            ]);

    }

    public function testAuthenticatedFailedBookSubSeats() {

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

        // Populate DB with mock data (for convenience it is the same data in the dump file)
        $this->populateDatabase();
        
        $response = $this->json('POST', 'api/book/1/1/1/3/1', [], ['Accept' => 'application/json']);

        // Seats are already booked for a longer route of this trip
        $response = $this->json('POST', 'api/book/1/1/1/2/1', [], ['Accept' => 'application/json']);
        $response->assertStatus(422)
            ->assertJson([
                "success" => false,
                "message" => "Could not book seats since they are already booked"
            ]);

    }

    public function testAuthenticatedFailedBookSuperSeats() {

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

        // Populate DB with mock data (for convenience it is the same data in the dump file)
        $this->populateDatabase();
        
        $response = $this->json('POST', 'api/book/1/1/1/2/1', [], ['Accept' => 'application/json']);

        // Seats are already booked for a longer route of this trip
        $response = $this->json('POST', 'api/book/1/1/1/3/1', [], ['Accept' => 'application/json']);
        $response->assertStatus(422)
            ->assertJson([
                "success" => false,
                "message" => "Could not book seats since they are already booked"
            ]);

    }

    public function testAuthenticatedFailedBookSeatsWrongUser() {

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

        // Populate DB with mock data (for convenience it is the same data in the dump file)
        $this->populateDatabase();

        // User 2 does not exist
        $response = $this->json('POST', 'api/book/2/1/1/3/1', [], ['Accept' => 'application/json']);
        $response->assertStatus(422)
            ->assertJson([
                "success" => false,
                "message" => "User not found"
            ]);

    }

    function populateDatabase() {
        DB::select(DB::raw(
            "INSERT INTO trips (start_destination, end_destination, trip_date) VALUES
            ('Cairo', 'Asyut', '2020-01-15 10:00:00'),
            ('Giza', 'Alexandria', '2020-01-16 10:00:00');"
        ));
        DB::select(DB::raw(
            "INSERT INTO stops (name, order_of_stop, trip_id) VALUES
            ('Cairo', 1, 1),
            ('AlFayyum', 2, 1),
            ('AlMinya', 3, 1),
            ('Asyut', 4, 1),
            ('Giza', 1, 2),
            ('Tanta', 2, 2),
            ('Alexandria', 3, 2);
        
            INSERT INTO seats (trip_id) VALUES
            (1), (1), (1), (1), (1), (1), (1), (1), (1), (1), (1), (1),
            (2), (2), (2), (2), (2), (2), (2), (2), (2), (2), (2), (2);
        
            INSERT INTO seats_stop (seat_id, stop_id) VALUES
            (1, 1), (2, 1), (3, 1), (4, 1), (5, 1), (6, 1), (7, 1), (8, 1), (9, 1), (10, 1), (11, 1), (12, 1),
            (1, 2), (2, 2), (3, 2), (4, 2), (5, 2), (6, 2), (7, 2), (8, 2), (9, 2), (10, 2), (11, 2), (12, 2),
            (1, 3), (2, 3), (3, 3), (4, 3), (5, 3), (6, 3), (7, 3), (8, 3), (9, 3), (10, 3), (11, 3), (12, 3),
            (13, 5), (14, 5), (15, 5), (16, 5), (17, 5), (18, 5), (19, 5), (20, 5), (21, 5), (22, 5), (23, 5), (24, 5),
            (13, 6), (14, 6), (15, 6), (16, 6), (17, 6), (18, 6), (19, 6), (20, 6), (21, 6), (22, 6), (23, 6), (24, 6);"
        ));
        DB::select(DB::raw(
            "INSERT INTO seats (trip_id) VALUES
            (1), (1), (1), (1), (1), (1), (1), (1), (1), (1), (1), (1),
            (2), (2), (2), (2), (2), (2), (2), (2), (2), (2), (2), (2);
        
            INSERT INTO seats_stop (seat_id, stop_id) VALUES
            (1, 1), (2, 1), (3, 1), (4, 1), (5, 1), (6, 1), (7, 1), (8, 1), (9, 1), (10, 1), (11, 1), (12, 1),
            (1, 2), (2, 2), (3, 2), (4, 2), (5, 2), (6, 2), (7, 2), (8, 2), (9, 2), (10, 2), (11, 2), (12, 2),
            (1, 3), (2, 3), (3, 3), (4, 3), (5, 3), (6, 3), (7, 3), (8, 3), (9, 3), (10, 3), (11, 3), (12, 3),
            (13, 5), (14, 5), (15, 5), (16, 5), (17, 5), (18, 5), (19, 5), (20, 5), (21, 5), (22, 5), (23, 5), (24, 5),
            (13, 6), (14, 6), (15, 6), (16, 6), (17, 6), (18, 6), (19, 6), (20, 6), (21, 6), (22, 6), (23, 6), (24, 6);"
        ));
        DB::select(DB::raw(
            "INSERT INTO seats_stop (seat_id, stop_id, is_booked, booking_user_id) VALUES
            (1, 1, 0, 0), (2, 1, 0, 0), (3, 1, 0, 0), (4, 1, 0, 0), (5, 1, 0, 0), (6, 1, 0, 0), (7, 1, 0, 0), (8, 1, 0, 0), (9, 1, 0, 0), (10, 1, 0, 0), (11, 1, 0, 0), (12, 1, 0, 0),
            (1, 2, 0, 0), (2, 2, 0, 0), (3, 2, 0, 0), (4, 2, 0, 0), (5, 2, 0, 0), (6, 2, 0, 0), (7, 2, 0, 0), (8, 2, 0, 0), (9, 2, 0, 0), (10, 2, 0, 0), (11, 2, 0, 0), (12, 2, 0, 0),
            (1, 3, 0, 0), (2, 3, 0, 0), (3, 3, 0, 0), (4, 3, 0, 0), (5, 3, 0, 0), (6, 3, 0, 0), (7, 3, 0, 0), (8, 3, 0, 0), (9, 3, 0, 0), (10, 3, 0, 0), (11, 3, 0, 0), (12, 3, 0, 0),
            (13, 5, 0, 0), (14, 5, 0, 0), (15, 5, 0, 0), (16, 5, 0, 0), (17, 5, 0, 0), (18, 5, 0, 0), (19, 5, 0, 0), (20, 5, 0, 0), (21, 5, 0, 0), (22, 5, 0, 0), (23, 5, 0, 0), (24, 5, 0, 0),
            (13, 6, 0, 0), (14, 6, 0, 0), (15, 6, 0, 0), (16, 6, 0, 0), (17, 6, 0, 0), (18, 6, 0, 0), (19, 6, 0, 0), (20, 6, 0, 0), (21, 6, 0, 0), (22, 6, 0, 0), (23, 6, 0, 0), (24, 6, 0, 0);"
        ));
    }
}
