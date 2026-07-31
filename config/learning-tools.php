<?php

return [
    'categories' => [
        'all' => 'all',
        'memory' => 'memory',
        'practice' => 'practice',
        'planning' => 'planning',
        'orientation' => 'orientation',
    ],
    'tools' => [
        'pomodoro' => ['category' => 'planning', 'icon' => 'heroicon-o-clock', 'roles' => ['student', 'teacher'], 'status' => 'active', 'route' => 'learning-tools.pomodoro.index'],
        'notes' => ['category' => 'memory', 'icon' => 'heroicon-o-book-open', 'roles' => ['student', 'teacher'], 'status' => 'active', 'route' => 'learning-tools.notes.index'],
        'knowledge_base' => ['category' => 'memory', 'icon' => 'heroicon-o-light-bulb', 'roles' => ['student', 'teacher'], 'status' => 'active', 'route' => 'learning-tools.resources.index'],
        'flashcards' => ['category' => 'memory', 'icon' => 'heroicon-o-rectangle-stack', 'roles' => ['student', 'teacher'], 'status' => 'planned'],
        'study_plan' => ['category' => 'planning', 'icon' => 'heroicon-o-calendar-days', 'roles' => ['student', 'teacher'], 'status' => 'planned'],
        'mistake_notebook' => ['category' => 'practice', 'icon' => 'heroicon-o-exclamation-triangle', 'roles' => ['student', 'teacher'], 'status' => 'planned'],
        'personalized_practice' => ['category' => 'practice', 'icon' => 'heroicon-o-pencil-square', 'roles' => ['student', 'teacher'], 'status' => 'planned'],
        'knowledge_gaps' => ['category' => 'practice', 'icon' => 'heroicon-o-chart-bar', 'roles' => ['student', 'teacher'], 'status' => 'planned'],
        'score_calculator' => ['category' => 'orientation', 'icon' => 'heroicon-o-calculator', 'roles' => ['student', 'teacher'], 'status' => 'planned'],
        'admission_lookup' => ['category' => 'orientation', 'icon' => 'heroicon-o-magnifying-glass', 'roles' => ['student', 'teacher'], 'status' => 'planned'],
        'ai_tutor' => ['category' => 'practice', 'icon' => 'heroicon-o-sparkles', 'roles' => ['student', 'teacher'], 'status' => 'planned'],
    ],
];
