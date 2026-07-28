<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDownNotifiedToOutageEvents extends Migration
{
    public function up(): void
    {
        // Idempotent: skip jika kolom sudah ada.
        $db = db_connect();
        if (in_array('down_notified', $db->getFieldNames('outage_events'), true)) {
            return;
        }

        $this->forge->addColumn('outage_events', [
            'down_notified' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 0,
                'after'      => 'is_acknowledged',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('outage_events', 'down_notified');
    }
}
