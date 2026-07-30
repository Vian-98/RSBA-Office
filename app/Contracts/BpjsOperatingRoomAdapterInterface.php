<?php

namespace App\Contracts;

interface BpjsOperatingRoomAdapterInterface
{
    /**
     * Fetch operating room schedules from BPJS.
     */
    public function fetchOperatingRoomSchedules(): array;
}
