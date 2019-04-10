<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    static $gojek = 1;
    static $grab = 2;

    /**
     *  Categories recursion.
     *
     *  @param array $arr
     *  @return \Illuminate\Http\Response
     */
    public function recurse($arr, $options = '', $level = 0, $selected = null)
    {
        foreach ($arr as $n) {
            $options .= '<option ';
            if ((!empty($selected) && $n->id == $selected) or old("id_parent") == $n->id) {
              $options .= 'selected ';
            }
            $options .= 'value="' . $n->id . '">' . str_repeat("-", $level) .' ' .$n->name . '</option>';

            if (isset($n->children) && !empty($n->children)) {
                $options = $this->recurse($n->children, $options, $level + 1, $selected);
            }
        }

        return $options;
    }
}
