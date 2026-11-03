<?php

declare(strict_types=1);

namespace App\Console\Commands\XarxesGest;

use App\Actions\User\CreateUser;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

/**
 * Creates the first administrator of a self-hosted install without the
 * well-known default credentials of UserSeeder. Self-hosted registration is
 * invite-only, so this is how the owner gets in for the first time.
 */
class CreateAdminCommand extends Command
{
    protected $signature = 'xarxesgest:create-admin
        {--name= : Full name}
        {--email= : Login email}
        {--locale=ca : Interface language}
        {--timezone=Europe/Madrid : Timezone}';

    protected $description = 'Create the owner account of a self-hosted XarxesGest install';

    public function handle(): int
    {
        $name = $this->option('name') ?: text('Name', required: true);
        $email = $this->option('email') ?: text('Email', required: true);
        $plainPassword = $this->resolvePassword();

        if (blank($plainPassword)) {
            $this->error('No password received. In a shell where PHP does not see a terminal, pass it in XARXESGEST_ADMIN_PASSWORD:');
            $this->line('  read -rsp "Password: " P && echo && XARXESGEST_ADMIN_PASSWORD="$P" php artisan xarxesgest:create-admin --name="..." --email=...; unset P');

            return self::FAILURE;
        }

        $validator = Validator::make(
            ['name' => $name, 'email' => $email, 'password' => $plainPassword],
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255'],
                'password' => ['required', Password::min(12)],
            ],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        if (User::query()->where('email', $email)->exists()) {
            $this->error("A user with email {$email} already exists.");

            return self::FAILURE;
        }

        CreateUser::execute([
            'name' => $name,
            'email' => $email,
            'password' => $plainPassword,
            'email_verified_at' => now(),
            'locale' => (string) $this->option('locale'),
            'timezone' => (string) $this->option('timezone'),
        ]);

        $this->info("Owner account created for {$email}.");

        return self::SUCCESS;
    }

    /**
     * XARXESGEST_ADMIN_PASSWORD (automated provisioning) wins; a terminal gets
     * a hidden prompt; otherwise the password is read from piped stdin. Some
     * hosts (CloudPanel over `su -`) report no terminal and an empty stdin, so
     * an empty result is reported with the environment variable as the way out.
     */
    private function resolvePassword(): string
    {
        return match (true) {
            filled(getenv('XARXESGEST_ADMIN_PASSWORD')) => (string) getenv('XARXESGEST_ADMIN_PASSWORD'),
            stream_isatty(STDIN) => password('Password (min. 12 characters)', required: true),
            default => trim((string) fgets(STDIN)),
        };
    }
}
