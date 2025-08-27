<?php
namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'nama',
        'email',
        'password',
        'google_id',
        'role',
        'created_at',
        'updated_at',
        'profile_photo',
        'reset_token',
        'reset_expires'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    protected function hashPassword(array $data)
    {
        if (isset($data['data']['password'])) {
            $pwd = $data['data']['password'];
            if (!preg_match('/^\$2y\$/', (string) $pwd)) {
                $data['data']['password'] = password_hash($pwd, PASSWORD_DEFAULT);
            }
        }
        return $data;
    }

    public function findByEmail(string $email)
    {
        return $this->where('email', strtolower($email))->first();
    }
}
