<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ControllersTest extends TestCase
{

    /** @test */
    public function a_generated_report_data_is_saved() {
        $user = User::find(14);
        $response = $this
            ->actingAs($user)
            ->get('/reportes/generated-report-file');
        dd($response->decodeResponseJson());
    }
}
