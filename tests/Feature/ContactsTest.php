<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use App\Models\Contacts;
use Tests\TestCase;

class ContactsTest extends TestCase
{
    use RefreshDatabase;

    public function testCreateContactsSuccess()
    {
        $response = $this->post('/api/v1/contacts', [
            'code' => '1234567890',
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'phone' => '1234567890',
            'mobile' => '9876543210',
            'street' => 'Merbabu',
            'city' => 'Medan',
            'state' => 'Sumatera',
            'zip' => '12345',
            'country' => 'Indonesia',
            'vat' => '123456789'
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Contact created successfully',
                'data' => [
                    'name' => 'John Doe',
                    'email' => 'johndoe@example.com'
                ]
            ]);
    }

    public function testCreateContactsFailed()
    {
        $response = $this->post('/api/v1/contacts', [
            'code' => '',
            'name' => '',
            'email' => ''
        ])->assertStatus(400)
            ->assertJson([
                "errors"=>[
                    "code"=>[
                        "The code field is required."
                    ],
                    "name"=>[
                        "The name field is required."
                    ],
                    "email"=>[
                        "The email field is required."
                    ]
                ]
            ]);
    }

    public function testUpdateContactsSuccess()
    {
        $contact = Contacts::create([
            'code' => '0987654321',
            'name' => 'Mitchell Admin',
            'email' => 'admin@gmail.com',
        ]);

        $response = $this->patch('/api/v1/contacts/'.$contact->id, [
            'name' => 'Mitchell Admin',
            'email' => 'admin@gmail.com'
        ])->assertStatus(200)
            ->assertJson([
                'message' => 'Contact updated successfully',
                'data' => [
                    'name' => 'Mitchell Admin',
                    'email' => 'admin@gmail.com'
                ]
            ]);
    }

    public function testUpdateContactsFailed()
    {
        $contact = Contacts::create([
            'code' => '0987654321',
            'name' => 'Mitchell Admin',
            'email' => 'admin@gmail.com',
        ]);

        $response = $this->patch('/api/v1/contacts/'.$contact->id, [
            'name' => 'Mitchell Admin Updated, but failed because of name validation failed, Mitchell Admin Updated, but failed because of name validation failed'
        ])->assertStatus(400)
            ->assertJson([
                "errors"=>[
                    "name"=>['The name field must not be greater than 100 characters.']
                ]
            ]);
    }

    public function testDeleteContactsSuccess()
    {
        $contact = Contacts::create([
            'code' => '0987654321',
            'name' => 'Mitchell Admin',
            'email' => 'admin@gmail.com',
        ]);

        $response = $this->delete('/api/v1/contacts/'.$contact->id);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Contact deleted successfully',
                'data' => true
            ]);
    }

    public function testDeleteContactsFailed()
    {
        $response = $this->delete('/api/v1/contacts/999');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Contact not found'
            ]);
    }
   
    public function testSearchContactsSuccess()
    {
        Contacts::create([
            'code' => '0987654321',
            'name' => 'Mitchell Admin',
            'email' => 'admin@gmail.com',
        ]);

        $response = $this->get('/api/v1/contacts/search');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    '*' => [
                        "id"=>'0987654321',
                        "name"=>'Mitchell Admin',
                        "email"=>'admin@gmail.com',
                    ]
                ]
            ]);
    }

    public function testSearchContactsFailed()
    {
        $response = $this->get('/api/v1/contacts/search?query=nonexistent');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }
}
