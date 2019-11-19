<?php
declare(strict_types=1);

namespace Tests\Traits;


use Illuminate\Foundation\Testing\TestResponse;

trait TestSaves
{
  protected function assertStorage(array $sendData, array $testDatabase, array $testJsonData = null): TestResponse
  {
      /** @var TestResponse $response */
      $response = $this->json('POST',$this->routeStore(),$sendData);
      if($response->status() !== 201){
          throw new \Exception("Response status nust be 201, given {$response->status()}: \n {$response->getContent()}");
      }
      $this->assertInDataBase($testDatabase, $response);
      $this->assertjsonResponseContent($response, $testDatabase, $testJsonData);

      return $response;
  }

    protected function assertUpdate(array $sendData, array $testDatabase, array $testJsonData = null): TestResponse
    {
        /** @var TestResponse $response */
        $response = $this->json('PUT',$this->routeUpdate(),$sendData);
        if($response->status() !== 200){
            throw new \Exception("Response status nust be 200, given {$response->status()}: \n {$response->content()}");
        }

        $this->assertInDataBase($testDatabase, $response);
        $this->assertjsonResponseContent($response, $testDatabase, $testJsonData);

        return $response;
    }

    private function assertInDataBase(array $testDatabase, TestResponse $response )
    {
        $model = $this->model();
        $table = (new $model)->getTable();
        $this->assertDatabaseHas($table,$testDatabase + ['id'=>$response->json('id')]);
    }

    public function assertjsonResponseContent(TestResponse $response, array $testDatabase, array $testJsonData  = null)
    {
        $testResponse = $testJsonData ?? $testDatabase;
        $response->assertJsonFragment($testResponse + ['id'=>$response->json('id')] );
    }

}
