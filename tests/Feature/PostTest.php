<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    protected $postsTable;

    protected function setUp(): void
    {
        parent::setUp();

        $this->postsTable = app(Post::class)->getTable();
    }

    public function test_posts_list(): void
    {
        $response = $this->get(route('posts.index'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'slug',
                    'text',
                    'user_id',
                ]
            ]
        ]);
    }

    public function test_posts_show(): void
    {
        $post = Post::factory()->create();

        $response = $this->get(route('posts.show', $post->slug));

        $response->assertStatus(200);
        $response->assertExactJson([
            'data' => [
                'slug' => $post->slug,
                'text' => $post->text,
                'user_id' => $post->user_id,
            ]
        ]);
    }

    /**
     * @dataProvider storeDataProvider
     */
    public function test_posts_store($slug, $text): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')->postJson(route('posts.store'), [
            'slug' => $slug,
            'text' => $text,
        ]);

        $response->assertSuccessful();
        $this->assertDatabaseHas($this->postsTable, [
            'user_id' => $user->id,
            'text' => $text,
        ]);

        // generate slug if slug nullable in request check
        if (!$slug) {
            $this->assertDatabaseMissing($this->postsTable, [
                'user_id' => $user->id,
                'slug' => $slug,
            ]);
        }
    }

    public static function storeDataProvider(): array
    {
        return [
            [null, 'Lorem Ipsum'],
            ['Unexpected Create Slug', 'Lorem Ipsum'],
        ];
    }

    /**
     * @dataProvider updateDataProvider
     */
    public function test_posts_update($slug, $text): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();
        $originalSlug = $post->slug;

        $this->assertDatabaseHas($this->postsTable, [
            'user_id' => $user->id,
            'text' => $post->text,
        ]);
        $this->assertDatabaseMissing($this->postsTable, [
            'user_id' => $user->id,
            'text' => $text,
        ]);

        $response = $this->actingAs($user, 'api')->putJson(route('posts.update', $post->slug), [
            'slug' => $slug,
            'text' => $text,
        ]);

        $response->assertSuccessful();

        $this->assertDatabaseHas($this->postsTable, [
            'user_id' => $user->id,
            'text' => $text,
        ]);

        // do not generate slug on update if slug nullable in request check
        if (!$slug) {
            $this->assertDatabaseHas($this->postsTable, [
                'user_id' => $user->id,
                'slug' => $originalSlug,
            ]);
        }
    }

    public static function updateDataProvider(): array
    {
        return [
            [null, 'Lorem Ipsum'],
            ['Unexpected Update Slug', 'Lorem Ipsum'],
        ];
    }

    public function test_posts_update_unauthenticated(): void
    {
        $post = Post::factory()->for(User::factory())->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')->putJson(route('posts.update', $post->slug), [
            'slug' => 'Testing Slug',
            'text' => 'Lorem Ipsum',
        ]);

        $response->assertForbidden();
    }

    public function test_posts_delete(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();

        $response = $this->actingAs($user, 'api')->deleteJson(route('posts.destroy', $post->slug));

        $response->assertSuccessful();
        $this->assertDatabaseMissing($this->postsTable, $post->toArray());
    }

    public function test_posts_delete_unauthenticated(): void
    {
        $post = Post::factory()->for(User::factory())->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')->deleteJson(route('posts.destroy', $post->slug));

        $response->assertForbidden();
        $this->assertDatabaseHas($this->postsTable, $post->toArray());
    }
}
