<?php

namespace NumaxLab\Lunar\Geslib\InterCommands;

use NumaxLab\Geslib\Lines\Ibic;

class IbicCommand extends AbstractCommand
{
    public const HANDLE = 'ibic';

    public string $articleId;

    public string $code;

    public string $description;

    public function __construct(private readonly Ibic $ibic)
    {
        $this->isBatch = true;
        $this->type = Ibic::CODE;
    }

    public function __invoke(): void
    {
        $this->articleId = $this->ibic->articleId();
        $this->code = $this->ibic->code();
        $this->description = $this->ibic->description();
    }
}
