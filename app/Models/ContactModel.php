<?php

namespace App\Models;

use CodeIgniter\Model;

class ContactModel extends Model
{
    protected $table         = 'contacts';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'name','email','phone','service','message',
        'ip_address','user_agent','status','created_at','updated_at'
    ];
    protected $useTimestamps = true;
}
