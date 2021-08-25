<?php
    
    namespace YS\Export\Tests;
    
    use YS\DataTables\Tests\Models\User;
    use Illuminate\Database\Schema\Blueprint;
    use Orchestra\Testbench\TestCase as BaseTestCase;
    
    abstract class TestCase extends BaseTestCase
    {
        protected function setUp()
        {
            parent::setUp();
            
            $this->migrateDatabase();
            
            $this->seedDatabase();
        }
        
        protected function migrateDatabase()
        {
            /** @var \Illuminate\Database\Schema\Builder $schemaBuilder */
            $schemaBuilder = $this->app['db']->connection()->getSchemaBuilder();
            if (! $schemaBuilder->hasTable('users')) {
                $schemaBuilder->create('users', function (Blueprint $table) {
                    $table->increments('id');
                    $table->string('name');
                    $table->string('email');
                    $table->timestamps();
                });
            }
            
        }
        
        protected function seedDatabase()
        {
            
            collect(range(1, 20))->each(function ($i)  {
                /** @var User $user */
                $user = User::query()->create([
                    'name'  => 'Record-' . $i,
                    'email' => 'Email-' . $i . '@example.com',
                ]);
            });
        }
        
        protected function getEnvironmentSetUp($app)
        {
            $app['config']->set('app.debug', true);
            $app['config']->set('database.default', 'testbench');
            $app['config']->set('database.connections.testbench', [
                'driver'   => 'sqlite',
                'database' => ':memory:',
                'prefix'   => '',
            ]);
        }
        
        protected function getPackageProviders($app)
        {
            return [
                \YS\Export\ExportServiceProvider::class,
            ];
        }
    }
