<?php

namespace App;

use App\User;
use Illuminate\Database\Eloquent\Model;

class RentedBook extends Model
{
    protected $table = 'rentedBooks';
    protected $guarded = [];

}
