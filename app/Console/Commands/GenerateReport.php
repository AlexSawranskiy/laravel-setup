<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Report;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GenerateReport extends Command
{
    protected $signature = 'app:generate-report 
                            {--N|name= : Назва звіту}
                            {--S|start= : Початкова дата (формат: YYYY-MM-DD)}
                            {--E|end= : Кінцева дата (формат: YYYY-MM-DD)}
                            {--F|file : Зберегти звіт у файл}';

    protected $description = 'Генерує звіт по задачах у проєктах за вказаний період';

    public function handle()
    {
        $this->info('🚀 Початок генерації звіту...');

        // Set date range
        $start = $this->option('start') 
            ? Carbon::parse($this->option('start')) 
            : now()->startOfMonth();
        
        $end = $this->option('end')
            ? Carbon::parse($this->option('end'))
            : now()->endOfMonth();

        $name = $this->option('name') ?? 'Звіт за період ' . $start->format('d.m.Y') . ' - ' . $end->format('d.m.Y');

        $this->info("🔍 Параметри звіту:");
        $this->info("Назва: " . $name);
        $this->info("Період: " . $start->format('d.m.Y') . ' - ' . $end->format('d.m.Y'));

        try {
            // Get tasks statistics
            $statistics = $this->getTasksStatistics($start, $end);

            // Create report
            $report = new Report();
            $report->name = $name;
            $report->period_start = $start;
            $report->period_end = $end;
            $report->statistics = $statistics;
            
            if ($this->option('file')) {
                $fileName = 'reports/report_' . now()->format('Y-m-d_His') . '.json';
                $filePath = storage_path('app/' . $fileName);
                
                if (!is_dir(dirname($filePath))) {
                    mkdir(dirname($filePath), 0755, true);
                }
                
                file_put_contents($filePath, json_encode($statistics, JSON_PRETTY_PRINT));
                $report->file_path = $fileName;
            }

            $report->save();

            $this->info("✅ Звіт успішно згенеровано! ID: {$report->id}");

            if ($this->option('file')) {
                $this->info("📄 Файл звіту збережено: storage/app/{$report->file_path}");
            }

            // Display report summary
            $this->displayReportSummary($statistics);

        } catch (\Exception $e) {
            $this->error("❌ Помилка при генерації звіту: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * Get tasks statistics for the given period
     */
    private function getTasksStatistics(Carbon $start, Carbon $end): array
    {
        return [
            'total_tasks' => DB::table('tasks')
                ->whereBetween('created_at', [$start, $end])
                ->count(),
            'completed_tasks' => DB::table('tasks')
                ->where('status', 'done')
                ->whereBetween('created_at', [$start, $end])
                ->count(),
            'in_progress_tasks' => DB::table('tasks')
                ->where('status', 'in_progress')
                ->whereBetween('created_at', [$start, $end])
                ->count(),
            'overdue_tasks' => DB::table('tasks')
                ->where('due_date', '<', now())
                ->where('status', '!=', 'done')
                ->whereBetween('created_at', [$start, $end])
                ->count(),
        ];
    }

    /**
     * Display report summary in a table
     */
    private function displayReportSummary(array $statistics): void
    {
        $this->info("\n📊 Підсумок звіту:");
        $this->table(
            ['Показник', 'Кількість'],
            [
                ['Всього завдань', $statistics['total_tasks']],
                ['Виконано', $statistics['completed_tasks']],
                ['В роботі', $statistics['in_progress_tasks']],
                ['Протерміновано', $statistics['overdue_tasks']],
            ]
        );
    }
}