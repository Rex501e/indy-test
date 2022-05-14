<?php

namespace Entity;

class RedeemInfo
{
    private $promocodeName;

    private $arguments = [];

    public function getPromocodeName(): ?string
    {
        return $this->promocodeName;
    }

    public function setPromocodeName(string $promocodeName): self
    {
        $this->promocodeName = $promocodeName;

        return $this;
    }

    public function getArguments(): ?array
    {
        return $this->arguments;
    }

    public function setArguments(array $arguments): self
    {
        $this->arguments = $arguments;

        return $this;
    }
}
