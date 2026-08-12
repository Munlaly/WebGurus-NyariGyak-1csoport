<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    // Clear the sqlite testing database after each test run
    use RefreshDatabase; 

    public function test_new_users_can_register(): void
    {
        
        $response = $this->post('/register', [
            'username' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        // 1. Assert the user is logged in
        $this->assertAuthenticated();

        // 2. Assert the user was inserted into the database
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);

        // 3. Assert the user is redirected to the welcome page
        $response->assertRedirect(route('welcome'));
    }

    public function test_users_cannot_register_with_invalid_emails(): void
    {
        // Execute register request on mock Http client
        $response = $this->post('/register', [
            'username' => 'Test User',
            'email' => 'not-an-email',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        // Assert validation error on the email field
        $response->assertSessionHasErrors('email');

        // Assert no session was established
        $this->assertGuest();
    }

    public function test_users_cannot_register_with_unconfirmed_passwords():void{
         // Execute register request on mock Http client
        $response = $this->post('register', [
            'username' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'wrong-confirmation',
        ]);

        // Assert validation error on the password field (is not confirmed)
        $response->assertSessionHasErrors();

        // Assert no session was established
        $this->assertGuest();
    }

    public function test_existing_users_cannot_register(): void{

    // Seed an existing user into the test database
        User::factory()->create([
            'email' => 'existing@example.com',
        ]);

         // Execute register request on mock Http client
        $response = $this->post('/register', [
            'username' => 'Duplicate User',
            'email' => 'existing@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        // Assert validation error on the email field (already exists)
        $response->assertSessionHasErrors('email');

         // Assert no session was established
        $this->assertGuest();

    }

    public function test_users_cannot_register_with_existing_username(){
        // Seed an existing user into the test database
        User::factory()->create([
            'username'=>'testuser'
        ]);

         // Execute register request on mock Http client
        $response = $this->post('/register',[
            'username'=> 'testuser',
            'email'=> 'test@example.com',
            'password'=>'password',
            'password_confirmation' =>'password',
        ]);

        // Assert validation error on the usernamefield (already exists)
        $response->assertSessionHasErrors('username');

        // Assert no session was established
        $this->assertGuest();
    }
}