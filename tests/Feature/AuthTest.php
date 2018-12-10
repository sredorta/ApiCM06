<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Artisan;
use App\User;
use App\Account;

class AuthTest extends TestCase
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
    ////////////////////////////////////////////////////////////////////////
    // Parameters testing
    ////////////////////////////////////////////////////////////////////////
    public function testAuthSignupInvalidEmailAddress() {
        $data = [
            'email' => 'toto',
            'firstName' => 'titi',
            'lastName' => 'Redorta',
            'mobile' => '0623133213',
            'password'=> 'Secure0'            
        ];
        $response = $this->withHeaders(['Accept-Language'=> 'fr-FR,fr'])->post('api/auth/signup', $data);
        $response->assertStatus(400)->assertJson(['response'=>'error', 'message'=>'validation.email']);
    }
    public function testAuthSignupInvalidPhone() {
        $data = [
            'email' => 'sergi.redorta@hotmail.com',
            'firstName' => 'titi',
            'lastName' => 'Redorta',
            'mobile' => '0623aa133213',
            'password'=> 'Secure0'            
        ];
        $response = $this->post('api/auth/signup', $data);
        $response->assertStatus(400)->assertJson(['response'=>'error', 'message'=>'validation.max.string']);
    }

    public function testAuthSignupInvalidMissingfirstName() {
        $data = [
            'email' => 'sergi.redorta@hotmail.com',
            'lastName' => 'Redorta',
            'mobile' => '0623133213',
            'password'=> 'Secure0'            
        ];
        $response = $this->post('api/auth/signup', $data);
        $response->assertStatus(400)->assertJson(['response'=>'error', 'message'=>'validation.required']);
    }    

    public function testAuthSignupInvalidMissingLastName() {
        $data = [
            'email' => 'sergi.redorta@hotmail.com',
            'firstName' => 'Sergi',
            'mobile' => '0623133213',
            'password'=> 'Secure0'            
        ];
        $response = $this->post('api/auth/signup', $data);
        $response->assertStatus(400)->assertJson(['response'=>'error', 'message'=>'validation.required']);
    }

    public function testAuthSignupInvalidMissingEmail() {
        $data = [
            'lastName' => 'redorta',
            'firstName' => 'Sergi',
            'mobile' => '0623133213',
            'password'=> 'Secure0'            
        ];
        $response = $this->post('api/auth/signup', $data);
        //dd ($response->json());
        $response->assertStatus(400)->assertJson(['response'=>'error', 'message'=>'validation.required']);
    }    

    public function testAuthSignupInvalidMissingAll() {
        $data = [];
        $response = $this->post('api/auth/signup', $data);
        //dd ($response->json());
        $response->assertStatus(400)->assertJson(['response'=>'error', 'message'=>'validation.required']);
    }     

    public function testAuthSignupAlreadyRegistered() {
        $data = [
            'email' => 'sergi.redorta@hotmail.com',
            'firstName' => 'sergi',
            'lastName' => 'Redorta',
            'mobile' => '0623133213',
            'password'=> 'Secure0'            
        ];        
        $response = $this->post('api/auth/signup', $data);
        dd($response->json());
        //$response->assertStatus(200)->assertJson(['response'=>'success', 'message'=>'auth.signup_success']);
        //Recreate same user
/*        $response = $this->post('api/auth/signup', $data);
        $response->assertStatus(400)->assertJson(['response'=>'error', 'message'=>'auth.user_already_exists']);
        //Check phone
        $data['email'] = 'sergi.redorta2@hotmail.com';
        $response = $this->post('api/auth/signup', $data);
        $response->assertStatus(400)->assertJson(['response'=>'error', 'message'=>'auth.user_already_exists']);
        //Check email
        $data['phone'] = '0623133222';
        $data['email'] = 'sergi.redorta@hotmail.com';
        $response = $this->post('api/auth/signup', $data);
        $response->assertStatus(400)->assertJson(['response'=>'error', 'message'=>'auth.user_already_exists']);
        //dd ($response->json());*/
    }


}
