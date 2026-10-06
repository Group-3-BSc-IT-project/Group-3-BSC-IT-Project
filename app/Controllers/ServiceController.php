<?php

class ServiceController
{
    public function __construct(
        private ServiceRepository $services
    ) {
    }

    public function all(): array
    {
        return $this->services->all();
    }
}
