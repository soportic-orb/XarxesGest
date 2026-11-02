<?php

declare(strict_types=1);

use App\Enums\User\Locale;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

afterEach(fn () => putenv('XARXESGEST_ADMIN_PASSWORD'));

test('creates a verified owner account in Catalan', function () {
    putenv('XARXESGEST_ADMIN_PASSWORD=una-contrasenya-llarga');

    $this->artisan('xarxesgest:create-admin', ['--name' => 'Octavi', '--email' => 'octavi@soportic.es'])
        ->assertSuccessful();

    $user = User::query()->where('email', 'octavi@soportic.es')->sole();

    expect($user->email_verified_at)->not->toBeNull()
        ->and($user->locale)->toBe(Locale::Catalan)
        ->and(Hash::check('una-contrasenya-llarga', $user->password))->toBeTrue();
});

test('rejects short passwords', function () {
    putenv('XARXESGEST_ADMIN_PASSWORD=curta');

    $this->artisan('xarxesgest:create-admin', ['--name' => 'Octavi', '--email' => 'octavi@soportic.es'])
        ->assertFailed();

    expect(User::query()->where('email', 'octavi@soportic.es')->exists())->toBeFalse();
});

test('refuses an email that already exists', function () {
    putenv('XARXESGEST_ADMIN_PASSWORD=una-contrasenya-llarga');
    User::factory()->create(['email' => 'octavi@soportic.es']);

    $this->artisan('xarxesgest:create-admin', ['--name' => 'Octavi', '--email' => 'octavi@soportic.es'])
        ->assertFailed();
});
