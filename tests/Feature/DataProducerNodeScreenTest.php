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

    /**
     * @test
     */
    public function testDataProducerNodeScreen()
    {
        $this->admin_can_create_data_producer_node();
    }

    /**
     * @return void
     */
    public function admin_can_create_data_producer_node()
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
