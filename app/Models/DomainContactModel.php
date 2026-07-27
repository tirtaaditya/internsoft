<?php

namespace App\Models;

use CodeIgniter\Model;

class DomainContactModel extends Model
{
    protected $table            = 'domain_contacts';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'domain_id',
        'phone_number',
    ];

    protected bool $allowEmptyInserts = false;

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
