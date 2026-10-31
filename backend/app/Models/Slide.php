<?php

namespace App\Models;

use App\Models\Concerns\IsVisualItem;
use Illuminate\Database\Eloquent\Model;

/** صورة في السلايدر الرئيسي */
class Slide extends Model
{
    use IsVisualItem;

    public function getFillable(): array
    {
        return $this->visualFillable();
    }

    protected function casts(): array
    {
        return $this->visualCasts();
    }
}
