<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Orchid\Support\Testing\ScreenTesting;
use Tests\CreatesApplication;
use Tests\TestCase;
use function onlyDigits;

class DataProducerNodeScreenTest extends TestCase
{
    use ScreenTesting, CreatesApplication, DatabaseMigrations;

//    /**
//     * @test
//     */
//    public function a_user_can_create_data_producer_node()
//    {
//        $user = User::factory()->admin()->create();
//        $response = $this->actingAs($user, 'platform')
//            ->withSession(['banned' => false])
//            ->post('/admin/centers/addDataProducerNode', );
//
//        $response->assertStatus(303);
//        $response->assertRedirectContains('/admin/data_producer_node');
//    }

    /**
     * @test
     */
    public function testDataProducerNodeScreen()
    {
        $screen = $this->screen('builder.data_producer_node')
            ->actingAs(User::factory()->admin()->create());

        $name = fake()->name();
        $email = fake()->unique()->safeEmail();
        $phone = fake()->phoneNumber();
        $desc = fake()->text();

        $screen
            ->method('addDataProducerNode', [
                'name' => $name,
                'desc' => $desc,
                'email' => $email,
                'phone' => $phone,
            ])
            ->assertStatus(200)
            ->assertSeeText($name)
            ->assertSeeText($desc)
            ->assertSeeText($email)
            ->assertSeeText(onlyDigits($phone));
    }
}
