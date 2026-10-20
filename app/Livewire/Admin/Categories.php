<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

/** RF-45 a RF-48. Las categorías con gastos solo se desactivan, nunca se eliminan. */
#[Title('Categorías')]
class Categories extends Component
{
    public bool $showForm = false;
    public ?int $editingId = null;
    public string $name = '';
    public string $parent_id = '';
    public string $monthly_limit = '';
    public bool $active = true;

    public function create(): void
    {
        $this->reset(['editingId', 'name', 'parent_id', 'monthly_limit']);
        $this->active = true;
        $this->resetErrorBag();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $c = Category::findOrFail($id);
        $this->editingId = $c->id;
        $this->name = $c->name;
        $this->parent_id = (string) $c->parent_id;
        $this->monthly_limit = $c->monthly_limit !== null ? (string) $c->monthly_limit : '';
        $this->active = $c->active;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->monthly_limit = str_replace(',', '', $this->monthly_limit);

        $data = $this->validate([
            'name' => ['required', 'string', 'max:120', Rule::unique('categories', 'name')->where('parent_id', $this->parent_id ?: null)->ignore($this->editingId)],
            'parent_id' => ['nullable', 'exists:categories,id', Rule::notIn([$this->editingId])],
            'monthly_limit' => ['nullable', 'numeric', 'gt:0', 'max:99999999.99'],
            'active' => ['boolean'],
        ]);

        $data['parent_id'] = $data['parent_id'] ?: null;
        $data['monthly_limit'] = $data['monthly_limit'] !== '' ? $data['monthly_limit'] : null;

        $this->editingId ? Category::findOrFail($this->editingId)->update($data) : Category::create($data);

        $this->showForm = false;
        session()->flash('status', 'Categoría guardada.');
    }

    public function toggle(int $id): void
    {
        $c = Category::findOrFail($id);
        $c->update(['active' => ! $c->active]);
    }

    public function render()
    {
        return view('livewire.admin.categories', [
            'categories' => Category::with('parent:id,name')->withCount('expenses')->orderBy('name')->get(),
            'parents' => Category::whereNull('parent_id')->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
