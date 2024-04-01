<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testRegisterSuccess()
    {
        $this->post('/api/v1/users', [
            'name'=> 'john doe',
            'email'=>'john.doe@gmail.com',
            'password'=>'123456789',
            'password_confirmation'=> '123456789'
        ])->assertStatus(201)
        ->assertJson([
            "data"=>[
            "name"=>"john doe",
            "email"=>"john.doe@gmail.com"
            ]]);
    }
    public function testRegisterFailed()
    {
        $this->post('/api/v1/users',[
            'name'=>'',
            'email'=>'',
            'password'=>'',
            'password_confirmation'=>''
        ])->assertStatus(400)
        ->assertjson([
            "errors"=>[
                "name"=>[
                    "the name field is required."
                ],
                "email"=>[
                    "the email field is required."
                ],
                "password"=>[
                    "the password field is required."
                ]
            ]
                ]);
    }
    public function testRegisterFailedValidation()
    {
    $this->testRegistersuccess();
    $test->post('/api/v1/users',[
        'name'=> 'mitchaell admin',
        'email'=> 'john.doe@gmail.com',
        'password'=>'123456789',
        'password_confirmation'=>'123456789'
    ])->assertStatus(400)
    ->assertjson([
        "errors"=>[
            "email"=>[
                "the email has already been taken."
            ]
        ]
            ]);
        }
        public function testloginSucces()
        {
            $this->testRegisterSuccess();
            $this->post('/api/v1/users/login',[
                'email'=>'john.doe@gmail.com',
                'password'=>'12345678'
            ])->assertstatus(200)
            ->assertJson([
                "data"=>[
                    "name"=>"john doe",
                    "email"=>"john.doe@gmail.com"
                ]
                ]);
                $user = user::where('email','john doe@gmail.com')->first();
                self::assertNotNull($user->remember_token);
        }
        public function testLoginFailedEmail()
        {

            $this->post('/api/v1/users/login',[
                'email'=>'john.doe@gmail.com',
                'password'=>'12345678'
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
            $this->post('/api/v1/users/login',[
                'email'=>'john.doe@gmail.com',
                'password'=> 'password12345678'
            ])->assertStatus(401)
            ->assertJson([
                "errors"=>[
                    "message"=>['username or password wrong']
                ]
                ]);
        }
    }
    