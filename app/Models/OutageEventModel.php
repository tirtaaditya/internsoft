<?php

namespace App\Models;

use CodeIgniter\Model;

class OutageEventModel extends Model
{
    protected $table            = 'outage_events';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'domain_id',
        'started_at',
        'ended_at',
        'duration_seconds',
        'is_acknowledged',
    ];

    protected bool $allowEmptyInserts = false;

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
