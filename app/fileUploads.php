<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class fileUploads extends Model
{
    protected $table = 'file_upload';
        use SoftDeletes;
    protected $softDelete = true;

    protected $fillable = ['lecture_name','file_path','url'];

    protected $timstamp = false;

}
