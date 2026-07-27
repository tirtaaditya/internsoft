<?php

namespace App\Models;

use CodeIgniter\Model;

class DomainCheckModel extends Model
{
    protected $table            = 'domain_checks';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'domain_id',
        'checked_at',
        'status',
        'http_code',
        'response_time_ms',
        'error_message',
    ];

    protected bool $allowEmptyInserts = false;
    protected $useTimestamps         = false;
}
