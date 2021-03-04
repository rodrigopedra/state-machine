<?php

namespace RodrigoPedra\StateMachine\Support;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

final class DotImageExporter
{
    private string $dotBinary;
    private string $format;

    public function __construct(
        string $dotBinary,
        string $format = 'png'
    ) {
        if (! \in_array($format, ['png', 'jpg', 'gif', 'svg'])) {
            throw new \InvalidArgumentException(\sprintf("Format '%s' is not supported", $format));
        }

        $this->dotBinary = $dotBinary;
        $this->format = $format;
    }

    public function export(string $dot, \Closure $pathResolver): string
    {
        $filepath = $pathResolver($this->format);

        $process = new Process([$this->dotBinary, '-T', $this->format, '-o', $filepath]);
        $process->setInput($dot);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $filepath;
    }
}
