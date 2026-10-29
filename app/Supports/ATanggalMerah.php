<?php

namespace App\Supports;

use Grei\TanggalMerah as GreiTanggalMerah;

class ATanggalMerah extends GreiTanggalMerah
{
    public function __construct(?string $local = null)
    {
        try {
            parent::__construct($local);
        } catch (\Throwable $e) {
            $this->data = [];
        }
    }

    public function getData()
    {
        return $this->data ?? [];
    }
}
