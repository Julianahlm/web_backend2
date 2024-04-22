<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\support\Facades\Hash;
use App\Models\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testRegisterSuccess()
    {
        $this->post('/api/v1/users',[
            'name' => 'John Doe',
            'email' => 'john.doe@gmail.com',
            'password' => '123456789',
            'password_confirmation' => '123456789'
        ])->assertStatus(201)
            ->assertJson([
                "data"=>[
                    "name"=>"John Doe",
                    "email"=>"john.doe@gmail.com"
                ]
                ]);
    }
    public function testRegisterFailed()
    {
        $this->post('api/v1/users',[
            'name'=>'',
            'email'=>'',
            'password'=>'',
            'password_confirmation'=>''
        ])->assertStatus(400)
            ->assertJson([
                "errors"=>[
                    "name"=>[
                        "The name field is required."
                    ],
                    "email"=>[
                        "The email field is required."
                    ],
                    "password"=>[
                        "The password field is required."
                    ]
                ]
           ]);
    }
    public function testRegisterFailedValidation()
    {
        $this->testRegisterSuccess();
        $this->post('api/v1/users',[
            'name'=>'Mitchell Admin',
            'email'=>'john.doe@gmail.com',
            'password'=>'123456789',
            'password_confirmation'=>'123456789'
        ])->assertStatus(400)
            ->assertJson([
                "errors"=>[
                    "email"=>[
                        "The email has already been taken."
                    ]
                ]
           ]);
    }

    public function testLoginSuccess()
    {
        $this->testRegisterSuccess();
        $this->post('/api/v1/users/login',[
            'email' => 'john.doe@gmail.com',
            'password' => '123456789'
        ])->assertStatus(200)
            ->assertJson([
                "data"=>[
                    "name"=>"John Doe",
                    "email"=>"john.doe@gmail.com"
                ]
            ]);

            $user = User::where('email','john.doe@gmail.com')->first();
            self::assertNotNull($user->remember_token);

        
    }

    public function testLoginFailedEmail()
    {
        $this->post('/api/v1/users/login',[
            'email' => 'john.doe@gmail.com',
            'password' => '123456789'
        ])->assertStatus(401)
            ->assertJson([
                "errors"=>[
                    "message"=>['username or password wrong']
                ]
            ]);
    }

    public function testLoginFailedPassword()
    {
        $this->testRegisterSuccess();
        $this->post('api/v1/users/login',[
            'email'=>'john.doe@gmail.com',
            'password'=>'password123456789'
        ])->assertStatus(401)
            ->assertJson([
                "errors"=>[
                    "message"=>['username or password wrong']
                ]
            ]);
    }

    public function testGetSuccess()
    {
        $user = User::create([
            'name' => 'Mitchell Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('12345678'),
            'remember_token' => '1234-5678'
        ]);


        $this->get('/api/v1/users/profile', [
            'Authorization' => '1234-5678'
        ])->assertStatus(200)
            ->assertJson([
                "data"=>[
                    "email"=>'admin@gmail.com',
                    "name"=>'Mitchell Admin',
                ]
            ]);
    }

    public function testGetFailedUnauthorized()
    {
        $user = User::create([
            'name' => 'Mitchell Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('12345678'),
            'remember_token' => '1234-5678'
        ]);

        $this->get('/api/v1/users/profile')
            ->assertStatus(401)
            ->assertJson([
                "error" => [
                    'message' => ['Unauthorized']
                ]
            ]);
    }

    public function testGetInvalidToken()
    {
        $user = User::create([
            'name' => 'Mitchell Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('12345678'),
            'remember_token' => '1234-5678'
        ]);

        $this->get('/api/v1/users/profile', [
            'Authorization' => '8765-4321'
        ])->assertStatus(401)
            ->assertJson([
                "error" => [
                    'message' => ['Unauthorized']
                ]
            ]);
    }

    public function testUpdateNameSuccess()
    {
        $user = User::create([
            'name' => 'Mitchell Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('12345678'),
            'remember_token' => '1234-5678'
        ]);
        $oldUser = User::where('email','admin@gmail.com')->first();


        $this->patch('/api/v1/users/profile', 
            [
                'name' => 'Mitchell Admin Updated'
            ],
            [
                'Authorization' => '1234-5678'
            ]
        )->assertStatus(200)
            ->assertJson([
                "data"=>[
                    "email"=>'admin@gmail.com',
                    "name"=>'Mitchell Admin Updated',
                ]
            ]);

        $newUser = User::where('email','admin@gmail.com')->first();
        self::assertNotEquals($oldUser->name, $newUser->name);
    }

    public function testUpdateNamePassword()
    {
        $user = User::create([
            'name' => 'Mitchell Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('12345678'),
            'remember_token' => '1234-5678'
        ]);
        $oldUser = User::where('email','admin@gmail.com')->first();


        $this->patch('/api/v1/users/profile', 
            [
                'password' => 'admin12345678',
            ],
            [
                'Authorization' => '1234-5678'
            ]
        )->assertStatus(200)
            ->assertJson([
                "data"=>[
                    "email"=>'admin@gmail.com',
                    "name"=>'Mitchell Admin',
                ]
            ]);

        $newUser = User::where('email','admin@gmail.com')->first();
        self::assertNotEquals($oldUser->password, $newUser->password);
    }

    public function testUpdateFailed()
    {
        $user = User::create([
            'name' => 'Mitchell Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('12345678'),
            'remember_token' => '1234-5678'
        ]);
        $this->patch('/api/v1/users/profile', 
            [
                'name' => 'Mitchell Admin Updated, but failed because of name validation failed, Mitchell Admin Updated, but failed because of name validation failed'
            ],
            [
                'Authorization' => '1234-5678'
            ]
        )->assertStatus(400)
            ->assertJson([
                "errors"=>[
                    "name"=>['The name field must not be greater than 100 characters.',]
                ]
            ]);
    }

    public function testLogoutSuccess()
    {
        $user = User::create([
            'name' => 'Mitchell Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('12345678'),
            'remember_token' => '1234-5678'
        ]);


        $this->delete(uri: '/api/v1/users/logout', headers:[
            'Authorization' => '1234-5678'
        ])->assertStatus(200)
            ->assertJson([
                "data"=>true
            ]);

        $user = User::where('email','admin@gmail.com')->first();
        self::assertNull($user->remember_token);
    }

    public function testLogoutFailed()
    {
        $user = User::create([
            'name' => 'Mitchell Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('12345678'),
            'remember_token' => '1234-5678'
        ]);

        $this->delete(uri:'/api/v1/users/logout',  headers: [
                'Authorization' => 'Wrong-Authorization-Token'
        ])->assertStatus(401)
            ->assertJson([
                "error"=>[
                    "message"=>['Unauthorized']
                ]
            ]);
    }

    
}
