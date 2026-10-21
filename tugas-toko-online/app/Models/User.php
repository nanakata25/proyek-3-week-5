<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
class User extends Authenticatable
{
    protected $primaryKey = 'id_user';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id_user','nama_lengkap','email','username','password','no_hp','alamat'];
    protected $hidden = ['password'];
    // Role is assigned only by trusted seeders or RoleManagementService.
    protected $attributes = ['role' => 'customer'];
    protected function casts(): array { return ['password' => 'hashed']; }
}
