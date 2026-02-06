<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Email extends Model
{
    use HasFactory;
    
    protected $guarded = [];

    // Relationship: Get the replies for this email
    public function replies()
    {
        return $this->hasMany(Email::class, 'parent_id')->with('replies'); // Recursive loading
    }

    // Relationship: Get the parent email
    public function parent()
    {
        return $this->belongsTo(Email::class, 'parent_id');
    }
}