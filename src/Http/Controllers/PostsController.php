<?php

namespace Wingsline\Blog\Http\Controllers;

use Illuminate\Http\Request;
use Wingsline\Blog\Posts\Post;
use App\Http\Controllers\Controller;
use Wingsline\Blog\Http\Requests\PostRequest;
use Wingsline\Blog\Http\Requests\ImageUploadRequest;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded;

class PostsController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $post = new Post();

        $post->publish_date = now();

        return view('blog::posts.create', compact('post'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Post $post)
    {
        $post->delete();

        flash()->success('Post deleted.');

        return redirect()->route('admin.posts.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Post $post)
    {
        return view('blog::posts.edit', compact('post'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $posts = Post::orderBy('publish_date', 'desc')->paginate(config('admin.per_page'));

        return view('blog::posts.index', compact('posts'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(PostRequest $request)
    {
        $post = (new Post())->updateAttributes($request->validated());

        flash()->success('Post saved.');

        return redirect()->route('admin.posts.edit', $post);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(PostRequest $request, Post $post)
    {
        $post->updateAttributes($request->validated());

        flash()->success('Post updated.');

        return redirect()->route('admin.posts.edit', $post);
    }

    /**
     * Upload an image.
     *
     * @param Post               $post
     * @param ImageUploadRequest $request
     *
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\DiskDoesNotExist
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileDoesNotExist
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileIsTooBig
     *
     * @return array
     */
    public function upload(Post $post, ImageUploadRequest $request)
    {
        try {
            $media = $post->addMedia($request->file('image'))
                ->toMediaCollection('images');
        } catch (FileCannotBeAdded $exception) {
            return response()->json(['error' => $exception->getMessage()], 422);
        }

        return [
            'data' => ['filePath' => ltrim($media->getUrl(), '/')],
        ];
    }
}
