<?php

namespace App\Console\Commands;

use App\Models\MoonshineUser;
use Illuminate\Console\Command;

class GenerateApiToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:generate-token 
                            {email : Email пользователя MoonShine}
                            {--name=api-token : Название токена}
                            {--show-user : Показать информацию о пользователе}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Генерирует API токен для пользователя MoonShine';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email');
        $tokenName = $this->option('name');

        $user = MoonshineUser::where('email', $email)->first();

        if (!$user) {
            $this->error("Пользователь с email '{$email}' не найден.");
            return self::FAILURE;
        }

        if ($this->option('show-user')) {
            $this->info('Информация о пользователе:');
            $this->table(
                ['ID', 'Имя', 'Email', 'Создан'],
                [[
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->created_at->format('d.m.Y H:i:s')
                ]]
            );
            $this->newLine();
        }

        $token = $user->createToken($tokenName);

        $this->info('✅ API токен успешно создан!');
        $this->newLine();
        $this->line('Токен для пользователя: ' . $user->name . ' (' . $user->email . ')');
        $this->line('Название токена: ' . $tokenName);
        $this->newLine();
        $this->warn('═══════════════════════════════════════════════════════════════');
        $this->line($token->plainTextToken);
        $this->warn('═══════════════════════════════════════════════════════════════');
        $this->newLine();
        $this->comment('Используйте этот токен в заголовке Authorization:');
        $this->line('Authorization: Bearer ' . $token->plainTextToken);
        $this->newLine();
        $this->comment('💡 Сохраните токен в безопасном месте. Он больше не будет показан.');

        return self::SUCCESS;
    }
}
