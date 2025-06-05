<?php

namespace NumaxLab\Lunar\Geslib\Geslib;

use NumaxLab\Geslib\Lines\Ibic;

class IbicCommand extends AbstractCommand
{
    public const HANDLE = 'ibic';

    public string $articleId;
    public string $code;
    public string $description;

    public function __construct()
    {
        $this->isBatch = true;
        $this->type = Ibic::CODE;
    }

    public function __invoke(Ibic $ibic): void
    {
        $this->articleId = $ibic->articleId();
        $this->code = $ibic->code();
        $this->description = $ibic->description();
    }
}
