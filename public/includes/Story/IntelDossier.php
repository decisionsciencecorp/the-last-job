<?php
declare(strict_types=1);

namespace LastJob\Story;

final class IntelDossier
{
    public function __construct(
        private string $path,
    ) {
    }

    /** @return array<int,array<string,mixed>> */
    public function threads(): array
    {
        if (!is_file($this->path)) {
            throw new \RuntimeException("Intel dossier missing: {$this->path}");
        }

        $decoded = json_decode((string) file_get_contents($this->path), true);
        if (!is_array($decoded)) {
            throw new \RuntimeException("Invalid intel dossier JSON: {$this->path}");
        }

        foreach ($decoded as $row) {
            if (!is_array($row) || empty($row['id']) || empty($row['title']) || empty($row['summary'])) {
                throw new \RuntimeException('Intel dossier row missing id, title, or summary');
            }
        }

        return $decoded;
    }
}
