<?php

namespace Entity;

class Promocode
{
    private $name;

    private $avantage = [];

    private $restrictions = [];

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAvantage(): ?array
    {
        return $this->avantage;
    }

    public function setAvantage(array $avantage): self
    {
        $this->avantage = $avantage;

        return $this;
    }

    public function getRestrictions(): ?array
    {
        return $this->restrictions;
    }

    public function setRestrictions(array $restrictions): self
    {
        $this->restrictions = $restrictions;

        return $this;
    }
}
