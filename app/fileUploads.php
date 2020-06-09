<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class fileUploads extends Model
{
    protected $table = 'file_upload';

    protected $fillable = ['lecture_name','file_url'];

    protected $timstamp = false;

}
