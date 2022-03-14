<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cards extends Model
{
    use HasFactory;

    protected $primaryKey='id';

    protected $fillable = [
        'name',
        'description',
        'collection',
    ];
    public function collections(){
        return $this->belongsTo(Collections::class);
    }

    public function collectionsCards(){
        return $this->belongsTo(CardsForCollections::class);
    }
}
