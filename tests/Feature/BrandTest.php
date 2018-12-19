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
        $this->assertDatabaseMissing('attachments', [
            'attachable_id'=>'1', 'attachable_type' => 'App\Brand'
        ]);
    }
    public function testBrandCreateValidNonDefaultImage() {
        $auth = $this->loginAsAdmin();    
        $image = 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAMCAgMCAgMDAwMEAwMEBQgFBQQEBQoHBwYIDAoMDAsKCwsNDhIQDQ4RDgsLEBYQERMUFRUVDA8XGBYUGBIUFRT/2wBDAQMEBAUEBQkFBQkUDQsNFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBT/wAARCAAcADIDASIAAhEBAxEB/8QAGwAAAQUBAQAAAAAAAAAAAAAACQAFBgcIAwT/xAArEAABAwMDAwMDBQAAAAAAAAABAgMEAAURBhIhBzFBCBNxIlFSMkKCkcH/xAAZAQEBAQADAAAAAAAAAAAAAAAEAgEAAwX/xAAjEQACAgEEAAcAAAAAAAAAAAABAgARAxIhMfATIkFRYaGx/9oADAMBAAIRAxEAPwDbPUaxsXG1yS41vWlBKaHcv1EW9rXN1tF0gu2xqNJXEZfVkqTzjKk44yQPjNExv2Nivihc+tbUmmrh1Dkps1tLd4hFUefKZYGx3IGCpQ8g5Hb/ACtXGWBYcCcfIqkI25PHfiMfWvXcO92RVutbxkPuKSlxTA3YSAeM4++PNO/TsMxtJxIzcht9xpob/bVnBPOKysZSmZqUNOqjlR5dSCUDPn5qanVWrLHDiW+zTFOQW2QoOxIud2fyJByajIjZE1g7TUdEfw97IvpmhZSyASo5prW+hw/qGfPPNUbF1/q9j6n35Lq+ctPRTjH34AqTaOlXm5Xd6ZPyke3tCNpSM5yOKAyaeYsG5ZftGlXjRc8JA2A4HelR7l1CwXvGxVC29XPT/Ulo6zTWNL29+UxdiiSGW0hW91ZIOfPcYz4ood8UcH4rL/qLu7umERrrAbZbuKCXEyFNhSso+pIOeCM+DXtqxVa9DCBAXDgebgH2ur/IOvVtgn6anrteqNOyrXc452vNOYODVu6BtdwjQmHcoEFxlJS32UkY48V6uoXUm69YnIDOo24rpS+p1TsdotrcUR+7nH9AVKreyiPEZabTtQltIA+wxRMrUKizl1bV36jLOjoXklIJ7cimVLQS4VbRg8dqkk9ADqh4xmmkpBQeMZT4opO0icNjX4g/xpV1LCc9qVdUq5//2Q==';

        $data = [
            'name' => 'honda',
            'description' => 'This is a test description',
            'image' => $image      
        ];
        $response = $this->post('api/brands/create', $data);
        dd($response->json());
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

    public function testBrandDelete() {
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
        $response = $this->post('api/brands/delete', ['id' => 1]);
        dd(Attachment::all()->toArray());

        $this->assertFileExists($this->getFileForAttachment($attachment));
    }

}