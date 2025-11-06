<?php

namespace NumaxLab\Lunar\Geslib\Models;

class Collection extends \Lunar\Models\Collection
{
    public function getAncestorSection(): ?self
    {
        if (! $this->isInSectionTree()) {
            return null;
        }

        if ($this->isRoot()) {
            return $this;
        }

        foreach ($this->ancestors as $ancestor) {
            if ($ancestor->translateAttribute('is-section') === true) {
                return $ancestor;
            }
        }

        return null;
    }

    public function isInSectionTree(): bool
    {
        if ($this->translateAttribute('is-section') === true) {
            return true;
        }

        foreach ($this->ancestors as $ancestor) {
            if ($ancestor->translateAttribute('is-section') === true) {
                return true;
            }
        }

        return false;
    }
}
