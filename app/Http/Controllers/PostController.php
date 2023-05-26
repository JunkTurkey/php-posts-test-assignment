<?php

namespace App\Http\Controllers;

use App\Http\Requests\Posts\PostDeleteRequest;
use App\Http\Requests\Posts\PostStoreRequest;
use App\Http\Requests\Posts\PostUpdateRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::query()
            ->simplePaginate(50)
        ;

        return PostResource::collection($posts);
    }

    public function show(Post $post)
    {
        return new PostResource($post);
    }

    public function store(PostStoreRequest $request)
    {
        Post::create($request->getData());
    }

    /*
     * UPDATE won't re-generate slug if it is empty in request
     */
    public function update(PostUpdateRequest $request, Post $post)
    {
        $post->update($request->getData());
    }

    public function destroy(PostDeleteRequest $request, Post $post)
    {
        $post->delete();
    }
}
