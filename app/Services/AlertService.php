<?php

namespace App\Services;

use App\Models\Alert;

class AlertService
{
    public function markAsRead(Alert $alert): Alert
    {
        $alert->update([
            'status' => 'READ',
            'read_at' => now(),
        ]);

        return $alert;
    }
}
