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
use App\Configuration;

class ConfigurationTest extends TestCase
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
    public function testGetConfig() {
        $response = $this->get('api/config');
        dd(Configuration::all()->toArray());
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
}