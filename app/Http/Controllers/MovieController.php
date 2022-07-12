<?php

namespace App\Http\Controllers;

use App\Models\Actor;
use App\Models\Movie;
use App\Models\Producer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MovieController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('guest');
    }

    /**
     * Show the movie list.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $movies = Movie::with(['actors','producer'])->get();
        $actors = Actor::get(['name','id']);

        $producers = Producer::get(['name','id']);

        $actor = [];
        foreach ($actors as $value) {
            $actor [] = [
                'value' => $value->name,
                'label' => $value->name,
            ];
        }

        $producer = [];
        foreach ($producers as $value) {
            $producer [] = [
                'value' => $value->name,
                'label' => $value->name,
            ];
        }

        $movie = [];
        foreach ($movies as $value) {
            $movie[] = (object) array_merge((array) $value->toArray(), (array) [
                'imagePath' => $value->image ? 'images/'.$value->image : 'assets/no_photo.png',
            ]);
        }

        $actor = collect($actor);
        $producer = collect($producer);
        $movie = collect($movie);
        
        return view('movie', compact('movie', 'actor', 'producer'));
    }

    /**
     * Remove movie from page.
     *
     * @param  Request $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        try{
            $movie = Movie::find($request->id)->delete();

            if ($movie) {
                $data = Movie::with(['actors','producer'])->get();

                $movie = [];
                foreach($data as $value){
                    $movie[] = (object) array_merge((array) $value->toArray(), (array) [
                        'imagePath' => $value->image ? 'images/'.$value->image : 'assets/no_photo.png',
                    ]);
                }

                $movie = collect($movie);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Movie successfully deleted.',
                'data' => $movie,
            ]);
        }catch(\Exception $e){
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Store new movie from create page
     *
     * @return mixed
     */
    public function store(Request $request)
    {   
        try{
            $validator = validator(
                $request->all(),
                [
                    'name' => ['required'],
                    'year_of_release' => ['required', 'integer', 'digits:4'],
                    'plot' => ['required'],
                    'image' => ['nullable', 'max:2000', 'mimes:jpeg,jpg,png'],
                ],
                [
                    'name.required' => 'Movie name is required',
                    'year_of_release.required' => 'Year of release is required',
                    'year_of_release.integer' => 'Invalid Year of release',
                    'year_of_release.digits' => 'Year of release should be 4 digits',
                    'plot.required' => 'Plot is required',
                ]
            );

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first(),
                ]);
            }

            $file_name = 'movie-'.rand(). '.' .$request->image->getClientOriginalExtension();
        
            $dataList = [
                'name' => $request->name,
                'year_of_release' => $request->year_of_release,
                'plot' => $request->plot,
                'image' => $file_name,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];

            $movie = Movie::create($dataList);

            foreach(explode(',', $request->actor) as $value){
                $movie->actors()->create([
                    'name' => $value
                ]);
            }

            $movie->producer()->create([
                'name' => $request->producer
            ]);
            
            if ($movie) {
                $request->image->move(public_path('images/'), $file_name);
                $data = Movie::with(['actors','producer'])->get();

                $movie = [];
                foreach($data as $value){
                    $movie[] = (object) array_merge((array) $value->toArray(), (array) [
                        'imagePath' => $value->image ? 'images/'.$value->image : 'assets/no_photo.png',
                    ]);
                }

                $movie = collect($movie);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Movie successfully created.',
                'data' => $movie,
            ]);
        }catch(\Exception $e){
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update movie from create page
     *
     * @return mixed
     */
    public function update(Request $request)
    {   
        $request->setMethod('PATCH');
        try{
            $validator = validator(
                $request->all(),
                [
                    'name' => ['required'],
                    'year_of_release' => ['required', 'integer', 'digits:4'],
                    'plot' => ['required'],
                    'imageNew' => ['nullable', 'max:2000', 'mimes:jpeg,jpg,png'],
                ],
                [
                    'name.required' => 'Movie name is required',
                    'year_of_release.required' => 'Year of release is required',
                    'year_of_release.integer' => 'Invalid Year of release',
                    'year_of_release.digits' => 'Year of release should be 4 digits',
                    'plot.required' => 'Plot is required',
                    'imageNew.max' => 'The image may not be greater than 2000 kilobytes',
                    'imageNew.mimes' => 'The image must be a file of type: jpeg, jpg, png',
                ]
            );

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first(),
                ]);
            }

            $file_name = $request->imageNew ? 'movie-'.rand(). '.' .$request->imageNew->getClientOriginalExtension() : $request->image;

            $dataList = [
                'name' => $request->name,
                'year_of_release' => $request->year_of_release,
                'plot' => $request->plot,
                'image' => $file_name,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];

            $movie = Movie::find($request->id)->update($dataList);
            
            if ($movie) {
                $request->imageNew ? $request->imageNew->move(public_path('images/'), $file_name) : '';

                $data = Movie::with(['actors','producer'])->get();

                $movie = [];
                foreach($data as $value){
                    $movie[] = (object) array_merge((array) $value->toArray(), (array) [
                        'imagePath' => $value->image ? 'images/'.$value->image : 'assets/no_photo.png',
                    ]);
                }

                $movie = collect($movie);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Movie successfully updated.',
                'data' => $movie,
            ]);
        }catch(\Exception $e){
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
}
