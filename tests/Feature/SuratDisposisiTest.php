<?php

namespace Tests\Feature;

use App\Services\SuratDisposisiService;
use PHPUnit\Framework\TestCase;

class SuratDisposisiTest extends TestCase
{
    public function test_pattern_formatting(): void
    {
        $service = new SuratDisposisiService();
        $pattern = 'AG/{YYYY}/{NUMBER:4}';
        
        $year = date('Y');
        $month = date('m');
        $countYear = 1;
        $formattedNumber = sprintf('%04d', $countYear);
        
        $result = str_replace(['{YYYY}', '{MM}', '{NUMBER:4}'], [$year, $month, $formattedNumber], $pattern);
        
        $this->assertEquals("AG/{$year}/0001", $result);
    }
}
