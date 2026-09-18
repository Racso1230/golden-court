<?php

declare(strict_types=1);

use App\Domain\Users\Enums\Role;
use App\Domain\Users\Models\User;
use Illuminate\Support\Facades\DB;

it('defaults new users to the player role', function (): void {
    $id = DB::table('users')->insertGetId([
        'name' => 'Sam Player',
        'display_name' => 'sam',
        'email' => 'sam@example.com',
        'password' => 'irrelevant',
    ]);

    expect(User::findOrFail($id)->role)->toBe(Role::Player);
});

it('rejects a role outside the enum', function (): void {
    expectCheckViolation(fn () => DB::table('users')->insert([
        'name' => 'Sam Player',
        'display_name' => 'sam',
        'email' => 'sam@example.com',
        'password' => 'irrelevant',
        'role' => 'superuser',
    ]));
});

it('rejects a second account with the same email', function (): void {
    User::factory()->create(['email' => 'sam@example.com']);

    expectUniqueViolation(fn () => User::factory()->create(['email' => 'sam@example.com']));
});
