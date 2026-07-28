<?php

namespace App\Models;

use CodeIgniter\Model;

class DomainModel extends Model
{
    protected $table            = 'domains';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'domain_url',
        'is_active',
        'last_status',
        'last_checked_at',
        'consecutive_failures',
    ];

    protected bool $allowEmptyInserts = false;

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
