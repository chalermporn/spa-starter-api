<?php

namespace App\Http\Controllers;

use App\Book;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class BooksController extends Controller
{
    /**
     * Creates a new class instance.
     */
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @param  Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $limit = (int) $request->query('limit', 10);
        $include = $request->query('include');
        $sort = $request->query('sort', 'id');
        $order = $request->query('order_by', 'desc');

        $books = Book::take($limit)->orderBy($sort, $order);

        if ($include) {
            $books = $books->with($include);
        }

        $books = $books->get();

        $pagination = new LengthAwarePaginator($books, Book::count(), $limit);

        return response()->json([
            'data' => $books,
            'metadata' => [
                'pagination' => array_except($pagination->toArray(), 'data'),
            ],
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
            'author' => 'required|exists:authors,id',
        ]);

        $book = new Book($request->all());

        $book->author()
            ->associate($request->author)
            ->save();

        $book->load('author');

        return response()->json([
            'data' => $book,
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  Request $request
     * @param  int     $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        $include = $request->query('include');
        $book = ($include) ? Book::with($include)->find($id) : Book::find($id);

        if (! $book) {
            return response()->json([
                'errors' => ['Book not found.'],
            ], 404);
        }

        return response()->json([
            'data' => $book,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request $request
     * @param  int         $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'title' => 'required',
            'author' => 'required|exists:authors,id',
        ]);

        $book = Book::find($id);

        if (! $book) {
            return response()->json([
                'errors' => ['Book not found.'],
            ], 404);
        }

        $book->fill($request->all())
            ->author()
            ->associate($request->author)
            ->save();

        $book->load('author');

        return response()->json([
            'data' => $book,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $book = Book::find($id);

        if (! $book) {
            return response()->json([
                'errors' => ['Book not found.'],
            ], 404);
        }

        $book->delete();

        return response()->json([], 204);
    }
}
