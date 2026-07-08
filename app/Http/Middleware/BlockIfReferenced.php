<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class BlockIfReferenced
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $modelClass): Response
    {
        $id = $request->route('id') ?? $request->route(strtolower(class_basename($modelClass)));

        if (! $id) {
            return $next($request);
        }

        $model = $modelClass::find($id);
        if (! $model) {
            return $next($request);
        }

        $connectionType = DB::connection()->getDriverName();
        $tableName = $model->getTable();
        $hasReference = false;

        if ($connectionType === 'pgsql') {
            $referencingColumns = DB::select("
                SELECT 
                    tc.table_name, 
                    kcu.column_name 
                FROM 
                    information_schema.table_constraints AS tc 
                    JOIN information_schema.key_column_usage AS kcu
                      ON tc.constraint_name = kcu.constraint_name
                      AND tc.table_schema = kcu.table_schema
                    JOIN information_schema.constraint_column_usage AS ccu
                      ON ccu.constraint_name = tc.constraint_name
                      AND ccu.table_schema = tc.table_schema
                WHERE 
                    tc.constraint_type = 'FOREIGN KEY' 
                    AND ccu.table_name = ?
            ", [$tableName]);

            foreach ($referencingColumns as $ref) {
                $count = DB::table($ref->table_name)
                    ->where($ref->column_name, $id)
                    ->count();
                if ($count > 0) {
                    $hasReference = true;
                    break;
                }
            }
        } elseif ($connectionType === 'sqlite') {
            $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table'");
            foreach ($tables as $t) {
                $foreignKeys = DB::select("PRAGMA foreign_key_list('{$t->name}')");
                foreach ($foreignKeys as $fk) {
                    if ($fk->table === $tableName) {
                        $count = DB::table($t->name)
                            ->where($fk->from, $id)
                            ->count();
                        if ($count > 0) {
                            $hasReference = true;
                            break 2;
                        }
                    }
                }
            }
        }

        if ($hasReference) {
            return response()->json([
                'message' => 'Cannot delete this record because it is referenced by other records.',
            ], 400);
        }

        return $next($request);
    }
}
