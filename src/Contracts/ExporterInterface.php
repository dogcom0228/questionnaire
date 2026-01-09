<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Contracts;

use Liangjin0228\Questionnaire\Models\Questionnaire;
use Symfony\Component\HttpFoundation\StreamedResponse;

interface ExporterInterface
{
    /**
     * Get the export format identifier.
     */
    public function getFormat(): string;

    /**
     * Get the MIME type for this export format.
     */
    public function getMimeType(): string;

    /**
     * Get the file extension for this export format.
     */
    public function getExtension(): string;

    /**
     * Export responses for a questionnaire.
     *
     * @param Questionnaire $questionnaire
     * @param array<string, mixed> $options
     * @return StreamedResponse
     */
    public function export(Questionnaire $questionnaire, array $options = []): StreamedResponse;

    /**
     * Get the export filename.
     */
    public function getFilename(Questionnaire $questionnaire): string;
}
