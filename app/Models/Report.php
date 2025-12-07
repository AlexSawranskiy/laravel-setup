<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\Project;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'period_start',
        'period_end',
        'statistics',
        'file_path',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'statistics' => 'array',
    ];

   public static function generateReport(string $name, Carbon $start, Carbon $end, bool $generateFile = false): self
{
    // Validate input
    if (empty($name)) {
        throw new \InvalidArgumentException("Report name cannot be empty");
    }
    
    if (!$start || !$end) {
        throw new \InvalidArgumentException("Start and end dates are required");
    }

    $statistics = [];
    
    $projects = Project::withCount([
        'tasks as todo_count' => function ($query) use ($start, $end) {
            $query->where('status', 'todo')
                ->whereBetween('created_at', [$start, $end]);
        },
        'tasks as in_progress_count' => function ($query) use ($start, $end) {
            $query->where('status', 'in_progress')
                ->whereBetween('created_at', [$start, $end]);
        },
        'tasks as done_count' => function ($query) use ($start, $end) {
            $query->where('status', 'done')
                ->whereBetween('created_at', [$start, $end]);
        },
        'tasks as expired_count' => function ($query) use ($start, $end) {
            $query->where('status', '!=', 'done')
                ->where('due_date', '<', now())
                ->whereBetween('created_at', [$start, $end]);
        },
    ])->get();

    foreach ($projects as $project) {
        $statistics[$project->name] = [
            'todo' => $project->todo_count,
            'in_progress' => $project->in_progress_count,
            'done' => $project->done_count,
            'expired' => $project->expired_count,
            'total' => $project->todo_count + $project->in_progress_count + $project->done_count,
        ];
    }

    try {
        $report = new self();
        $report->name = $name;
        $report->period_start = $start->toDateString();
        $report->period_end = $end->toDateString();
        $report->statistics = $statistics;
        $report->save();

        if ($generateFile) {
            $report->generateReportFile();
        }

        return $report;
    } catch (\Exception $e) {
        \Log::error('Failed to generate report', [
            'name' => $name,
            'start' => $start->toDateString(),
            'end' => $end->toDateString(),
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        throw $e;
    }
}

    public function generateReportFile(): void
    {
        $fileName = 'reports/report_' . $this->id . '_' . now()->format('Y-m-d_His') . '.json';
        $filePath = storage_path('app/' . $fileName);
        
        if (!is_dir(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }
        
        file_put_contents($filePath, json_encode([
            'report_id' => $this->id,
            'name' => $this->name,
            'period' => [
                'start' => $this->period_start->toDateString(),
                'end' => $this->period_end->toDateString(),
            ],
            'generated_at' => now()->toDateTimeString(),
            'statistics' => $this->statistics,
        ], JSON_PRETTY_PRINT));

        $this->update(['file_path' => $fileName]);
    }
}