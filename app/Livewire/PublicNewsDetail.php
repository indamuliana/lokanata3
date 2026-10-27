<?php

namespace App\Livewire;

use App\Models\WebPost;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.public')]
class PublicNewsDetail extends Component
{
    public string $slug;
    public ?WebPost $post = null;

    public function mount(string $slug): void
    {
        $this->slug = $slug;
        $this->post = WebPost::where('slug', $slug)->firstOrFail();

        // Increment view count
        $this->post->increment('views_count');
    }

    public function render()
    {
        $relatedPosts = WebPost::published()
            ->where('id', '!=', $this->post->id)
            ->where(function ($q) {
                $q->where('category', $this->post->category);
            })
            ->latest('published_at')
            ->take(3)
            ->get();

        if ($relatedPosts->isEmpty()) {
            $relatedPosts = WebPost::published()
                ->where('id', '!=', $this->post->id)
                ->latest('published_at')
                ->take(3)
                ->get();
        }

        return view('livewire.public-news-detail', [
            'post' => $this->post,
            'relatedPosts' => $relatedPosts,
        ]);
    }
}
