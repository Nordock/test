<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use Auth;

class ProfileComposer
{
    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $profiles = [
            'avatar' => (!Auth::guest() && !empty(Auth::user()->photo)) ? asset('/uploads/photo/200_200_' . Auth::user()->photo) : asset('/uploads/avatar.jpeg')
        ];

        $view->with($profiles);
    }
}
