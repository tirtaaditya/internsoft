<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddConsecutiveFailuresToDomains extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('domains', [
            'consecutive_failures' => [
                'type'       => 'TINYINT',
                'constraint' => 3,
                'unsigned'   => true,
                'null'       => false,
                'default'    => 0,
                'after'      => 'last_checked_at',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('domains', 'consecutive_failures');
    }
}
