# Export
Export data in database to various file format . Currently only xls,json and csv file formats are supported

##Example

  <h6>Using Eloquent</h6>
```php4
  use YS\Export\Csv;
  use App\User;
  
  public function exportUsers()
  {
      $csv = new Csv( User::select('*'));
      return $csv->response();
  } 
```

  <h6>Using DB Facade</h6>
```php
  use YS\Export\Csv;
  use App\User;
  
  public function exportUsers()
  {
      $csv = new Csv( DB::table('users')->select('name','email'));
      return $csv->response();
  } 
```

  <h6>Using Joins in query</h6>
  
 ```php
 use YS\Export\Csv;
 use App\User;
  
  public function exportUsers()
  {
      $query = User::join('companies', 'companies.id','users.company_id')->select('users.name','users.email','companies.name as company');
      $csv = new Csv( $query );
      return $csv->response();
  } 
```
<p>Similarly you can use Json export</p>

```php
  use YS\Export\Json;
  use App\User;
  
  public function exportUsers()
  {
      $json = new Json( DB::table('users')->select('name','email'));
      return $json->response();
  } 
```
<p>You can provide column names inside select statement in order to export only selected
columns from database.Optionally you can also define column names inside ys-export config file
which you do not want to export in  file like id,password etc...</p>
<p> To do this just run php artisan vendor:publish and select ys-export:config group</p>

