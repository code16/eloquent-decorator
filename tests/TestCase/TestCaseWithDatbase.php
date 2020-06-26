<?php namespace igaster\EloquentDecorator\Tests\TestCase;

use Dotenv\Dotenv;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Orchestra\Testbench\TestCase;

class TestCaseWithDatbase extends TestCase
{

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        if (file_exists(__DIR__.'/../.env')) {
            $dotenv = Dotenv::create(__DIR__.'/../');
            $dotenv->load();
        }
    }

    protected $database;

    public function setUp(): void
    {
        parent::setUp();

        Eloquent::unguard();
        $database = new DB;
        $database->addConnection([
            'driver'   => 'sqlite',
            'database' => ':memory:',
        ]);
        $database->bootEloquent();
        $database->setAsGlobal();
        $this->database = $database;
    }

    public function testDatabaseConnection()
    {
        $this->assertInstanceOf('Illuminate\Database\SQLiteConnection', $this->database->connection());
    }

    protected function seeInDatabase($table, array $data, $connection = null)
    {
        $database = $this->database;

        $count = $database->table($table)->where($data)->count();

        $this->assertGreaterThan(0, $count, sprintf(
            'Unable to find row in database table [%s] that matched attributes [%s].', $table, json_encode($data)
        ));

        return $this;
    }

    protected function notSeeInDatabase($table, array $data, $connection = null)
    {
        $database = $this->database;

        $count = $database->table($table)->where($data)->count();

        $this->assertEquals(0, $count, sprintf(
            'Found unexpected records in database table [%s] that matched attributes [%s].', $table, json_encode($data)
        ));

        return $this;
    }
}    