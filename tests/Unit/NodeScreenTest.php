<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Orchid\Support\Testing\DynamicTestScreen;
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
    public function admin_can_create_node()
    {
        $name = fake()->name();
        $desc = fake()->text();
        $email = fake()->unique()->safeEmail();
        $phone = fake()->phoneNumber();

        $this
            ->getNodeScreen()
            ->actingAs(User::factory()->admin()->create())
            ->method('addNode', [
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
    public function guest_can_not_create_node()
    {
        $name = fake()->name();
        $desc = fake()->text();
        $email = fake()->unique()->safeEmail();
        $phone = fake()->phoneNumber();

        $this
            ->getNodeScreen()
            ->method('addNode', [
                'name' => $name,
                'desc' => $desc,
                'email' => $email,
                'phone' => $phone,
            ])
            ->assertStatus(200)
            ->assertDontSeeText($name)
            ->assertDontSeeText($desc)
            ->assertDontSeeText($email)
            ->assertDontSeeText(onlyDigits($phone));
    }

    /**
     * @test
     */
    public function user_can_not_create_node()
    {
        $this
            ->getNodeScreen()
            ->actingAs(User::factory()->create())
            ->method('addNode', [
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
    public function admin_can_generate_nodes()
    {
        $this
            ->getNodeScreen()
            ->actingAs(User::factory()->admin()->create())
            ->method('generateNodes')
            ->assertStatus(200)
            ->assertSeeText('Configure columns');
    }

    /**
     * @test
     */
    public function guest_can_not_generate_nodes()
    {
        $this
            ->getNodeScreen()
            ->method('generateNodes')
            ->assertStatus(200)
            ->assertDontSeeText('Configure columns');
    }

    /**
     * @test
     */
    public function user_can_not_generate_nodes()
    {
        $this
            ->getNodeScreen()
            ->actingAs(User::factory()->create())
            ->method('generateNodes')
            ->assertStatus(403);
    }

    /**
     * @test
     */
    public function admin_can_clear_nodes()
    {
        $this
            ->getNodeScreen()
            ->actingAs(User::factory()->admin()->create())
            ->method('clearNodes')
            ->assertStatus(200)
            ->assertSeeText('There are no objects currently displayed');
    }

    /**
     * @test
     */
    public function guest_can_not_clear_nodes()
    {
        $this
            ->getNodeScreen()
            ->method('clearNodes')
            ->assertStatus(200)
            ->assertDontSeeText('There are no objects currently displayed');
    }

    /**
     * @test
     */
    public function user_can_not_clear_nodes()
    {
        $this
            ->getNodeScreen()
            ->actingAs(User::factory()->create())
            ->method('clearNodes')
            ->assertStatus(403);
    }

    private function getNodeScreen(): DynamicTestScreen
    {
        return $this->screen('builder.nodes');
    }
}
