<?php

namespace App\Actions;

use App\Models\Monster;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;

class GenerateMonsterAction
{
    protected array $types = ["water", "fire", "grass", "electric", "normal"];
    protected string $namesFilePath = 'monsters.php';

    public function execute(): void
    {
        $names = $this->loadMonsterNames();
        $name = $this->getUniqueName($names);

        if (!$name) {
            return;
        }

        $type = $this->getRandomType();
        $this->createMonster($name, $type);
    }

    protected function loadMonsterNames(): Collection
    {
        $names = require storage_path($this->namesFilePath);

        if (!is_array($names)) {
            throw new \RuntimeException("Monster names file must return an array.");
        }

        return collect($names);
    }

    protected function getUniqueName(Collection $names): ?string
    {
        $createdNames = Monster::query()->pluck('name')->toArray();
        return $names->diff($createdNames)->first();
    }

    protected function getRandomType(): string
    {
        return $this->types[array_rand($this->types)];
    }

    protected function createMonster(string $name, string $type): void
    {
        Monster::create([
            'name' => $name,
            'type' => $type,
            'hp' => rand(30, 100),
            'attack' => rand(30, 100),
            'defense' => rand(30, 100),
        ]);
    }
}
