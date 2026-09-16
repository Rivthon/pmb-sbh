<?php

namespace App\Traits\Utilities;

use Haruncpi\LaravelIdGenerator\IdGenerator;

trait WithCodeGenerator
{
    public function generateUniqueCode($unique_prefix_code)
    {
        $base_code_length = 12;
        $code_length = $base_code_length + strlen($unique_prefix_code);

        return IdGenerator::generate([
            'table' => $this->getTable(),
            'field' => 'code',
            'length' => $code_length,
            'prefix' => $unique_prefix_code . '-' . now()->format('dmy') . '-',
            'reset_on_prefix_change' => true
        ]);
    }
}