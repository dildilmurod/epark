<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class GenName extends Authenticatable
{
    public function __construct()
    {

    }

    protected function gen_name($file, $name)
    {
        //creates unique file name
        $fileName = $file->getClientOriginalName();
        $fileName = pathinfo($fileName, PATHINFO_FILENAME);
        //just takes file extension
        $ext = $file->getClientOriginalExtension();
        //filename to store
        if($name != '' || !empty($name)){
            $name = preg_replace('/\s+/', '_', $name);
            $fileToStore = $name . '_' . time() . '.' . $ext;
        }
        else {
            $fileToStore = md5(uniqid($fileName)) . '.' . $ext;
        }

        return $fileToStore;
    }


    public function generate($file, $name='')
    {
        return $this->gen_name($file, $name);
    }

}
