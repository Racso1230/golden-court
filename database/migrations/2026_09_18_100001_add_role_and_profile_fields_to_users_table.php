<?php

declare(strict_types=1);

use App\Domain\Users\Enums\Role;
use App\Support\Database\CheckConstraint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('role')->default(Role::Player->value)->after('email');
            $table->string('display_name')->nullable()->after('name');
            $table->text('bio')->nullable()->after('display_name');
        });

        // Existing accounts keep their account name as their public name.
        DB::table('users')->whereNull('display_name')->update(['display_name' => DB::raw('name')]);

        Schema::table('users', function (Blueprint $table): void {
            $table->string('display_name')->nullable(false)->change();
        });

        CheckConstraint::enum('users', 'role', Role::class);
    }

    public function down(): void
    {
        CheckConstraint::drop('users', CheckConstraint::enumName('users', 'role'));

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['role', 'display_name', 'bio']);
        });
    }
};
