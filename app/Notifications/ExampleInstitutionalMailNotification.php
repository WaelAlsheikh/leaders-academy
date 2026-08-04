<?php

namespace App\Notifications;

/**
 * Example official notice routed via HasInstitutionalMail (active mailbox preferred).
 */
class ExampleInstitutionalMailNotification extends InstitutionalMailNotification
{
    public function __construct(
        private readonly string $subjectLine,
        private readonly array $lines,
    ) {}

    protected function mailSubject(): string
    {
        return $this->subjectLine;
    }

    protected function mailLines(): array
    {
        return $this->lines;
    }
}
