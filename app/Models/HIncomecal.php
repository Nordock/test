<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HIncomecal extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'h_incomecal';

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'id_user');
    }
}
