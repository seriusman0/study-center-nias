<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_roles', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['user_id', 'role_id']);
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->primary(['role_id', 'permission_id']);
        });

        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('student_number', 50)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('birth_place', 100)->nullable();
            $table->string('gender', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('guardian_name', 100)->nullable();
            $table->string('guardian_phone', 50)->nullable();
            $table->string('school_name', 150)->nullable();
            $table->unsignedSmallInteger('entry_year')->nullable();
            $table->timestamps();
        });

        Schema::create('mentor_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('expertise', 150)->nullable();
            $table->text('bio')->nullable();
            $table->string('education', 150)->nullable();
            $table->unsignedTinyInteger('experience_years')->nullable();
            $table->decimal('hourly_rate', 12, 2)->nullable();
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });

        Schema::create('admin_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('employee_number', 50)->nullable();
            $table->string('department', 100)->nullable();
            $table->string('position', 100)->nullable();
            $table->timestamps();
        });

        $this->migrateExistingData();
        $this->dropOldUserColumns();
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('bio')->constrained()->nullOnDelete();
            $table->date('tanggal_lahir')->nullable()->after('bio');
            $table->string('tempat_lahir')->nullable()->after('tanggal_lahir');
            $table->string('no_hp_orangtua')->nullable()->after('tempat_lahir');
            $table->string('nama_sekolah')->nullable()->after('no_hp_orangtua');
            $table->text('alamat')->nullable()->after('nama_sekolah');
            $table->unsignedSmallInteger('tahun_masuk')->nullable()->after('alamat');
        });

        Schema::dropIfExists('admin_profiles');
        Schema::dropIfExists('mentor_profiles');
        Schema::dropIfExists('student_profiles');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('user_roles');
    }

    private function migrateExistingData(): void
    {
        if (! Schema::hasColumn('users', 'role_id')) {
            return;
        }

        $now = now();

        DB::table('users')
            ->whereNotNull('role_id')
            ->select('id', 'role_id')
            ->orderBy('id')
            ->chunk(500, function ($users) use ($now) {
                $rows = [];
                foreach ($users as $u) {
                    $rows[] = [
                        'user_id'    => $u->id,
                        'role_id'    => $u->role_id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                if ($rows) {
                    DB::table('user_roles')->insertOrIgnore($rows);
                }
            });

        $studentRoleId = DB::table('roles')->where('name', 'student')->value('id');
        if (! $studentRoleId) {
            return;
        }

        $studentColumns = ['tanggal_lahir', 'tempat_lahir', 'no_hp_orangtua', 'nama_sekolah', 'alamat', 'tahun_masuk'];
        $present = array_filter($studentColumns, fn($c) => Schema::hasColumn('users', $c));
        if (! $present) {
            return;
        }

        $select = array_merge(['users.id'], array_map(fn($c) => "users.$c", $present));
        DB::table('users')
            ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
            ->where('user_roles.role_id', $studentRoleId)
            ->select($select)
            ->orderBy('users.id')
            ->chunk(500, function ($users) use ($now, $present) {
                $rows = [];
                foreach ($users as $u) {
                    $hasData = false;
                    foreach ($present as $c) {
                        if (! is_null($u->$c) && $u->$c !== '') {
                            $hasData = true;
                            break;
                        }
                    }
                    if (! $hasData) {
                        continue;
                    }
                    $rows[] = [
                        'user_id'        => $u->id,
                        'birth_date'     => in_array('tanggal_lahir', $present) ? $u->tanggal_lahir : null,
                        'birth_place'    => in_array('tempat_lahir', $present) ? $u->tempat_lahir : null,
                        'guardian_phone' => in_array('no_hp_orangtua', $present) ? $u->no_hp_orangtua : null,
                        'school_name'    => in_array('nama_sekolah', $present) ? $u->nama_sekolah : null,
                        'address'        => in_array('alamat', $present) ? $u->alamat : null,
                        'entry_year'     => in_array('tahun_masuk', $present) ? $u->tahun_masuk : null,
                        'created_at'     => $now,
                        'updated_at'     => $now,
                    ];
                }
                if ($rows) {
                    DB::table('student_profiles')->insertOrIgnore($rows);
                }
            });
    }

    private function dropOldUserColumns(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'role_id')) {
                $table->dropForeign(['role_id']);
                $table->dropColumn('role_id');
            }
        });

        $cols = ['tanggal_lahir', 'tempat_lahir', 'no_hp_orangtua', 'nama_sekolah', 'alamat', 'tahun_masuk'];
        $present = array_filter($cols, fn($c) => Schema::hasColumn('users', $c));
        if ($present) {
            Schema::table('users', function (Blueprint $table) use ($present) {
                $table->dropColumn($present);
            });
        }
    }
};
