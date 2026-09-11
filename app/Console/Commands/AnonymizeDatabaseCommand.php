<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Domains\Estagiarios\Models\Estagiario;
use App\Domains\ControleDePonto\Models\RegistroPonto;
use App\Services\AnonymizationService;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;

class AnonymizeDatabaseCommand extends Command
{
    /**
     * O nome e a assinatura do comando do console.
     *
     * @var string
     */
    protected $signature = 'ponto:anonymize 
                            {--mode=faker : Modo de anonimização: "faker" (dados fictícios) ou "hash" (criptografia não reversível)} 
                            {--inactive-only : Anonimiza apenas os estagiários desativados (ds_situacao = 0)} 
                            {--force : Força a execução sem confirmação interativa}';

    /**
     * A descrição do comando do console.
     *
     * @var string
     */
    protected $description = 'Anonimiza dados pessoais sensíveis no banco de dados (LGPD) usando dados simulados ou criptografia não reversível.';

    /**
     * Executa o comando do console.
     */
    public function handle(AnonymizationService $anonymizer): int
    {
        $mode = strtolower($this->option('mode') ?: 'faker');
        if (!in_array($mode, ['faker', 'hash'])) {
            $this->error("Modo inválido '{$mode}'. Escolha 'faker' ou 'hash'.");
            return Command::FAILURE;
        }

        $inactiveOnly = (bool)$this->option('inactive-only');
        $force = (bool)$this->option('force');

        $this->info("=================================================");
        $this->info("  PONTO-ESTAGIO: ANONIMIZAÇÃO DE DADOS (LGPD)");
        $this->info("=================================================");
        $this->line("Modo selecionado: <fg=yellow>" . ($mode === 'hash' ? 'Criptografia Não Reversível (HMAC-SHA256)' : 'Dados Fictícios (Faker pt_BR)') . "</>");
        $this->line("Filtro de registros: <fg=yellow>" . ($inactiveOnly ? 'Apenas Estagiários Inativos' : 'Todos os Estagiários') . "</>");

        if (app()->environment('production') && !$force) {
            $this->error("ATENÇÃO: Você está em ambiente de PRODUÇÃO!");
            if (!$this->confirm('Deseja REALMENTE anonimizar os dados da base de dados? Esta operação pode ser irreversível!', false)) {
                $this->warn('Operação cancelada pelo usuário.');
                return Command::SUCCESS;
            }
        } elseif (!$force) {
            if (!$this->confirm('Deseja prosseguir com a anonimização dos registros selecionados?', true)) {
                $this->warn('Operação cancelada.');
                return Command::SUCCESS;
            }
        }

        $query = Estagiario::query();
        if ($inactiveOnly) {
            $query->where('ds_situacao', false);
        }

        $totalEstagiarios = $query->count();

        if ($totalEstagiarios === 0) {
            $this->warn('Nenhum estagiário encontrado para os critérios informados.');
            return Command::SUCCESS;
        }

        $this->info("\nAnonimizando {$totalEstagiarios} estagiário(s) e seus respectivos pontos...");

        $faker = Faker::create('pt_BR');
        $estagiariosAnonimizados = 0;
        $pontosAnonimizados = 0;

        DB::beginTransaction();

        try {
            $bar = $this->output->createProgressBar($totalEstagiarios);
            $bar->start();

            $query->chunkById(50, function ($estagiarios) use (&$estagiariosAnonimizados, &$pontosAnonimizados, $anonymizer, $mode, $faker, $bar) {
                foreach ($estagiarios as $estagiario) {
                    // Anonimiza dados do estagiário
                    $anonymizer->anonymizeEstagiario($estagiario, $mode, $faker);
                    $estagiariosAnonimizados++;

                    // Anonimiza registros de ponto associados
                    $registros = RegistroPonto::where('estagiario_id', $estagiario->id)->get();
                    foreach ($registros as $registro) {
                        $anonymizer->anonymizeRegistroPonto($registro, $mode);
                        $pontosAnonimizados++;
                    }

                    $bar->advance();
                }
            });

            $bar->finish();
            $this->newLine(2);

            DB::commit();

            $this->info("✔ Sucesso! Anonimização concluída com segurança.");
            $this->table(
                ['Métrica', 'Total'],
                [
                    ['Estagiários Anonimizados', $estagiariosAnonimizados],
                    ['Registros de Ponto Sanitizados', $pontosAnonimizados],
                    ['Modo Aplicado', $mode === 'hash' ? 'Criptografia Não Reversível' : 'Faker pt_BR'],
                    ['Status LGPD', 'Concluído'],
                ]
            );

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->newLine();
            $this->error("Falha durante a anonimização: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

