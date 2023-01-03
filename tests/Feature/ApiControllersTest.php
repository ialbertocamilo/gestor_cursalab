<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Faker\Factory as Faker;

class ApiControllersTest extends TestCase
{
    /** @test */
    public function entrenamiento_alumnos_report_is_paginated() {

        $user = User::where('document', '11111111')->first();
        $response1 = $this
            ->actingAs($user, 'api')
            ->post('/api/rest/entrenamiento/alumnos', [
                'page' => 1
            ]);
        $responseData1 = json_decode($response1->getContent());

        $this->assertIsObject($responseData1->pagination);
    }

    /** @test */
    public function user_history_report_filters_return_data()
    {
        $user = User::find(16);
        $response = $this
            ->actingAs($user, 'api')
            ->get('/api/rest/reports/user-history-filters');

        // Check HTTP status

        $response->assertStatus(200);

        // Check if all data values are included and
        // have the right datatype

        $responseData = json_decode($response->getContent());
        $this->assertIsArray($responseData->workspaces);
        $this->assertIsArray($responseData->recommendations);
    }

    /** @test */
    public function user_history_report_return_data()
    {
        //$user = User::find(16);
        $user = User::where('document', '11111111')->first();
        $response1 = $this
            ->actingAs($user, 'api')
            ->get('/api/rest/reports/user-history?page=1');

        $response2 = $this
            ->actingAs($user, 'api')
            ->get('/api/rest/reports/user-history');

        $response3 = $this
            ->actingAs($user, 'api')
            ->get('/api/rest/reports/user-history?page=1&search=covid');

        $response1->assertStatus(200);
        $this->assertTrue(isset($response1->decodeResponseJson()['courses']));

//        $response2->assertStatus(200);
//        $this->assertTrue(isset($response2->decodeResponseJson()['courses']));
//
//        $response3->assertStatus(200);
//        $this->assertTrue(isset($response3->decodeResponseJson()['courses']));
    }

    /** @test */
    public function login_ticker_should_be_stored_or_return_an_error_message() {

        $faker = Faker::create();
        $formData = [
            'name' => $faker->name,
            'workspace_id' => 900,
            'dni' => '12345678',
            'phone' => '987654321',
            'details' => 'This is an example of details'
        ];

        // Check if data is stored

        $response = $this->post('/api/rest/registrar_soporte_login', $formData);
        $response->assertStatus(200);

        // Check if response is a message when document does not exist

        $formData['dni'] = '9999999999';
        $response2 = $this->post('/api/rest/registrar_soporte_login', $formData);
        $response2->assertStatus(200);
    }

    /** @test */
    public function login_endpoint_returns_historic_menu_option() {
        $response1 = $this
            ->post('/api/auth/login', [
                'user' => '11111111',
                'password' => '11111111'
            ]);

        $submenuOptions = $response1->decodeResponseJson()['config_data']['side_menu'];
        $itHashistoryOption = false;
        foreach ($submenuOptions as $option) {
            if ($option['code'] === 'historico') {
                $itHashistoryOption = true;
            }
        }

        $this->assertTrue($itHashistoryOption);

    }

    /** @test */
    public function scorm_property_is_present() {
        $user = User::where('document', '11111111')->first();
        $response1 = $this
            ->actingAs($user, 'api')
            ->get('/api/rest/vademecum/search');

        $response1->assertStatus(200);
        $this->assertTrue(
            $response1->decodeResponseJson()['data']['data'][0]['scorm'] != ''
        );
    }
}
