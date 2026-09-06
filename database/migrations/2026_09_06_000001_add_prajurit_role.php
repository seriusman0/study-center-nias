<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        DB::table('roles')->insertOrIgnore([
            'name'        => 'prajurit',
            'description' => 'Prajurit — dapat mengisi jurnal melalui scan QR',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    public function down(): void {
        DB::table('roles')->where('name', 'prajurit')->delete();
    }
};
