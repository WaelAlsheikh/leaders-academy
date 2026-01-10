<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StudentPlatform;

class StudentPlatformController extends Controller
{
    public function index()
    {
        $items = StudentPlatform::orderBy('title')->get();
        return view('student_platforms.index', compact('items'));
    }

    public function show($slug)
    {
        $item = StudentPlatform::where('slug', $slug)->firstOrFail();

        // sections: array of objects [{title,image,summary,content,button_text,button_link}]
        $sections = $item->sections ?? [];

        // If no sections provided, create two fallback sections using summary & content
        if (empty($sections)) {
            $sections = [
                [
                    'title' => $item->title,
                    'image' => $item->file ?? null,
                    'summary' => $item->summary ?? null,
                    'content' => $item->content ?? null,
                    'button_text' => 'سجل الآن',
                    'button_link' => route('student-platform.index'),
                ],
            ];
        }

        return view('student_platforms.show', compact('item','sections'));
    }
}
