<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CardsForCollections extends Model
{
    use HasFactory;
    protected $table = 'cards_collections';

    public function cardsCollection(){
        return $this->hasMany(Cards::class, 'id');
    }
}
