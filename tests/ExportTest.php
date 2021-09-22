<?php
    
    namespace YS\Export\Tests;
    
    use YS\Export\Tests\Models\User;
    use YS\Export\Xls;

    class ExportTest extends TestCase
    {
        
        public function testItReturnsBinaryFileResponse()
        {
            $csv = new Xls(User::select('*'),'users');
            
            $this->assertTrue( $csv->response() instanceof \Symfony\Component\HttpFoundation\BinaryFileResponse);
   
        }
        
        public function testItShouldBeAbleToSetNameOfFileToDownload()
        {
            $csv = new Xls(User::select('*'),'users');
    
            $this->assertTrue( $csv->getFileName() === "users");
        }
        
        public function testItShouldReturnErrorIfDriverIsOtherThanEloquentAndQueryBuilder()
        {
            $this->expectException(  \YS\Export\IncorrectDataSourceException::class);
            $csv = new Xls(User::all());
        }
    }
