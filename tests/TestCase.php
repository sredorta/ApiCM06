<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Exceptions\Handler;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use JWTAuth;
use App\kubiikslib\Helper;
use Artisan;
use App\User;
use App\Account;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected $token = null;    //Stores the token
    protected $user = null;     //Stores the auth user


    /////////////////////////////////////////////////////////////////////////////////
    //Creates an user and updates email as valid
    /////////////////////////////////////////////////////////////////////////////////
    protected function signup($data = []) {
        $default = [
            'email' => 'sergi.redorta@hotmail.com',
            'firstName' => 'Sergi',
            'lastName' => 'Redorta',
            'mobile' => '0623133212',
            'password'=> 'Secure0'            
        ];
        $response = $this->post('api/auth/signup', array_merge($default, $data));
        $response->assertStatus(200);

        //We now update isEmailValidated to get access to the user
        if (User::all()->count()>0) {
            $user = User::all()->last();
            $user->isEmailValidated = true;
            $user->update();  
        }

        return $response;   //We return resonse for tests of Auth      
    }

    /////////////////////////////////////////////////////////////////////////////////
    //Login to the specified user
    /////////////////////////////////////////////////////////////////////////////////
    protected function login($data = []) {
        $default = [
            'email' => 'sergi.redorta@hotmail.com',
            'password' => 'Secure0',
            'keepconnected' => false
        ];
        //Now we login and get the token
        $response = $this->post('api/auth/login', array_merge($default, $data));
        $response->assertStatus(200);  //expected status

        //Now get the Auth user
        $result = $response->json();
        $token = $result['token']; //This is our response 
        $this->token = $token;
    }

    /////////////////////////////////////////////////////////////////////////////////
    //Get authenticated user
    /////////////////////////////////////////////////////////////////////////////////
    protected function getAuthUser() {
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])->get('api/auth/user');
        $result = $response->json();
        $user = User::find($result['id']); 
        $this->user = $user;      
    }
    /////////////////////////////////////////////////////////////////////////////////
    //Create a user and login and return the authenticated user Admnin account
    /////////////////////////////////////////////////////////////////////////////////
    protected function loginAsAdmin($data = []) {
        $default = [
            'email' => 'sergi.redorta@hotmail.com',
            'firstName' => 'Sergi',
            'lastName' => 'Redorta',
            'mobile' => '0623133212',
            'password'=> 'Secure0',
            'access' => Config::get('constants.ACCESS_ADMIN')
        ];        
        $this->signup(array_merge($default, $data));
        //Check if is first account and has already admin account, if not create one
        $user = User::all()->last();
        if ($user->accounts()->get()->count()==1) {
            $account = new Account;
            $account->user_id = $user->id;
            $account->key = Helper::generateRandomStr(30);
            $account->password = Hash::make('Secure0', ['rounds' => 12]);
            $account->access = Config::get('constants.ACCESS_ADMIN');
            $user->accounts()->save($account); 
        }
        //dd($user->accounts()->get()->toArray());
        $this->login(array_merge($default, $data));
        return $this->getAuthUser();
    }

    /////////////////////////////////////////////////////////////////////////////////
    //Create a user and login and return the authenticated user Member account
    /////////////////////////////////////////////////////////////////////////////////
    protected function loginAsMember($data = []) {
        $default = [
            'email' => 'sergi.redorta@hotmail.com',
            'firstName' => 'Sergi',
            'lastName' => 'Redorta',
            'mobile' => '0623133212',
            'password'=> 'Secure0',
            'access' => Config::get('constants.ACCESS_MEMBER')
        ];        
        $this->signup(array_merge($default, $data));
        $this->login(array_merge($default, $data));
        return $this->getAuthUser();
    }    



/*    /////////////////////////////////////////////////////////////////////////////////
    //Create a user and login and return the authenticated user
    /////////////////////////////////////////////////////////////////////////////////
    protected function loginAs($data = []) {
        $default = [
            'email' => 'sergi.redorta@hotmail.com',
            'firstName' => 'Sergi',
            'lastName' => 'Redorta',
            'mobile' => '0623133212',
            'password'=> 'Secure0'
        ];        
        $this->signup(array_merge($default, $data));
        $this->login(array_merge($default, $data));
        return $this->getAuthUser();
    }
    /////////////////////////////////////////////////////////////////////////////////
    //Create a user and login and return the authenticated user Member account
    /////////////////////////////////////////////////////////////////////////////////
    public function loginAsMember() {
        $this->signup();
        $user = User::all()->last();
        $account = new Account;
        $account->user_id = $user->id;
        $account->key = Helper::generateRandomStr(30);
        $account->password = Hash::make('Secure10', ['rounds' => 12]);
        $account->access = Config::get('constants.ACCESS_MEMBER');
        $user->accounts()->save($account); 
        $this->login(['password'=>'Secure10','access' => Config::get('constants.ACCESS_MEMBER')]);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])->get('api/auth/user');
    }


    /////////////////////////////////////////////////////////////////////////////////
    //Create a user with multiple access and login with the required access
    /////////////////////////////////////////////////////////////////////////////////
    protected function loginAsMultiple($data = []) {
        $default = [
            'email' => 'sergi.redorta@hotmail.com',
            'password'=> 'Secure0',
            'access' => Config::get('constants.ACCESS_DEFAULT')
        ];        
        $signupData = [
            'email' => 'sergi.redorta@hotmail.com',
            'firstName' => 'Sergi',
            'lastName' => 'Redorta',
            'mobile' => '0623133212',
            'password'=> 'Secure0'
        ];  
        $this->signup($signupData);
        $user = User::all()->last();
        //Add the other access types
        $account = new Account;
        $account->user_id = $user->id;
        $account->key = Helper::generateRandomStr(30);
        $account->password = Hash::make('Secure1', ['rounds' => 12]);
        $account->access = Config::get('constants.ACCESS_MEMBER');
        $user->accounts()->save($account);  

        $account = new Account;
        $account->user_id = $user->id;
        $account->key = Helper::generateRandomStr(30);
        $account->password = Hash::make('Secure2', ['rounds' => 12]);
        $account->access = Config::get('constants.ACCESS_ADMIN');
        $user->accounts()->save($account);  
        $this->login(array_merge($default, $data));
        return $this->getAuthUser();
    }*/

    /////////////////////////////////////////////////////////////////////////////////
    // Logout current user
    /////////////////////////////////////////////////////////////////////////////////
    protected function logout() {
        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])->post('api/auth/logout');
        $this->token = null;
    }

    /////////////////////////////////////////////////////////////////////////////////
    // Invalidate token
    /////////////////////////////////////////////////////////////////////////////////
    protected function invalidateToken() {
        JWTAuth::invalidate($this->token);
    }

}
