<?php

function getCategoryIcon($categoryName)
{
    return match ($categoryName) {
        'Annonces officielles' => 'bullhorn',
        'Aide aux devoirs' => 'book',
        'Discussions générales' => 'comments',
        'Questions aux professeurs' => 'question-circle',
        default => 'folder'
    };
}
