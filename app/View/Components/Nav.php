<?php

namespace App\View\Components;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Component;

class Nav extends Component
{
    public $items;
    public $active;

    public function __construct($context)
    {
        //
        $this->items = $this->preparedItems(config('nav'));
        // $this->items =config('nav');

        $this->active = Route::currentRouteName();
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.nav');
    }

    protected function preparedItems($items){
        $user = Auth::user();

        foreach($items as $key=>$item){
            if(isset($item['ability']) && !$user->can($item['ability'])){
                unset($items[$key]);
            }
        }
        return $items;
    }
}
