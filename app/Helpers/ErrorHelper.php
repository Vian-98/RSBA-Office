<?php

namespace App\Helpers;

use Throwable;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ErrorHelper
{
    /**
     * Get user-friendly error message based on exception type
     * ALWAYS return user-friendly message, regardless of environment
     */
    public static function getUserMessage(Throwable $e): string
    {
        return match (true) {
            $e instanceof QueryException => self::handleDatabaseError($e),
            $e instanceof ValidationException => 'Data yang dimasukkan tidak valid.',
            $e instanceof HttpException => self::handleHttpError($e),
            default => 'Terjadi kesalahan sistem. Silakan coba lagi.'
        };
    }

    /**
     * Get detailed error message for debugging (only in development)
     */
    public static function getDetailedMessage(Throwable $e): string
    {
        if (app()->environment('production')) {
            return self::getUserMessage($e);
        }

        return $e->getMessage();
    }

    /**
     * Handle database-specific errors
     */
    private static function handleDatabaseError(QueryException $e): string
    {
        $errorCode = $e->errorInfo[1] ?? null;
        $message = $e->getMessage();

        return match (true) {
            // Duplicate entry
            str_contains($message, 'Duplicate entry') || $errorCode === 1062
            => 'Data sudah ada sebelumnya.',

            // Foreign key constraint - Cannot delete
            str_contains($message, 'foreign key constraint') || $errorCode === 1451
            => 'Data tidak dapat dihapus karena masih terkait dengan data lain.',

            // Foreign key constraint - Cannot add or update child row
            $errorCode === 1452
            => 'Data yang direferensikan tidak ditemukan.',

            // Column cannot be null
            str_contains($message, 'cannot be null') || $errorCode === 1048
            => self::parseNullColumnError($message),

            // Data too long
            str_contains($message, 'Data too long') || $errorCode === 1406
            => self::parseDataTooLongError($message),

            // Table doesn't exist
            str_contains($message, "doesn't exist") || $errorCode === 1146
            => 'Terjadi kesalahan konfigurasi database.',

            // Unknown column
            str_contains($message, 'Unknown column') || $errorCode === 1054
            => 'Terjadi kesalahan pada field data.',

            // Incorrect value
            str_contains($message, 'Incorrect') || $errorCode === 1366
            => 'Format data tidak sesuai.',

            default
            => 'Terjadi kesalahan pada database.'
        };
    }

    /**
     * Parse column name from "cannot be null" error
     */
    private static function parseNullColumnError(string $message): string
    {
        // Extract column name from error message
        if (preg_match("/Column '([^']+)' cannot be null/", $message, $matches)) {
            $columnName = $matches[1];
            $readableName = self::makeColumnReadable($columnName);
            return "Field '{$readableName}' harus diisi.";
        }

        return 'Terdapat field yang harus diisi.';
    }

    /**
     * Parse column name from "Data too long" error
     */
    private static function parseDataTooLongError(string $message): string
    {
        // Extract column name from error message
        if (preg_match("/Data too long for column '([^']+)'/", $message, $matches)) {
            $columnName = $matches[1];
            $readableName = self::makeColumnReadable($columnName);
            return "Data pada field '{$readableName}' terlalu panjang.";
        }

        return 'Data yang dimasukkan terlalu panjang.';
    }

    /**
     * Convert snake_case column name to readable format
     */
    private static function makeColumnReadable(string $columnName): string
    {
        // Remove table prefix if exists (e.g., um_opname_stok_details -> stok_details)
        $columnName = preg_replace('/^[a-z]+_/', '', $columnName);

        // Convert snake_case to Title Case
        return ucwords(str_replace('_', ' ', $columnName));
    }

    /**
     * Handle HTTP exceptions
     */
    private static function handleHttpError(HttpException $e): string
    {
        return match ($e->getStatusCode()) {
            404 => 'Data tidak ditemukan.',
            403 => 'Anda tidak memiliki akses.',
            401 => 'Silakan login terlebih dahulu.',
            500 => 'Terjadi kesalahan server.',
            503 => 'Layanan sedang tidak tersedia.',
            default => 'Terjadi kesalahan sistem.'
        };
    }

    /**
     * Log error with context
     */
    public static function log(Throwable $e, array $context = []): void
    {
        $logData = array_merge([
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'user_id' => auth()->id(),
            'url' => request()->fullUrl(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ], $context);

        // Add SQL for QueryException
        if ($e instanceof QueryException) {
            $logData['sql'] = $e->getSql();
            $logData['bindings'] = $e->getBindings();
            $logData['error_code'] = $e->errorInfo[1] ?? null;
        }

        Log::error($e->getMessage(), $logData);
    }

    /**
     * Handle exception and return user message (combines get and log)
     * This ALWAYS returns user-friendly message
     */
    public static function handle(Throwable $e, array $context = []): string
    {
        self::log($e, $context);
        return self::getUserMessage($e);
    }

    /**
     * Handle exception and return both user message and detailed message
     * Useful for development/debugging
     */
    public static function handleWithDetails(Throwable $e, array $context = []): array
    {
        self::log($e, $context);

        return [
            'user_message' => self::getUserMessage($e),
            'detailed_message' => self::getDetailedMessage($e),
            'error_code' => $e instanceof QueryException ? ($e->errorInfo[1] ?? null) : null,
        ];
    }
}
