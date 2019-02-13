<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Artisan;
use App\Product;
use App\Order;
use App\Configuration;

class OrderTest extends TestCase
{

    //Database setup
    public function setUp() {
        parent::setUp();
        
        Mail::fake();        //Avoid sending emails
        //Storage::fake('public');     //Avoid writting to storage
        Artisan::call('migrate');
        /*$this->cleanDirectories();*/
    }

    //Clean up after the test
    public function tearDown() {
        parent::tearDown();
//        $this->cleanDirectories();
    }

    public function cleanDirectories () {
        Storage::disk('public')->deleteDirectory('uploads');
    }

    public function getFileForAttachment($attachment) {
        return dirname(__DIR__) . '/storage/uploads/' . $attachment['file_name'];
    }



    ////////////////////////////////////////////////////////////////////////
    // Parameters testing
    ////////////////////////////////////////////////////////////////////////
    public function testOrderCheck() {
        //Create the configuration thresholds
        Configuration::create(["key"=>"delivery1", "value"=>10]);
        Configuration::create(["key"=>"delivery2", "value"=>20]);
        Configuration::create(["key"=>"delivery3", "value"=>30]);
        //Create two products to see
        Product::create(['title'=> 'test1', 'price'=>100, 'stock'=>1, 'weight'=>1.2, 'isVehicle'=>true, 'modele_id'=>1]);
        Product::create(['title'=> 'test2', 'price'=>200, 'stock'=>3, 'discount'=>100, 'weight'=>0.1, 'isVehicle'=>true, 'modele_id'=>1]);
        $data = [
        'firstName' => 'Sergi',
        'lastName' => 'Redorta',
        'email' => 'sergi.redorta@hotmail.com',            
        'mobile' => '0623133212',
        'delivery' => true,
        'address1' =>"6, rue roger Avon",
        'address2'  => '',
        'cp'        => '06610',
        'city'      => 'LaGaude',
        'cart'  => [["id"=>1, "quantity"=>1],["id"=>2, "quantity"=>5],["id"=>3, "quantity"=>3]]
        ];
        $response = $this->post('api/order/check',$data);
        //dd(Configuration::all()->toArray());
        dd($response->json());
        /*$response->assertStatus(200);
        //dd(Attachment::all()->toArray());
        dd($response->json());
        $this->assertDatabaseHas('brands', [
            'name'=>'honda', 'description' => 'This is a test description'
        ]);  
        $this->assertDatabaseMissing('attachments', [
            'attachable_id'=>'1', 'attachable_type' => 'App\Brand'
        ]);*/
    }
    public function testOrderCreate() {
        //Create the configuration thresholds
        Configuration::create(["key"=>"delivery1", "value"=>10]);
        Configuration::create(["key"=>"delivery2", "value"=>20]);
        Configuration::create(["key"=>"delivery3", "value"=>30]);
        //Create two products to see
        Product::create(['title'=> 'test1', 'price'=>100, 'stock'=>1, 'weight'=>1.2, 'isVehicle'=>true, 'modele_id'=>1]);
        Product::create(['title'=> 'test2', 'price'=>200, 'stock'=>3, 'discount'=>100, 'weight'=>0.1, 'isVehicle'=>true, 'modele_id'=>1]);
        $data = [
        'firstName' => 'Sergi',
        'lastName' => 'Redorta',
        'email' => 'sergi.redorta@hotmail.com',            
        'mobile' => '0623133212',
        'delivery' => true,
        'address1' =>"6, rue roger Avon",
        'address2'  => '',
        'cp'        => '06610',
        'city'      => 'LaGaude',
        'cart'  => [["id"=>1, "quantity"=>1, "title"=>"test"],["id"=>2, "quantity"=>5, "title"=>"test2"]],
        'total'             => 10,
        'deliveryCost'      => 1,
        'price'             => 9,
        'paypalOrderId'     =>'TESTORDER',
        'paypalPaymentId'   => 'PAYID-LRR6OOQ2CG57508GP611664T$',
        'status'            => 'en traitement',
        'user_id'           => null
    ];
        $response = $this->post('api/order/create',$data);
        dd($response->json());
    }


    public function testPayPal() {
        $response = $this->post('api/debugPayPal',[]);
        dd($response);
    }
}