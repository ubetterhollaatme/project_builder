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

class NodeScreenTest extends TestCase
{
    use ScreenTesting, CreatesApplication, DatabaseMigrations;

    /**
     * @test
     */
    public function admin_can_create_data_producer_node()
    {
        $name = fake()->name();
        $desc = fake()->text();
        $email = fake()->unique()->safeEmail();
        $phone = fake()->phoneNumber();

        $this
            ->getDPNScreen()
            ->actingAs(User::factory()->admin()->create())
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

    /**
     * @test
     */
    public function user_can_not_create_data_producer_node()
    {
        $this
            ->getDPNScreen()
            ->actingAs(User::factory()->create())
            ->method('addDataProducerNode', [
                'name' => fake()->name(),
                'desc' => fake()->text(),
                'email' => fake()->unique()->safeEmail(),
                'phone' => fake()->phoneNumber(),
            ])
            ->assertStatus(403);
    }

    /**
     * @test
     */
    public function admin_can_generate_data_producer_nodes()
    {
        $this
            ->getDPNScreen()
            ->actingAs(User::factory()->admin()->create())
            ->method('generateNodes')
            ->assertStatus(200)
            ->assertSeeText('Configure columns');
    }

    /**
     * @test
     */
    public function user_can_not_generate_data_producer_nodes()
    {
        $this
            ->getDPNScreen()
            ->actingAs(User::factory()->create())
            ->method('generateNodes')
            ->assertStatus(403);
    }

    /**
     * @test
     */
    public function admin_can_clear_data_producer_nodes()
    {
        $this
            ->getDPNScreen()
            ->actingAs(User::factory()->admin()->create())
            ->method('clearNodes')
            ->assertStatus(200)
            ->assertSeeText('There are no objects currently displayed');
    }

    /**
     * @test
     */
    public function user_can_not_clear_data_producer_nodes()
    {
        $this
            ->getDPNScreen()
            ->actingAs(User::factory()->create())
            ->method('clearNodes')
            ->assertStatus(403);
    }

    private function getDPNScreen()
    {
        return $this->screen('builder.data_producer_node');
    }
}
