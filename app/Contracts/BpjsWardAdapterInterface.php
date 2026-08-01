<?php

namespace App\Contracts;

interface BpjsWardAdapterInterface
{
    /**
     * Fetch ward availability from BPJS.
     */
    public function fetchWardAvailability(): array;
}
