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

    // --- NEW MAGIC FUNCTION ---
    // This separates the "Real Message" from the "Quoted History"
    public function getCleanBodyAttribute()
    {
        // 1. Array of common "Reply Headers" used by Gmail, Outlook, Apple Mail
        $patterns = [
            '/On\s+\w{3},\s+\w{3}\s+\d{1,2},\s+\d{4}\s+at\s+.*wrote:/is', // Gmail style
            '/On\s+.*wrote:/is', // Generic style
            '/From:\s+.*Sent:\s+.*To:\s+.*Subject:/is', // Outlook style
            '/-{3,}\s+Original Message\s+-{3,}/is', // Generic separators
            '/_{3,}/' // Underscore separators
        ];

        $body = $this->body;

        foreach ($patterns as $pattern) {
            // Split the body into two parts: [0] = New Message, [1] = History
            $parts = preg_split($pattern, $body, 2);
            
            if (count($parts) > 1) {
                // We found a match! Return only the new part.
                return trim($parts[0]);
            }
        }

        // If no history found, return the whole body
        return trim($body);
    }

    // Helper to check if there IS hidden history
    public function hasHistory()
    {
        return trim($this->clean_body) !== trim($this->body);
    }
}