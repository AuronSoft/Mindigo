<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Chuyển dữ liệu cũ (string) sang json array trước khi đổi kiểu cột
        $rows = DB::table('assignments')->whereNotNull('file_path')->get();
        foreach ($rows as $row) {
            // Nếu đã là json hợp lệ thì bỏ qua
            if (json_decode($row->file_path) !== null) {
                continue;
            }
            DB::table('assignments')
                ->where('id', $row->id)
                ->update(['file_path' => json_encode([$row->file_path])]);
        }

        Schema::table('assignments', function (Blueprint $table) {
            $table->json('file_path')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->string('file_path')->nullable()->change();
        });

        // Chuyển ngược json array về string (lấy phần tử đầu tiên)
        $rows = DB::table('assignments')->whereNotNull('file_path')->get();
        foreach ($rows as $row) {
            $decoded = json_decode($row->file_path, true);
            $first = is_array($decoded) ? ($decoded[0] ?? null) : $row->file_path;
            DB::table('assignments')
                ->where('id', $row->id)
                ->update(['file_path' => $first]);
        }
    }
};
