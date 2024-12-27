<?php

namespace Tests\Feature;

use App\Models\Node;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Orchid\Support\Testing\DynamicTestScreen;
use Orchid\Support\Testing\ScreenTesting;
use Tests\CreatesApplication;
use Tests\TestCase;

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
            ->assertStatus(200);

        $node = Node::where('email', '=', $email)->first();

        $this->assertNotNull($node);
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
            ->assertStatus(200);

        $node = Node::where('email', '=', $email)->first();

        $this->assertNull($node);
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
        Node::truncate();

        $this
            ->getNodeScreen()
            ->actingAs(User::factory()->admin()->create())
            ->method('generateNodes')
            ->assertStatus(200);

        $this->assertNotEmpty(Node::all());
    }

    /**
     * @test
     */
    public function guest_can_not_generate_nodes()
    {
        Node::truncate();

        $this
            ->getNodeScreen()
            ->method('generateNodes')
            ->assertStatus(200);

        $this->assertEmpty(Node::all());
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
        $this->generateNodes();

        $this
            ->getNodeScreen()
            ->actingAs(User::factory()->admin()->create())
            ->method('clearNodes')
            ->assertStatus(200);

        $this->assertEmpty(Node::all());
    }

    /**
     * @test
     */
    public function guest_can_not_clear_nodes()
    {
        $this->generateNodes();

        $this
            ->getNodeScreen()
            ->method('clearNodes')
            ->assertStatus(200);

        $this->assertNotEmpty(Node::all());
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

    private function generateNodes(): void
    {
        Node::factory()
            ->count(5)
            ->make()
            ->each(fn ($node) => $node->save());

        $this->assertNotEmpty(Node::all());
    }
}
