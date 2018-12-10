<?php
namespace Test\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Artisan;
use App\User;
use App\Account;

class AuthTest extends TestCase {

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
    ////////////////////////////////////////////////////////////////////////
    // Parameters testing
    ////////////////////////////////////////////////////////////////////////
    public function testSignupInvalidEmailAddress() {
        $data = [
            'email' => 'toto',
            'firstName' => 'titi',
            'lastName' => 'Redorta',
            'mobile' => '0623133213',
            'password'=> 'Secure0'            
        ];
        $response = $this->post('api/auth/signup', $data);
        $response->assertStatus(400)->assertJson(['response'=>'error', 'message'=>'validation.email']);
    }
}