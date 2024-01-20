<?php


namespace Tests\Feature;

use App\Models\ToDo;
use Tests\TestCase;

class ToDoControllerTest extends TestCase
{
    public function test_ToDo取得()
    {
        // テストデータ作成
        \App\Models\ToDo::factory(5)
            ->create()
            ->each(function (\App\Models\ToDo $todo) {
                \App\Models\ToDoDetail::factory(3)->create([
                    'to_do_id' => $todo->id,
                ]);
            });

        // リクエスト
        $response = $this->get('/api/toDos');

        // アサーション
        $response->assertStatus(200);
        $response->assertJsonCount(5);
        $first = $response->json(0);
        $this->assertCount(3, $first['to_do_details']);
    }

    public function test_ToDo登録()
    {
        // リクエスト
        $response = $this->postJson('/api/toDos', [
            'title' => 'test'
        ]);

        // アサーション
        $response->assertStatus(200);
        $this->assertDatabaseCount(ToDo::class, 1);
    }

    public function test_ToDo更新()
    {
        // テストデータ作成
        $before = ToDo::factory()->create([
            'title' => 'test'
        ]);

        // リクエスト
        $response = $this->putJson('/api/toDos/'.$before->id, [
            'title' => 'updated'
        ]);

        // アサーション
        $response->assertStatus(200);
        $after = ToDo::find($before->id);
        $this->assertEquals('updated', $after->title);
    }


    public function test_ToDo削除()
    {
        // テストデータ作成
        $toDo = ToDo::factory()->create([
            'title' => 'test'
        ]);

        // リクエスト
        $response = $this->delete('/api/toDos/'.$toDo->id);

        // アサーション
        $response->assertStatus(200);
        $this->assertDatabaseCount(ToDo::class, 0);
    }
}
