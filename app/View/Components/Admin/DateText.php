<?php

namespace App\View\Components\Admin;

use App\Support\IndonesianDate;
use Carbon\CarbonInterface;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class DateText extends Component
{
    public function __construct(
        public ?CarbonInterface $date,
        public bool $time = false,
    ) {}

    public function display(): string
    {
        return IndonesianDate::format($this->date, $this->time);
    }

    public function render(): View|Closure|string
    {
        return view('components.admin.date-text');
    }
}
