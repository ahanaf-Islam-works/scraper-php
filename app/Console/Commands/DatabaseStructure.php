<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DatabaseStructure extends Command
{
    protected $signature = 'db:structure';

    protected $description = 'Display complete database structure with foreign keys';

    public function handle(): int
    {
        $tables = DB::select("
            SELECT table_name
            FROM information_schema.tables
            WHERE table_schema = current_schema()
            AND table_type = 'BASE TABLE'
            ORDER BY table_name
        ");

        $structure = [];

        foreach ($tables as $table) {
            $tableName = $table->table_name;

            // Fetch columns
            $columns = DB::select("
                SELECT
                    c.column_name,
                    c.data_type,
                    c.is_nullable,
                    c.column_default,
                    c.character_maximum_length,
                    CASE
                        WHEN pk.column_name IS NOT NULL THEN true
                        ELSE false
                    END AS is_primary_key
                FROM information_schema.columns c
                LEFT JOIN (
                    SELECT
                        ku.column_name,
                        ku.table_name
                    FROM information_schema.table_constraints tc
                    JOIN information_schema.key_column_usage ku
                        ON tc.constraint_name = ku.constraint_name
                        AND tc.table_schema = ku.table_schema
                    WHERE tc.constraint_type = 'PRIMARY KEY'
                    AND tc.table_schema = current_schema()
                ) pk
                    ON pk.table_name = c.table_name
                    AND pk.column_name = c.column_name
                WHERE c.table_schema = current_schema()
                AND c.table_name = ?
                ORDER BY c.ordinal_position
            ", [$tableName]);

            // Fetch foreign keys
            $foreignKeys = DB::select("
                SELECT
                    kcu.column_name,
                    ccu.table_name AS referenced_table,
                    ccu.column_name AS referenced_column,
                    tc.constraint_name
                FROM information_schema.table_constraints tc
                JOIN information_schema.key_column_usage kcu
                    ON tc.constraint_name = kcu.constraint_name
                    AND tc.table_schema = kcu.table_schema
                JOIN information_schema.constraint_column_usage ccu
                    ON ccu.constraint_name = tc.constraint_name
                    AND ccu.constraint_schema = tc.constraint_schema
                WHERE tc.constraint_type = 'FOREIGN KEY'
                AND tc.table_schema = current_schema()
                AND tc.table_name = ?
            ", [$tableName]);

            // Fetch indexes
            $indexes = DB::select("
                SELECT
                    indexname,
                    indexdef
                FROM pg_indexes
                WHERE schemaname = current_schema()
                AND tablename = ?
            ", [$tableName]);

            $structure[$tableName] = [
                'columns' => $columns,
                'foreign_keys' => $foreignKeys,
                'indexes' => $indexes,
            ];
        }

        $json = json_encode(
            $structure,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );

        $path = storage_path('app/database-structure.json');

        file_put_contents($path, $json);

        $this->info("Database structure exported successfully!");
        $this->info("File: {$path}");

        return self::SUCCESS;
    }
}
