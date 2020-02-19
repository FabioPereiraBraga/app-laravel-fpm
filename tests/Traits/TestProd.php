<?php
namespace Tests\Traits;

trait TestProd {
   protected function skipTestIfProd($message = 'Teste de produção') {
       if(!$this->isTestingProd()){
         $this->markTestSkipped($message);
       }
   }

   protected function isTestingProd(){
       return env('TESTING_PROD') === true;
   }
}