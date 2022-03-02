<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PastLabels extends Model
{
    use HasFactory;
    protected $table = 'past_labels';
    public $timestamps = true;
    public $guarded = ['id'];
}
