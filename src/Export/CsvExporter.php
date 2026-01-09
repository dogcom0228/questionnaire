<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Export;

use Liangjin0228\Questionnaire\Contracts\ExporterInterface;
use Liangjin0228\Questionnaire\Contracts\QuestionTypeRegistryInterface;
use Liangjin0228\Questionnaire\Models\Questionnaire;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvExporter implements ExporterInterface
{
    public function __construct(
        protected QuestionTypeRegistryInterface $questionTypeRegistry
    ) {}

    /**
     * {@inheritdoc}
     */
    public function getFormat(): string
    {
        return 'csv';
    }

    /**
     * {@inheritdoc}
     */
    public function getMimeType(): string
    {
        return 'text/csv';
    }

    /**
     * {@inheritdoc}
     */
    public function getExtension(): string
    {
        return 'csv';
    }

    /**
     * {@inheritdoc}
     */
    public function export(Questionnaire $questionnaire, array $options = []): StreamedResponse
    {
        $questionnaire->load(['questions', 'responses.answers']);

        $delimiter = $options['delimiter'] ?? config('questionnaire.export.csv.delimiter', ',');
        $enclosure = $options['enclosure'] ?? config('questionnaire.export.csv.enclosure', '"');
        $includeHeaders = $options['include_headers'] ?? config('questionnaire.export.csv.include_headers', true);

        $filename = $this->getFilename($questionnaire);

        return new StreamedResponse(function () use ($questionnaire, $delimiter, $enclosure, $includeHeaders) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Headers
            if ($includeHeaders) {
                $headers = $this->buildHeaders($questionnaire);
                fputcsv($handle, $headers, $delimiter, $enclosure);
            }

            // Data rows
            foreach ($questionnaire->responses as $response) {
                $row = $this->buildRow($questionnaire, $response);
                fputcsv($handle, $row, $delimiter, $enclosure);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => $this->getMimeType() . '; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getFilename(Questionnaire $questionnaire): string
    {
        $slug = \Illuminate\Support\Str::slug($questionnaire->title);
        $date = now()->format('Y-m-d_His');

        return "{$slug}_responses_{$date}.{$this->getExtension()}";
    }

    /**
     * Build the header row.
     *
     * @return array<string>
     */
    protected function buildHeaders(Questionnaire $questionnaire): array
    {
        $headers = [
            'Response ID',
            'Submitted At',
            'IP Address',
            'Respondent Type',
            'Respondent ID',
        ];

        foreach ($questionnaire->questions as $question) {
            $headers[] = $question->content;
        }

        return $headers;
    }

    /**
     * Build a data row for a response.
     *
     * @return array<string>
     */
    protected function buildRow(Questionnaire $questionnaire, $response): array
    {
        $row = [
            $response->id,
            $response->created_at->toIso8601String(),
            $response->ip_address ?? '',
            $response->respondent_type ?? '',
            $response->respondent_id ?? '',
        ];

        foreach ($questionnaire->questions as $question) {
            $answer = $response->answers->firstWhere('question_id', $question->id);

            if ($answer) {
                $questionType = $this->questionTypeRegistry->get($question->type);
                if ($questionType) {
                    $row[] = $questionType->formatValue($answer->value, $question);
                } else {
                    $row[] = $answer->value ?? '';
                }
            } else {
                $row[] = '';
            }
        }

        return $row;
    }
}
