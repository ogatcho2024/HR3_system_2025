<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class GenerateMlPredictions extends Command
{
    protected $signature = 'ml:generate-predictions {--approval-only} {--demand-only}';
    protected $description = 'Run offline ML prediction generation (approval + demand).';

    public function handle(): int
    {
        $pythonBin = config('ml.python_bin');
        $scriptsPath = config('ml.scripts_path');
        $script = $scriptsPath . DIRECTORY_SEPARATOR . 'generate_predictions.py';

        if (!file_exists($script)) {
            $this->error("ML script not found: {$script}");
            return self::FAILURE;
        }

        $args = [$pythonBin, $script];

        if ($this->option('approval-only')) {
            $args[] = '--approval-only';
        }
        if ($this->option('demand-only')) {
            $args[] = '--demand-only';
        }

        $process = new Process($args, base_path());
        $process->setTimeout(3600);
        $process->run(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        if (!$process->isSuccessful()) {
            $this->error('ML prediction generation failed.');
            return self::FAILURE;
        }

        $this->info('ML prediction generation completed.');
        return self::SUCCESS;
    }
}
