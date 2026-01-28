<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Infrastructure\Export;

use Liangjin0228\Questionnaire\Contracts\ExporterInterface;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Models\Questionnaire;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * CSV exporter for questionnaire responses.
 *
 * Exports questionnaire responses to CSV format.
 */
class CsvExporter implements ExporterInterface
{
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
        $filename = $this->getFilename($questionnaire);

        $callback = function () use ($questionnaire) {
            $file = fopen('php://output', 'w');

            // Write CSV header
            $headers = ['Response ID', 'Submitted At', 'Respondent Type', 'Respondent ID'];
            foreach ($questionnaire->questions as $question) {
                $headers[] = $question->text;
            }
            fputcsv($file, $headers);

            // Write response data
            foreach ($questionnaire->responses as $response) {
                $row = [
                    $response->id,
                    $response->submitted_at,
                    $response->respondent_type ?? 'anonymous',
                    $response->respondent_id ?? '',
                ];

                foreach ($questionnaire->questions as $question) {
                    $answer = $response->answers->firstWhere('question_id', $question->id);
                    $value = $answer?->value ?? '';

                    // Handle array values (checkboxes, etc.)
                    if (is_array($value)) {
                        $value = implode(', ', $value);
                    }

                    $row[] = $value;
                }

                fputcsv($file, $row);
            }

            fclose($file);
        };

        return new StreamedResponse($callback, 200, [
            'Content-Type' => $this->getMimeType(),
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getFilename(Questionnaire $questionnaire): string
    {
        $slug = $questionnaire->slug ?? 'questionnaire';
        $timestamp = now()->format('Y-m-d-His');

        return "{$slug}-responses-{$timestamp}.csv";
    }
}
