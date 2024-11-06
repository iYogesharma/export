# Export
Export data in database to various file format . Currently xls,json and csv file formats are supported

## Example

<h6>Using Eloquent</h6>

```php

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
### Similarly you can use Json and Excel export

```php
  use YS\Export\Json;
  use App\User;
  
  public function exportUsers()
  {
      $json = new Json( DB::table('users')->select('name','email'));
      return $json->response();
  } 
```

```php
  use YS\Export\Xls;
  use App\User;
  
  public function exportUsers()
  {
      $xls = new Xls( DB::table('users')->select('name','email'));
      return $xls->response();
  } 
  ```

  ```php

  use YS\Export\ArrayToXls;
  use App\User;
  
  /**
   * $data array of records to be exported
   * eg,  $data = [
      ['name' => 'user', 'email' => 'user@test.com','contact' => 123456789],
      ['name' => 'user1', 'email' => 'user1@test.com', 'contact' => 123456789]
    ]
   * bool $export with default value as true indicate weather to export data or not
   * string $fileName name of the file to be exported
   * array  $headers with default value as [] if values provided with export only provided columns
   * eg, headers = ['name','email'] will export only name and email not contact
   */


  public function exportUsers()
  {
    $xls = new ArrayToXls($data,$export,$fileName,$headers); // will export only name and email
    return $xls->response();
  } 

  ```
  
<p>You can provide column names inside select statement in order to export only selected
columns from database.Optionally you can also define column names inside ys-export config file
which you do not want to export in  file like id,password etc...</p>
<p> To do this just run php artisan vendor:publish and select ys-export:config group</p>
