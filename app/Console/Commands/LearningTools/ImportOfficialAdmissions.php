<?php

namespace App\Console\Commands\LearningTools;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Mindigo\LearningTools\Models\AdmissionProgram;
use Mindigo\LearningTools\Models\University;
use RuntimeException;

class ImportOfficialAdmissions extends Command
{
    protected $signature = 'learning-tools:import-admissions {file : UTF-8 CSV file} {--dry-run : Validate without writing}';

    protected $description = 'Validate and import admission data published by official institutions';

    public function handle(): int
    {
        $path = realpath((string) $this->argument('file'));
        if (! $path || ! is_readable($path)) {
            $this->error('Admission CSV file was not found: '.$this->argument('file'));
            $this->line('The text "path\\data.csv" is only an example. Use an existing absolute path or a path relative to the project root.');
            $this->line('CSV template: packages/Mindigo/LearningTools/src/resources/templates/admissions-import.csv');

            return self::FAILURE;
        }
        try {
            $rows = $this->readRows($path);
            if ($rows->isEmpty()) {
                throw new RuntimeException('The CSV contains headers only. Add verified records from official admission announcements before importing.');
            }
            if (! $this->option('dry-run')) {
                DB::transaction(fn () => $rows->each(fn (array $row) => $this->persist($row)));
            }
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
        $this->info(($this->option('dry-run') ? 'Validated' : 'Imported').' '.$rows->count().' official admission records.');

        return self::SUCCESS;
    }

    private function readRows(string $path)
    {
        $handle = fopen($path, 'rb');
        $headers = fgetcsv($handle) ?: [];
        $required = ['university_code', 'university_name', 'major_code', 'major_name', 'year', 'method', 'combinations', 'source_url', 'source_name', 'published_at'];
        if (array_diff($required, $headers)) {
            throw new RuntimeException('CSV is missing required official-data columns.');
        }
        $rows = collect();
        $line = 1;
        while (($values = fgetcsv($handle)) !== false) {
            $line++;
            if (count($values) !== count($headers)) {
                throw new RuntimeException("Invalid column count on line {$line}.");
            }
            $row = array_combine($headers, $values);
            $validator = Validator::make($row, [
                'university_code' => ['required', 'string', 'max:30'], 'university_name' => ['required', 'string', 'max:255'],
                'major_code' => ['nullable', 'string', 'max:30'], 'major_name' => ['required', 'string', 'max:255'],
                'year' => ['required', 'integer', 'min:2020', 'max:'.(now()->year + 1)], 'method' => ['required', 'string', 'max:120'],
                'source_url' => ['required', 'url', 'starts_with:https://'], 'source_name' => ['required', 'string', 'max:255'],
                'published_at' => ['required', 'date'], 'benchmark_score' => ['nullable', 'numeric', 'min:0', 'max:100'], 'quota' => ['nullable', 'integer', 'min:0'],
            ]);
            if ($validator->fails()) {
                throw new RuntimeException("Invalid official data on line {$line}: ".$validator->errors()->first());
            }
            $row['combinations'] = collect(explode('|', $row['combinations']))->map(fn ($code) => strtoupper(trim($code)))->filter()->unique()->values()->all();
            $row['source_hash'] = hash('sha256', json_encode($row, JSON_UNESCAPED_UNICODE));
            $rows->push($row);
        }
        fclose($handle);

        return $rows;
    }

    private function persist(array $row): void
    {
        $university = University::updateOrCreate(['code' => $row['university_code']], ['name' => $row['university_name'], 'short_name' => ($row['university_short_name'] ?? '') ?: null, 'province' => ($row['province'] ?? '') ?: null, 'website' => ($row['university_website'] ?? '') ?: null, 'is_active' => true]);
        AdmissionProgram::updateOrCreate(
            ['university_id' => $university->id, 'major_code' => $row['major_code'] ?: null, 'major_name' => $row['major_name'], 'year' => $row['year'], 'method' => $row['method']],
            ['combinations' => $row['combinations'], 'benchmark_score' => ($row['benchmark_score'] ?? '') !== '' ? $row['benchmark_score'] : null, 'quota' => ($row['quota'] ?? '') !== '' ? $row['quota'] : null, 'source_url' => $row['source_url'], 'source_name' => $row['source_name'], 'published_at' => $row['published_at'], 'verified_at' => now(), 'source_hash' => $row['source_hash']]
        );
    }
}
