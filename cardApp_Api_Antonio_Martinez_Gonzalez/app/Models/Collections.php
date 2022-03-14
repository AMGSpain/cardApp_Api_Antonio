<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Collections extends Model
{
    use HasFactory;
    protected $primaryKey='id';

    protected $fillable = [
        'name',
        'dateEdition',
        'symbol',
    ];

    public function card(){
        return $this->hasMany(Cards::class, 'id');
    }
}
