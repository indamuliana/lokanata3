<?php

namespace App\Livewire;

use App\Models\WebPost;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.public')]
class PublicNewsList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $category = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCategory(): void
    {
        $this->resetPage();
    }

    public function selectCategory(string $cat): void
    {
        $this->category = $cat;
        $this->resetPage();
    }

    public function render()
    {
        $query = WebPost::published();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', "%{$this->search}%")
                  ->orWhere('summary', 'like', "%{$this->search}%")
                  ->orWhere('content', 'like', "%{$this->search}%");
            });
        }

        if ($this->category) {
            $query->where('category', $this->category);
        }

        $posts = $query->latest('published_at')->paginate(9);

        $categories = WebPost::published()
            ->select('category')
            ->distinct()
            ->pluck('category');

        return view('livewire.public-news-list', [
            'posts' => $posts,
            'categories' => $categories,
        ]);
    }
}
