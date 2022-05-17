<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Validation\Rule;

class AdminPostController extends Controller
{
    public function index(): Factory|View|Application
    {
        return view('admin.posts.index', [
            'posts' => Post::paginate(50)
        ]);
    }

    public function create(): Factory|View|Application
    {
        return view('admin.posts.create');
    }

    public function store(): Redirector|Application|RedirectResponse
    {
        try {
            Post::create(array_merge($this->validatePost(), [
                'user_id'   => request()->user()->id,
                'thumbnail' => request()->file('thumbnail')->storeAs(
                    'thumbnails',
                    request()->file('thumbnail')->hashName(),
                    'public'

                )]));
        } catch (\Exception $exception) {
            dd($exception);
        }


        return redirect('/');
    }

    public function edit(Post $post): Factory|View|Application
    {
        return view('admin.posts.edit', ['post' => $post]);
    }

    public function update(Post $post): RedirectResponse
    {
        $attributes = $this->validatePost($post);

        if ($attributes['thumbnail'] ?? false) {
            $attributes['thumbnail'] = request()->file('thumbnail')->storeAs(
                'thumbnails',
                request()->file('thumbnail')->hashName(),
                'public');
        }

        $post->update($attributes);

        return back()->with('success', 'Post Updated!');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $post->delete();

        return back()->with('success', 'Post Deleted!');
    }

    protected function validatePost(?Post $post = null): array
    {
        $post ??= new Post();

        return request()->validate([
            'title'        => 'required',
            'thumbnail'    => $post->exists ? ['image'] : ['required', 'image'],
            'slug'         => ['required', Rule::unique('posts', 'slug')->ignore($post)],
            'excerpt'      => 'required',
            'body'         => 'required',
            'category_id'  => ['required', Rule::exists('categories', 'id')],
            'published_at' => 'nullable'
        ]);
    }
}
