<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CategoryCard extends Component
{
    public $image;
    public $title;
    public $desc;

    /**
     * Create a new component instance.
     */
    public function __construct($image, $title, $desc)
    {
        $this->image = $image;
        $this->title = $title;
        $this->desc = $desc;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.category-card');
    }
}
