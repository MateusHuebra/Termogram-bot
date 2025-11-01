<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use TelegramBot\Api\BotApi;

class DumpDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:dump';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Realiza backup do banco de dados';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $database = env('DB_DATABASE');
        $username = env('DB_USERNAME');
        $password = env('DB_PASSWORD');
        $host = env('DB_HOST', '127.0.0.1');
        
        $filename = $database . '_dump_' . now()->format('Y.m.d_H.i') . '.sql';
        $path = storage_path("app/db_dumps/{$filename}");

        $this->info("🔄 Gerando dump do banco de dados...");

        // Cria o comando mysqldump
        $command = "mysqldump --user={$username} --password={$password} --host={$host} {$database} > {$path}";
        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            $this->error('❌ Erro ao gerar o dump.');
            return Command::FAILURE;
        }

        $this->info("✅ Dump criado: {$path}");

        try {
            $this->sendViaTelegram($path, $database);
            $this->info("✅ Dump enviado com sucesso.");

        } catch(Exception $e) {
            $this->info('❌ Erro ao enviar o dump: ' . $e->getMessage());
        }

        return Command::SUCCESS;
    }

    private function sendViaTelegram(string $path, string $database)
    {
        $bot = new BotApi(env('TG_TOKEN'));
        $chatId = env('TG_DUMP_ID', env('TG_MY_ID'));
        $file = new \CURLFile($path);

        $bot->sendDocument($chatId, $file, "#{$database}_dump");
    }
}
