<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\User;
use App\Account;
use App\kubiikslib\Helper;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;

class CreateAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');   
            $table->string('key',30)->unique(); 
            $table->string('password',255);
            $table->string('access',50)->default(Config::get('constants.ACCESS_DEFAULT'));            
            $table->timestamps();
        });

        $user = User::create([
            'firstName' => 'Sergi',           
            'lastName' => 'Redorta',
            'mobile' => '0623133212',
            'email' => 'sergi.redorta@hotmail.com',
            'emailValidationKey' => Helper::generateRandomStr(30),
            'language' => 'fr'
        ]);
        $account = new Account;
        $account->user_id = $user->id;
        $account->key = Helper::generateRandomStr(30);
        $account->password = Hash::make('Secure0', ['rounds' => 12]);
        $account->access = 'Membre';
        $user->accounts()->save($account); 
        $account = new Account;
        $account->user_id = $user->id;
        $account->key = Helper::generateRandomStr(30);
        $account->password = Hash::make('Secure0', ['rounds' => 12]);
        $account->access = 'Admin';
        $user->accounts()->save($account); 

        for ($x = 0; $x<100;$x++) {
            $user = User::create([
                'firstName' => 'Sergi' . $x,           
                'lastName' => 'Redorta' . $x,
                'mobile' => '06' . rand(11111111, 99999999),
                'email' => 'sergi'.$x.'redorta'.$x.'@hotmail'.$x.'.com',
                'emailValidationKey' => Helper::generateRandomStr(30),
                'language' => 'fr'
            ]);
            $account = new Account;
            $account->user_id = $user->id;
            $account->key = Helper::generateRandomStr(30);
            $account->password = Hash::make('Secure10', ['rounds' => 12]);
            $account->access = 'Membre';
            $user->accounts()->save($account); 
        }
        for ($x = 0; $x<10;$x++) {
            $user = User::create([
                'firstName' => 'Sergi' . $x,           
                'lastName' => 'Redorta' . $x,
                'mobile' => '06' . rand(11111111, 99999999),
                'email' => 'sergi.redorta'.$x.'@hotmail.com',
                'emailValidationKey' => Helper::generateRandomStr(30),
                'language' => 'fr'
            ]);
            $account = new Account;
            $account->user_id = $user->id;
            $account->key = Helper::generateRandomStr(30);
            $account->password = Hash::make('Secure10', ['rounds' => 12]);
            $account->access = 'Admin';
            $user->accounts()->save($account); 
        }


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('accounts');
    }
}
