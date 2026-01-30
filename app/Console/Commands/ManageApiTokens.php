<?php

namespace App\Console\Commands;

use App\Models\MoonshineUser;
use Illuminate\Console\Command;

class ManageApiTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:tokens 
                            {action : Действие: list, revoke, revoke-all}
                            {email : Email пользователя MoonShine}
                            {--token-id= : ID токена для удаления}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Управление API токенами пользователя';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $action = $this->argument('action');
        $email = $this->argument('email');

        $user = MoonshineUser::where('email', $email)->first();

        if (!$user) {
            $this->error("Пользователь с email '{$email}' не найден.");
            return self::FAILURE;
        }

        return match($action) {
            'list' => $this->listTokens($user),
            'revoke' => $this->revokeToken($user),
            'revoke-all' => $this->revokeAllTokens($user),
            default => $this->invalidAction($action),
        };
    }

    /**
     * Показать список всех токенов пользователя
     */
    private function listTokens(MoonshineUser $user): int
    {
        $tokens = $user->tokens;

        if ($tokens->isEmpty()) {
            $this->info("У пользователя {$user->email} нет активных токенов.");
            return self::SUCCESS;
        }

        $this->info("Токены пользователя: {$user->name} ({$user->email})");
        $this->newLine();

        $tableData = $tokens->map(function ($token) {
            return [
                $token->id,
                $token->name,
                $token->abilities ? implode(', ', $token->abilities) : 'все',
                $token->last_used_at ? $token->last_used_at->format('d.m.Y H:i:s') : 'никогда',
                $token->created_at->format('d.m.Y H:i:s'),
            ];
        })->toArray();

        $this->table(
            ['ID', 'Название', 'Права', 'Последнее использование', 'Создан'],
            $tableData
        );

        $this->newLine();
        $this->comment('Для удаления токена используйте: php artisan api:tokens revoke ' . $user->email . ' --token-id=ID');

        return self::SUCCESS;
    }

    /**
     * Удалить конкретный токен
     */
    private function revokeToken(MoonshineUser $user): int
    {
        $tokenId = $this->option('token-id');

        if (!$tokenId) {
            $this->error('Укажите ID токена с помощью --token-id=ID');
            $this->comment('Посмотреть список токенов: php artisan api:tokens list ' . $user->email);
            return self::FAILURE;
        }

        $token = $user->tokens()->where('id', $tokenId)->first();

        if (!$token) {
            $this->error("Токен с ID {$tokenId} не найден у пользователя {$user->email}");
            return self::FAILURE;
        }

        $tokenName = $token->name;
        $token->delete();

        $this->info("✅ Токен '{$tokenName}' (ID: {$tokenId}) успешно удалён.");

        return self::SUCCESS;
    }

    /**
     * Удалить все токены пользователя
     */
    private function revokeAllTokens(MoonshineUser $user): int
    {
        $count = $user->tokens()->count();

        if ($count === 0) {
            $this->info("У пользователя {$user->email} нет активных токенов.");
            return self::SUCCESS;
        }

        if (!$this->confirm("Удалить все {$count} токен(ов) пользователя {$user->email}?", false)) {
            $this->info('Отменено.');
            return self::SUCCESS;
        }

        $user->tokens()->delete();

        $this->info("✅ Все токены ({$count}) пользователя {$user->email} удалены.");

        return self::SUCCESS;
    }

    /**
     * Неверное действие
     */
    private function invalidAction(string $action): int
    {
        $this->error("Неизвестное действие: {$action}");
        $this->comment('Доступные действия: list, revoke, revoke-all');
        return self::FAILURE;
    }
}
