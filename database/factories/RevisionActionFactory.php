<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\RevisionAction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RevisionAction>
 */
class RevisionActionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'article_id' => Article::factory(),
            'revisor_id' => User::factory()->state(['is_revisor' => true]),
            'previous_status' => null,
            'new_status' => true,
            'undone_at' => null,
        ];
    }
}
