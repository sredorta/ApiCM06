<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Artisan;
use App\User;
use App\Account;
use App\Attachment;
use App\Brand;
use App\Thumb;

class BrandTest extends TestCase
{

    //Database setup
    public function setUp() {
        parent::setUp();
        
        Mail::fake();        //Avoid sending emails
        //Storage::fake('public');     //Avoid writting to storage
        Artisan::call('migrate');
        $this->cleanDirectories();
    }

    //Clean up after the test
    public function tearDown() {
        parent::tearDown();
        $this->cleanDirectories();
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
    public function testBrandCreateValidDefaultImage() {
        $auth = $this->loginAsAdmin();
        $data = [
            'name' => 'honda',
            'description' => 'This is a test description',          
        ];
        $response = $this->post('api/brands/create', $data);
        //dd($response->json());
        $response->assertStatus(200);
        //dd(Attachment::all()->toArray());
        dd($response->json());
        $this->assertDatabaseHas('brands', [
            'name'=>'honda', 'description' => 'This is a test description'
        ]);  
        $this->assertDatabaseHas('attachments', [
            'attachable_id'=>'1', 'attachable_type' => 'App\Brand', 'file_name'=> 'no-photo-available.jpg'
        ]);
    }
    public function testBrandCreateValidNonDefaultImage() {
        $auth = $this->loginAsAdmin();
        $path = dirname(__DIR__) . '/storage/test_files/test.jpg';
        $file = new UploadedFile($path, 'test.jpg', filesize($path), 'image/jpeg', null, true);       

        $data = [
            'name' => 'honda',
            'description' => 'This is a test description',
            'image' => $file       
        ];
        $response = $this->post('api/brands/create', $data);
        //dd($response->json());
        $response->assertStatus(200);
        dd($response->json());
        dd(Thumb::all()->toArray());
        $this->assertDatabaseHas('brands', [
            'name'=>'honda', 'description' => 'This is a test description'
        ]);  
        $this->assertDatabaseMissing('attachments', [
            'attachable_id'=>'1', 'attachable_type' => 'App\Brand', 'file_name'=> 'no-photo-available.jpg'
        ]);
        $this->assertDatabaseHas('attachments', [
            'attachable_id'=>'1', 'attachable_type' => 'App\Brand', 'file_extension'=> 'jpeg'
        ]);
        $attachment = Attachment::find(1);
        $this->assertFileExists($this->getFileForAttachment($attachment));
    }


    public function testBrandGetAll() {
        $auth = $this->loginAsAdmin();
        $path = dirname(__DIR__) . '/storage/test_files/test.jpg';
        $file = new UploadedFile($path, 'test.jpg', filesize($path), 'image/jpeg', null, true);       

        $data = [
            'name' => 'honda',
            'description' => 'This is a test description',
            'image' => $file       
        ];
        $response = $this->post('api/brands/create', $data);
        $data = [
            'name' => 'hunday',
            'description' => 'This is a test description',
            'image' => null       
        ];        
        $response = $this->post('api/brands/create', $data);

        $response = $this->post('api/brands', ['size' => 'medium']);
        dd($response->json());

        $this->assertFileExists($this->getFileForAttachment($attachment));
    }
}