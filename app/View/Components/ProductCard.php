<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ProductCard extends Component
{
    public $image;
    public $title;
    public $price;
    public $seller;

    /**
     * Create a new component instance.
     */
    public function __construct($image, $title, $price, $seller)
    {
        $this->image = $image;
        $this->title = $title;
        $this->price = $price;
        $this->seller = $seller;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.product-card');
    }
}
