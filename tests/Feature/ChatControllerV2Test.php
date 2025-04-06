<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Session;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ChatControllerV2Test extends TestCase
{
    use RefreshDatabase;

    public function testIndexReturnsMessages()
    {
        $session = Session::factory()->create();
        Message::factory()->count(5)->create(['session_id' => $session->id]);

        $response = $this->withCookie('session_v2_id', $session->id)
                         ->getJson(route('v2.chat.index'));

        dd($response->json());

        $response->assertStatus(200)
                 ->assertJsonStructure(['data', 'links', 'meta']);
    }

    public function testGetSessionIdCreatesNewSession()
    {
        $response = $this->postJson('/api/chat/session');

        $response->assertStatus(200)
                 ->assertJsonStructure(['session_id']);

        $this->assertDatabaseCount('sessions', 1);
    }

    public function testStoreSavesMessagesAndReturnsResponse()
    {
        $session = Session::factory()->create();

        $this->mock(\App\Actions\GetLLMResponse::class, function ($mock) {
            $mock->shouldReceive('run')
                 ->andReturn([
                     'status' => 'success',
                     'data' => ['output' => 'Test response']
                 ]);
        });

        $response = $this->withSession(['session_v2_id' => $session->id])
                         ->postJson('/api/chat/messages', [
                             'message' => 'Hello, world!'
                         ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success',
                     'data' => ['output' => 'Test response']
                 ]);

        $this->assertDatabaseCount('messages', 2); // One for user, one for assistant
    }
}
