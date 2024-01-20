<?php


use App\Models\ToDo;
use App\Models\ToDoDetail;
use Tests\TestCase;

class ToDoDetailControllerTest extends TestCase
{
    public function test_ToDoDetail登録()
    {
        // テストデータ作成
        $toDo = ToDo::factory()->create();

        // リクエスト
        $response = $this->postJson('/api/toDoDetails', [
            'to_do_id' => $toDo->id,
            'name'     => 'test'
        ]);

        // アサーション
        $response->assertStatus(200);
        $this->assertDatabaseCount(ToDoDetail::class, 1);
        $toDoDetail = ToDoDetail::first();
        $this->assertEquals($toDo->id, $toDoDetail->to_do_id);
    }

    public function test_ToDoDetail更新()
    {
        // テストデータ作成
        $toDo   = ToDo::factory()->create();
        $before = ToDoDetail::factory()->create([
            'to_do_id' => $toDo->id,
            'name'     => 'test'
        ]);

        // リクエスト
        $response = $this->putJson('/api/toDoDetails/'.$before->id, [
            'name'           => 'updated',
            'completed_flag' => true
        ]);

        // アサーション
        $response->assertStatus(200);
        $after = ToDoDetail::find($before->id);
        $this->assertEquals($toDo->id, $after->to_do_id);
        $this->assertEquals('updated', $after->name);
        $this->assertTrue($after->completed_flag);
    }


    public function test_ToDoDetail削除()
    {
        // テストデータ作成
        $toDo       = ToDo::factory()->create();
        $toDoDetail = ToDoDetail::factory()->create([
            'to_do_id' => $toDo->id,
        ]);

        // リクエスト
        $response = $this->delete('/api/toDoDetails/'.$toDoDetail->id);

        // アサーション
        $response->assertStatus(200);
        $this->assertDatabaseCount(ToDoDetail::class, 0);
    }
}
